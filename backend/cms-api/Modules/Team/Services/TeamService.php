<?php

namespace Modules\Team\Services;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Contact;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\MediaStore;
use Modules\Core\Models\Person;
use Modules\Core\Services\Constants;
use Modules\Core\Services\SocialMediaService;
use Modules\Core\Services\TranslationService;
use Modules\Core\Traits\MediaTrait;
use Modules\Department\Entities\Department;
use Modules\Department\Services\DepartmentService;
use Modules\Team\Models\TeamMember;

class TeamService
{
    use AppHelperTrait, SAAApiResponse, MediaTrait;

    public function __construct(
        private readonly TranslationService $translationService,
        private readonly DepartmentService $departmentService,
        private readonly SocialMediaService $socialMediaService,
    ) {
    }

    /**
     * Fetches one team member 
     * @param string|array $where
     * 
     * @return $result
     */
    public function getTeamMember($where = [], $requestedLang = null)
    {
        $teamMember = null;

        $query = DB::table('team AS t')
            ->leftJoin('persons AS p', function ($join) {
                $join->on('t.contact_id', '=', 'p.contact_id');
            })
            ->leftJoin('media_store AS ms', function ($join) {
                $join->on('ms.entity_id', '=', 't.id');
                $join->on('ms.entity_name', '=', DB::raw("'TeamMemberImage'"));
            })
            ->leftJoin('departments AS d', 'd.id', '=', 't.departments_id')
            ->select(
                't.id AS id',
                't.unique_title AS unique_title',
                't.contact_id AS contact_id',
                'p.title_id AS titles_id',
                'p.date_of_birth AS date_of_birth',
                'd.id AS departments_id',
                't.show_on_web AS show_on_web',
                't.order AS order',
                'ms.id AS media_store_id'
            );

        if (!is_null($requestedLang)) {
            $query
                ->selectRaw('p.first_name AS first_name, (
                    SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.id = ?
                            AND lc.code = p.first_name
                        ) AS first_name', [$requestedLang])
                ->selectRaw('p.middle_names AS middle_names, (
                    SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.id = ?
                            AND lc.code = p.middle_names
                        ) AS middle_names', [$requestedLang])
                ->selectRaw('p.last_name AS last_name, (
                    SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.id = ?
                            AND lc.code = p.last_name
                        ) AS last_name', [$requestedLang])
                ->selectRaw('position, (
                    SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.id = ?
                            AND lc.code = t.position
                        ) AS position', [$requestedLang])
                ->selectRaw('short_description, (
                    SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.id = ?
                            AND lc.code = t.short_description
                        ) AS short_description', [$requestedLang])

                ->selectRaw('description, (
                    SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.id = ?
                            AND lc.code = t.description
                        ) AS description', [$requestedLang])
                ->selectRaw('d.name AS department, (
                    SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.id = ?
                            AND lc.code = d.name
                        ) AS department', [$requestedLang]);
        } else {
            $query
                ->addSelect('p.first_name AS first_name')
                ->addSelect('p.middle_names AS middle_names')
                ->addSelect('p.last_name AS last_name')
                ->addSelect('t.position AS position')
                ->addSelect('t.short_description AS short_description')
                ->addSelect('t.description AS description')
                ->addSelect('d.name AS department');
        }

        if (count($where) > 0) {
            $query->where($where);
        }

        $teamMember = $query->first();

        if (!$teamMember) {
            throw new Exception('Team Member not found!', Response::HTTP_NOT_FOUND);
        }

        $teamMemberArray = json_decode(json_encode($teamMember, true), true);

        $teamMemberArray['img_url'] = $teamMemberArray['media_store_id'] ? route('media.image.download', ['id' => $teamMemberArray['media_store_id']]) : null;

        $teamMemberArray['social_media'] = $this->socialMediaService->getSocialMediaByContactId($teamMemberArray['contact_id']);

        return $teamMemberArray;
    }

