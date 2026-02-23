<?php

namespace Modules\Core\Services;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Core\Mail\ResetPasswordEmail;
use Modules\Core\Mail\SecurityRoleUpdateEmail;
use Modules\Core\Models\Contact;
use Modules\Core\Models\Email;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\MediaStore;
use Modules\Core\Models\Person;
use Modules\Core\Models\SfyPasswordReset;
use Modules\Core\Models\User;
use Modules\Core\Traits\MediaTrait;

class UserService
{
    use AppHelperTrait, SAAApiResponse, MediaTrait;

    public function __construct(
        private readonly TranslationService $translationService,
        private readonly SystemMessageService $systemMessageService,
        private readonly SettingsService $settingsService,
    ) {}

    /**
     * Create a new user
     * 
     * @param array $userData
     * 
     * @return array
     */
    public function createUser(Request $request): Response|ResponseFactory|JsonResponse
    {
        DB::beginTransaction();

        try {
            // Manual validation :((
            if (is_null($request->use_existing_contact)) {
                if (!$request->first_name) {
                    throw new Exception('First name is required', 400);
                }

                if (!$request->last_name) {
                    throw new Exception('Last name is required', 400);
                }
            } else {
                if (!$request->contact_id) {
                    throw new Exception('You must select a contact', 400);
                }
            }

            $roles = $request->get('roles', []);

            if (empty($roles)) {
                throw new Exception("You must select at least one security role!");
            }

            $this->checkOwner($roles, $request->username);

            $contactType = DB::table('application_code_type')->where('code', Constants::ACT_CONTACT_TYPES)->first();

            $contact = Contact::create([
                'contact_type_id' => DB::table('application_code')
                    ->where('application_code_type_id', $contactType->id)
                    ->where('code', Constants::AC_CONTACT_TYPE_PERSON)
                    ->first()->id,
                'reference_no' => isset($user['reference_no']) && !empty($user['reference_no']) ? $user['reference_no'] : $this->formatCode(json_encode($request->all()))
            ]);

            $contactId = $contact->id;

            $person = Person::create([
                'contact_id' => $contactId,
                'title_id' => $request->titles_id,
                'first_name' => $request->first_name,
                'middle_names' => $request->middle_names,
                'last_name' => $request->last_name,
                'date_of_birth' => $request->date_of_birth,
            ]);

            $emailType = DB::table('application_code_type')->where('code', Constants::ACT_EMAIL_TYPES)->first();

            Email::updateOrCreate(
                [
                    'contact_id' => $contactId,
                    'is_primary' => true
                ],
                [
                    'email_address' => $request->email,
                    'type_id' => DB::table('application_code')
                        ->where('application_code_type_id', $emailType->id)
                        ->where('code', Constants::AC_EMAIL_TYPE_WORK)
                        ->first()->id
                ]
            );

            $user = new User();
            $user->username = $request->username;
            $user->password = '.';
            $user->contact_id = $contactId;
            $user->save();

            $user->roles()->detach();

            foreach ($roles as $role) {
                $user->assignRole($role);
            }

            $this->verifyProjectOwner();

            $prefix = 'USER_' . $user->id . '_';

            $person->update([
                'first_name' => str_replace('TMPSET_', $prefix, $person->first_name),
                'middle_names' => str_replace('TMPSET_', $prefix, $person->middle_names),
                'last_name' => str_replace('TMPSET_', $prefix, $person->last_name),
            ]);

            $this->translationService->translateFields([
                'first_name',
                'middle_names',
                'last_name',
            ], $request->all(), $prefix);

            $person = Person::where(['contact_id' => $contactId])->first();

            $uniqueTitle = $this->formatUniqueTitle(getPersonFullName($person));

            if ($request->has('user_image') && !is_null($request->user_image)) {
                $this->uploadFile($request, [
                    'field_name' => 'user_image',
                    'file_name' => $uniqueTitle,
                ], [
                    'name' => 'UserImage',
                    'id' => $user->id
                ], []);
            }

            $this->sendPasswordResetEmail($user->id);

            DB::commit();
            return $this->successResponse([
                'msg' => 'Created successfully!'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    public function updateUser(
        User $user,
        Request $request,
        bool $isProfile = false,
        bool $updatePassword = false
    ): Response|ResponseFactory|JsonResponse {
        DB::beginTransaction();

        try {
            if ($updatePassword) {
                $user->password = bcrypt($request->new_password);
                $user->verified = true;
            }

            $user->username = $request->username;
            $user->save();

            $emailInUse = Email::where([
                'email_address' => $request->email,
                'is_primary' => true
            ])
                ->where('contact_id', '!=', $user->contact_id)
                ->first();

            if ($emailInUse) {
                return $this->errorResponse('Email ' . $request->email . ' is already in use.', 400);
            }

            if (!$isProfile) {
                $roles = $request->get('roles', []);
                $currentRoles = $user->roles()->get()->pluck('name')->toArray();

                if ($currentRoles !== $roles) {
                    $user->roles()->detach();

                    if (empty($roles)) {
                        throw new Exception("You must select at least one security role!");
                    }

                    $this->checkOwner($roles, $request->username);

                    foreach ($roles as $role) {
                        $user->assignRole($role);
                    }

                    $updaedRoles = $user->roles()->get()->pluck('name')->toArray();

                    $previousRolesArray = [];
                    $currentRolesArray = [];

                    foreach ($updaedRoles as $_role) {
                        array_push($currentRolesArray, [
                            'name' => Constants::USER_ROLES_ARRAY[$_role]
                        ]);
                    }

                    foreach ($currentRoles as $_role) {
                        array_push($previousRolesArray, [
                            'name' => Constants::USER_ROLES_ARRAY[$_role]
                        ]);
                    }

                    $this->sendSecurityRolesUpdatedEmail([
                        'previous' => $previousRolesArray,
                        'current' => $currentRolesArray,
                    ], $request->email);
                }
            }

            $person = Person::where(['contact_id' => $user->contact_id])->first();

            $person->title_id = $request->titles_id;
            $person->first_name = $request->first_name;
            $person->middle_names = $request->middle_names;
            $person->last_name = $request->last_name;
            $person->date_of_birth = $request->date_of_birth;

            $person->save();

            $emailType = DB::table('application_code_type')->where('code', Constants::ACT_EMAIL_TYPES)->first();

            Email::updateOrCreate(
                [
                    'contact_id' => $user->contact_id,
                    'is_primary' => true
                ],
                [
                    'email_address' => $request->email,
                    'type_id' => DB::table('application_code')
                        ->where('application_code_type_id', $emailType->id)
                        ->where('code', Constants::AC_EMAIL_TYPE_WORK)
                        ->first()->id
                ]
            );

            $fullName = getPersonFullName($person);

            $removeUserImage = isset($request->remove_user_image) && ($request->remove_user_image === true || $request->remove_user_image === 'true');

            if ($removeUserImage) {
                MediaStore::where([
                    'entity_id' => (string)$user->id,
                    'entity_name' => 'UserImage'
                ])->delete();
            } else {
                if ($request->has('user_image') && !is_null($request->user_image)) {
                    $this->uploadFile($request, [
                        'field_name' => 'user_image',
                        'file_name' => $this->formatUniqueTitle($fullName),
                    ], [
                        'name' => 'UserImage',
                        'id' => $user->id
                    ], []);
                }
            }

            $prefix = 'USER_' . $user->id . '_';
            $this->translationService->translateFields([
                'first_name',
                'middle_names',
                'last_name',
            ], $request->all(), $prefix);

            DB::commit();
            return $this->successResponse([
                'msg' => $fullName . ' was updated successfully!',
                'user' => $this->formatUser($user)
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    private function checkOwner(array $roles, string $username): void
    {
        $projectOwner = User::role(Constants::USER_ROLE_OWNER)->first();
        if ($projectOwner && $projectOwner->username !== $username && in_array(Constants::USER_ROLE_OWNER, $roles)) {
            throw new Exception("This project has an owner assigned already. Projects can have only one owner!");
        }
    }

    public function deleteUser(int $id): Response|ResponseFactory|JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);

            SfyPasswordReset::where(['user_id' => $id])->delete();

            if (!$user) {
                return $this->errorResponse('User was not found!', 404);
            }

            Email::where([
                'contact_id' => $user->contact_id,
            ])->delete();

            $person = Person::where([
                'contact_id' => $user->contact_id,
            ])->first();

            // Clean translations
            LanguageCode::whereIn('code', [
                $person->first_name,
                $person->middle_names,
                $person->last_name,
            ])->delete();

            $fullName = getPersonFullName($person);

            $person->delete();

            $user->delete();

            $this->verifyProjectOwner();

            Contact::where([
                'id' => $user->contact_id,
            ])->delete();

            MediaStore::where([
                'entity_id' => (string)$id,
                'entity_name' => 'UserImage'
            ])->delete();

            DB::commit();
            return $this->successResponse([
                'msg' => 'User ' . $fullName . ' has been deleted successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    public function verifyProjectOwner(): void
    {
        $projectOwner = User::role(Constants::USER_ROLE_OWNER)->first();

        if (!$projectOwner) {
            throw new Exception("You must have one owner! Please set another snother user as owner and try again.", 400);
        }
    }

    public function sendPasswordResetEmail(int $userId, string $email = null): bool
    {
        try {
            SfyPasswordReset::where(['user_id' => $userId])->delete();

            $token = $this->generateToken();

            if (is_null($email)) {
                $email = $this->getUserEmailByUserId($userId);
            }

            $code = rand(10000000, 99999999);

            DB::table('sfy_password_reset')->insert([
                'user_id' => $userId,
                'token' => $token,
                'code' => $code,
                'created_at' => Carbon::now()
            ]);

            Mail::to($email)->send(new ResetPasswordEmail([
                'token' => $token,
                'code' => $code
            ]));

            return true;
        } catch (\Throwable $th) {
            throw $th;
            return false;
        }
    }

    public function sendSecurityRolesUpdatedEmail(array $roles, string $email): bool
    {
        try {
            $this->verifyProjectOwner();

            Mail::to($email)->send(new SecurityRoleUpdateEmail([
                'roles' => $roles,
            ]));

            return true;
        } catch (\Throwable $th) {
            throw $th;
            return false;
        }
    }

    public function getUserByEmail(string $email): ?User
    {
        return User::where([
            'contact_id' => DB::raw("(SELECT contact_id FROM email WHERE email_address = :email AND is_primary = 1)")
        ])
            ->setBindings(['email' => $email])
            ->first();
    }

    private function getUserEmailByUserId(int $userId): string
    {
        $emailQuery = DB::table('email AS e')
            ->leftJoin('users AS u', 'e.contact_id', '=', 'u.contact_id')
            ->select("e.id AS id", 'e.email_address')
            ->where([
                'u.id' => $userId,
                'e.is_primary' => 1,
            ])
            ->first();

        return $emailQuery ? $emailQuery->email_address : null;
    }

    /**
     * Fetch the one user by id
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        $user = null;
        try {
            $contactTypeCode = DB::table('users AS u')
                ->select('ac.code AS app_code')
                ->join('contacts AS c', 'c.id', '=', 'u.contact_id')
                ->join('application_code AS ac', 'ac.id', '=', 'c.contact_type_id')
                ->where('u.id', $id)
                ->first();

            if ($contactTypeCode) {
                switch ($contactTypeCode->app_code) {
                    case 'P':
                        $user = User::from('users AS u')
                            ->select(
                                'u.id AS id',
                                'u.username AS username',
                                'p.first_name AS first_name',
                                'p.middle_names AS middle_names',
                                'p.last_name AS last_name',
                                'p.date_of_birth AS date_of_birth',
                                'ms.id AS media_store_id',
                                'e.email_address AS email',
                                'ac.id AS titles_id',
                            )
                            ->join('contacts AS c', 'c.id', '=', 'u.contact_id')
                            ->join('persons AS p', 'c.id', '=', 'p.contact_id')
                            ->leftJoin('email AS e', function ($join) {
                                $join->on('e.contact_id', '=', 'c.id');
                                $join->on('e.is_primary', '=', DB::raw("1"));
                            })
                            ->leftJoin('media_store AS ms', function ($join) {
                                $join->on('ms.entity_id', '=', 'u.id');
                                $join->on('ms.entity_name', '=', DB::raw("'UserImage'"));
                            })
                            ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.title_id')
                            ->where('u.id', $id)
                            ->first();
                        break;
                    case 'O':
                        $user = User::from('users AS u')
                            ->select(
                                'u.id AS id',
                                'u.username AS username',
                                'ms.id AS media_store_id',
                                'o.name AS organisation_name',
                                'e.email_address AS email'
                            )
                            ->join('contacts AS c', 'c.id', '=', 'u.contact_id')
                            ->join('organisations AS o', 'c.id', '=', 'o.contact_id')
                            ->leftJoin('email AS e', function ($join) {
                                $join->on('e.contact_id', '=', 'c.id');
                                $join->on('e.is_primary', '=', DB::raw("1"));
                            })
                            ->leftJoin('media_store AS ms', function ($join) {
                                $join->on('ms.entity_id', '=', 'u.id');
                                $join->on('ms.entity_name', '=', DB::raw("'UserImage'"));
                            })
                            ->where('u.id', $id)
                            ->first();
                        break;
                }
            }
        } catch (Exception $ex) {
            throw $ex;
        }

        return $user;
    }

    /**
     * Fetch the one user by username or email
     * @param $usernameOrEmail
     * @return mixed
     */
    public function getUserByUsernameOrEmail($usernameOrEmail)
    {
        $user = null;
        try {
            $contactTypeCode = DB::table('users AS u')
                ->select('ac.code AS app_code')
                ->join('contacts AS c', 'c.id', '=', 'u.contact_id')
                ->join('application_code AS ac', 'ac.id', '=', 'c.contact_type_id')
                ->leftJoin('email AS e', function ($join) {
                    $join->on('e.contact_id', '=', 'c.id');
                    $join->on('e.is_primary', '=', DB::raw("1"));
                })
                ->where('u.username', $usernameOrEmail)
                ->orWhere('e.email_address', $usernameOrEmail)
                ->first();
            if ($contactTypeCode) {
                switch ($contactTypeCode->app_code) {
                    case 'P':
                        $user = DB::table('users AS u')
                            ->select('*', 'u.id AS id', 'ac.code AS title_code', 'ac.name AS title_name')
                            ->join('contacts AS c', 'c.id', '=', 'u.contact_id')
                            ->join('persons AS p', 'c.id', '=', 'p.contact_id')
                            ->leftJoin('email AS e', function ($join) {
                                $join->on('e.contact_id', '=', 'c.id');
                                $join->on('e.is_primary', '=', DB::raw("1"));
                            })
                            ->leftJoin('media_store AS ms', function ($join) {
                                $join->on('ms.entity_id', '=', 'u.id');
                                $join->on('ms.entity_name', '=', DB::raw("'" . env('USER_AVATAR_ENTITY_NAME') . "'"));
                            })
                            ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.title_id')
                            ->where('u.username', $usernameOrEmail)
                            ->orWhere('e.email_address', $usernameOrEmail)
                            ->first();
                        break;
                    case 'O':
                        $user = DB::table('users AS u')
                            ->select('*', 'u.id AS id', 'o.name AS organisation_name')
                            ->join('contacts AS c', 'c.id', '=', 'u.contact_id')
                            ->join('organisations AS o', 'c.id', '=', 'o.contact_id')
                            ->leftJoin('email AS e', function ($join) {
                                $join->on('e.contact_id', '=', 'c.id');
                                $join->on('e.is_primary', '=', DB::raw("1"));
                            })
                            ->leftJoin('media_store AS ms', function ($join) {
                                $join->on('ms.entity_id', '=', 'u.id');
                                $join->on('ms.entity_name', '=', DB::raw("'" . env('USER_AVATAR_ENTITY_NAME') . "'"));
                            })
                            ->where('u.username', $usernameOrEmail)
                            ->orWhere('e.email_address', $usernameOrEmail)
                            ->first();
                        break;
                }
            }
        } catch (Exception $ex) {
            throw $ex;
        }
        return $user;
    }

    /**
     * Get virification token
     * 
     * @param array $user_data
     * 
     * @return string
     */
    public function getVirificationToken($user_data)
    {
        return strtoupper(md5(json_encode([
            'email' => $user_data['email'],
            'verification_code' => '123'
        ])));
    }

    public function generateRandomCode($length = 4)
    {
        return rand(pow(10, $length - 1), pow(10, $length) - 1);
    }

    public function getUserInfo($id)
    {
        $user = DB::table('users AS u')
            ->select(
                'u.id AS id',
                DB::raw('CONCAT(p.first_name," ", p.last_name) AS fullName'),
                'ms.id AS media_store_id',
                'msis.id AS media_store_image_size_id',
                'msis.content AS media_store_content',
                'ms.mime_type AS media_store_mime_type',
            )
            ->join('contacts AS c', 'c.id', '=', 'u.contact_id')
            ->join('persons AS p', 'c.id', '=', 'p.contact_id')
            ->leftJoin('media_store AS ms', function ($join) {
                $join->on('ms.entity_id', '=', 'u.id');
                $join->on('ms.entity_name', '=', DB::raw("'" . env('USER_AVATAR_ENTITY_NAME') . "'"));
            })
            ->leftJoin('media_store_image_sizes AS msis', function ($join) {
                $join->on('ms.id', '=', 'msis.media_store_id');
                $join->on('msis.width', '=', DB::raw("'48'"));
            })
            ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.title_id')
            ->where('u.id', $id)
            ->first();

        $user->imgPath = $user->media_store_id ? route('media.image.download', ['id' => $user->media_store_id, 'resize_width' => 48]) : null;
        $user->imgBlob = $user->media_store_image_size_id ? ('data:' . $user->media_store_mime_type . ';base64,' . base64_encode($user->media_store_content)) : null;

        unset($user->id, $user->media_store_id, $user->media_store_content, $user->media_store_mime_type, $user->media_store_image_size_id);

        return $user;
    }

    private function generateToken(): string
    {
        $key = config('app.key');
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        return hash_hmac('sha256', Str::random(40), $key);
    }

    public function checkPasswordResetToken(string $token, array $updateData = []): array
    {
        try {
            $recQuery = SfyPasswordReset::where(['token' => $token]);
            $rec = $recQuery->first();

            if (!$rec || $token !== $rec->token) {
                throw new Exception('Invalid Token! Please use the link from your email and try again.', 403);
            }

            if (!empty($updateData)) {
                if ($updateData['verificationCode'] !== $rec->code) {
                    throw new Exception('Invalid verification code!', 403);
                }

                if ($updateData['newPassword'] !== $updateData['confirmNewPassword']) {
                    throw new Exception('Password and confirmation must match!', 400);
                }

                $user = User::find($rec->user_id);
                $user->password = bcrypt($updateData['newPassword']);
                $user->verified = true;
                $user->save();

                $recQuery->delete();
            }

            $result = [
                'success' => true,
            ];
        } catch (\Throwable $th) {
            $result = [
                'success' => false,
                'error' => [
                    'msg' => $th->getMessage(),
                    'code' => $th->getCode()
                ]
            ];
        }

        return $result;
    }

    public function getLoggedInResponseData(array $params): array
    {
        $messagesCounts = $this->systemMessageService->getUserMessagesCounts($params['user']);

        $appInitialisingData = $this->settingsService->getAppInitialisingData(true, false);

        if (isset($appInitialisingData['error'])) {
            throw new Exception($appInitialisingData['error'], 500);
        }

        $initData = array_merge($appInitialisingData, [
            'processes' => [
                'notifications' => $messagesCounts['notifications']
            ],
        ]);

        return [
            'token' => $params['access_token'],
            'expires_in' => $params['expires_in'],
            // 'user' => $params['user'],
            'user' => $this->formatUser($params['user']),
            'initData' => $initData
        ];
    }

    public function formatUser(User $user): array
    {
        $userImg = MediaStore::where([
            'entity_name' => 'UserImage',
            'entity_id' => (string)$user->id
        ])->first();

        $userImgId = $userImg->id ?? null;

        return [
            'id' => $user->id,
            'username' => $user->username,
            'image_id' => $userImg ? $userImg->id : null,
            'image' => $userImgId ? route('media.image.download', ['id' => $userImgId, 'resize_width' => 32]) . '?t=' . time() : null,
            'roles' => $user->roles()->get()->pluck('name'),
        ];
    }
}