    public function getTeamMembersForWeb(string $requestedLang = null): array
    {
        $teamQuery = DB::table('team AS t')
            ->leftJoin('persons AS p', function ($join) {
                $join->on('t.contact_id', '=', 'p.contact_id');
            })
            ->leftJoin('departments AS d', 'd.id', '=', 't.departments_id')
            ->leftJoin('media_store AS ms', function ($join) {
                $join->on('ms.entity_id', '=', 't.id');
                $join->on('ms.entity_name', '=', DB::raw("'TeamMemberImage'"));
            })
            ->select(
                't.id AS id',
                't.unique_title AS unique_title',
                't.contact_id AS contact_id',
                'd.unique_title AS department_unique_title',
                'ms.id AS media_store_id',
                'ms.mime_type AS mime_type',
                'ms.content AS img_content'
            );


        if (!is_null($requestedLang)) {
            $teamQuery
                ->selectRaw('p.first_name AS first_name, (
                        SELECT lt.text FROM language_translation lt
                                LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                                LEFT JOIN language l ON l.id = lt.language_id
                                WHERE l.id = ?
                                AND lc.code = p.first_name
                            ) AS first_name', [$requestedLang])
                ->selectRaw('p.middle_names AS middle_names, (
                        SELECT lt.text FROM language_translation lt
                                LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                                LEFT JOIN language l ON l.id = lt.language_id
                                WHERE l.id = ?
                                AND lc.code = p.middle_names
                            ) AS middle_names', [$requestedLang])
                ->selectRaw('p.last_name AS last_name, (
                        SELECT lt.text FROM language_translation lt
                                LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                                LEFT JOIN language l ON l.id = lt.language_id
                                WHERE l.id = ?
                                AND lc.code = p.last_name
                            ) AS last_name', [$requestedLang])
                ->selectRaw('position, (
                        SELECT lt.text FROM language_translation lt
                                LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                                LEFT JOIN language l ON l.id = lt.language_id
                                WHERE l.id = ?
                                AND lc.code = t.position
                            ) AS position', [$requestedLang])
                ->selectRaw('short_description, (
                        SELECT lt.text FROM language_translation lt
                                LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                                LEFT JOIN language l ON l.id = lt.language_id
                                WHERE l.id = ?
                                AND lc.code = t.short_description
                            ) AS short_description', [$requestedLang])

                ->selectRaw('description, (
                        SELECT lt.text FROM language_translation lt
                                LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                                LEFT JOIN language l ON l.id = lt.language_id
                                WHERE l.id = ?
                                AND lc.code = t.description
                            ) AS description', [$requestedLang])
                ->selectRaw('d.name AS department, (
                        SELECT lt.text FROM language_translation lt
                                LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                                LEFT JOIN language l ON l.id = lt.language_id
                                WHERE l.id = ?
                                AND lc.code = d.name
                            ) AS department', [$requestedLang])

                ->selectRaw('d.sub_header AS department_sub_header, (
                    SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.id = ?
                            AND lc.code = d.sub_header
                        ) AS department_sub_header', [$requestedLang]);
        } else {
            $teamQuery
                ->addSelect('p.first_name AS first_name')
                ->addSelect('p.middle_names AS middle_names')
                ->addSelect('p.last_name AS last_name')
                ->addSelect('t.position AS position')
                ->addSelect('t.short_description AS short_description')
                ->addSelect('t.description AS description')
                ->addSelect('d.name AS department')
                ->addSelect('d.sub_header AS department_sub_header');
        }

        $teamQuery->orderBy('d.order', 'asc')
            ->orderBy('t.order', 'asc')
            ->where([
                't.show_on_web' => 1,
            ]);

        $teamList = $teamQuery->get()->toArray();
        $teams = [];

        foreach ($teamList as $teamMember) {
            $teamMember->img_url = $teamMember->media_store_id ? route('media.image.download', ['id' => $teamMember->media_store_id]) : null;
            $teamMember->social_media = $this->socialMediaService->getSocialMediaByContactId($teamMember->contact_id);
            unset($teamMember->img_content);
            $teams[$teamMember->department_unique_title]['members'][] = $teamMember;
        }

        return array_map(function ($department_unique_title, $team) use ($teamMember) {
            return array(
                'department_uri' => $department_unique_title,
                'department_name' => $teamMember->department,
                'department_sub_header' => $teamMember->department_sub_header,
                'team'  => $team
            );
        }, array_keys($teams), $teams);
    }

    public function createTeamMember(Request $request): Response|ResponseFactory|JsonResponse
    {
        DB::beginTransaction();

        try {
            $contactType = DB::table('application_code_type')->where('code', Constants::ACT_CONTACT_TYPES)->first();

            $contact = Contact::create([
                'contact_type_id' => DB::table('application_code')
                    ->where('application_code_type_id', $contactType->id)
                    ->where('code', Constants::AC_CONTACT_TYPE_PERSON)
                    ->first()->id,
                'reference_no' => isset($user['reference_no']) && !empty($user['reference_no']) ? $user['reference_no'] : $this->formatCode(json_encode($request))
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

            $teamMember = new TeamMember();

            $teamMember->contact_id = $contactId;
            $teamMember->unique_title = '.';
            $teamMember->departments()->associate(Department::find($request->departments_id));
            $teamMember->position = $request->position;
            $teamMember->short_description = $request->short_description;
            $teamMember->description = $request->description;
            $teamMember->order = $request->order;
            $teamMember->show_on_web = isset($request->show_on_web) && ($request->show_on_web === true || $request->show_on_web === 'true');

            $teamMember->save();

            $this->socialMediaService->updateSocialMedia($teamMember->contact_id, $request);

            $prefix = 'TEAM_MEMBER_' . $teamMember->id . '_';

            $teamMember->update([
                'position' => str_replace('TMPSET_', $prefix, $teamMember->position),
                'short_description' => str_replace('TMPSET_', $prefix, $teamMember->short_description),
                'description' => str_replace('TMPSET_', $prefix, $teamMember->description),
            ]);

            $person->update([
                'first_name' => str_replace('TMPSET_', $prefix, $person->first_name),
                'middle_names' => str_replace('TMPSET_', $prefix, $person->middle_names),
                'last_name' => str_replace('TMPSET_', $prefix, $person->last_name),
            ]);

            $this->translationService->translateFields([
                'first_name',
                'middle_names',
                'last_name',
                'position',
                'short_description',
                'description',
            ], $request->all(), $prefix);

            $person = Person::where(['contact_id' => $contactId])->first();

            $uniqueTitle = $this->formatUniqueTitle(getPersonFullName($person));

            if ($request->has('team_member_image') && !is_null($request->team_member_image)) {
                $this->uploadFile($request, [
                    'field_name' => 'team_member_image',
                    'file_name' => $uniqueTitle,
                ], [
                    'name' => 'TeamMemberImage',
                    'id' => $teamMember->id
                ], []);
            }

            $teamMember->update([
                'unique_title' => $uniqueTitle,
            ]);

            DB::commit();
            return $this->successResponse([
                'msg' => 'Created successfully!'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    public function updateTeamMember(int $id, Request $request): Response|ResponseFactory|JsonResponse
    {
        DB::beginTransaction();

        try {
            $teamMember = TeamMember::find($id);

            $person = Person::where(['contact_id' => $teamMember->contact_id])->first();

            $person->title_id = $request->titles_id;
            $person->first_name = $request->first_name;
            $person->middle_names = $request->middle_names;
            $person->last_name = $request->last_name;
            $person->date_of_birth = $request->date_of_birth;

            $person->save();

            $fullName = getPersonFullName($person);

            $teamMember->unique_title = $this->formatUniqueTitle($fullName);
            $teamMember->departments()->associate(Department::find($request->departments_id));
            $teamMember->position = $request->position;
            $teamMember->short_description = $request->short_description;
            $teamMember->description = $request->description;
            $teamMember->order = $request->order;
            $teamMember->show_on_web = isset($request->show_on_web) && ($request->show_on_web === true || $request->show_on_web === 'true');

            $teamMember->save();

            $this->socialMediaService->updateSocialMedia($teamMember->contact_id, $request);

            $removeTeamMemberImage = isset($request->remove_team_member_image) && ($request->remove_team_member_image === true || $request->remove_team_member_image === 'true');

            if ($removeTeamMemberImage) {
                MediaStore::where([
                    'entity_id' => (string)$teamMember->id,
                    'entity_name' => 'TeamMemberImage'
                ])->delete();
            } else {
                if ($request->has('team_member_image') && !is_null($request->team_member_image)) {
                    $this->uploadFile($request, [
                        'field_name' => 'team_member_image',
                        'file_name' => $teamMember->unique_title,
                    ], [
                        'name' => 'TeamMemberImage',
                        'id' => $teamMember->id
                    ], []);
                }
            }

            $prefix = 'TEAM_MEMBER_' . $teamMember->id . '_';
            $this->translationService->translateFields([
                'first_name',
                'middle_names',
                'last_name',
                'position',
                'short_description',
                'description',
            ], $request->all(), $prefix);

            DB::commit();
            return $this->successResponse([
                'msg' => $fullName . ' was updated successfully!'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    public function deleteTeamMember(int $id): Response|ResponseFactory|JsonResponse
    {
        DB::beginTransaction();
        try {
            $teamMember = TeamMember::find($id);

            $person = Person::where([
                'contact_id' => $teamMember->contact_id,
            ])->first();

            $fullName = getPersonFullName($person);

            // Clean translations
            LanguageCode::whereIn('code', [
                $teamMember->position,
                $teamMember->short_description,
                $teamMember->description,
                $person->first_name,
                $person->middle_names,
                $person->last_name,
            ])->delete();

            $person->delete();

            $teamMember->delete();

            Contact::where([
                'id' => $teamMember->contact_id,
            ])->delete();

            MediaStore::where([
                'entity_id' => (string)$id,
                'entity_name' => 'TeamMemberImage'
            ])->delete();

            DB::commit();
            return $this->successResponse([
                'msg' => 'Member ' . $fullName . ' was deleted successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }
}
