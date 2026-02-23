<?php

namespace Modules\Core\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Services\UserService;
use Modules\Core\Traits\MediaTrait;

class ProfileController extends Controller
{
    use AppHelperTrait;
    use SAAApiResponse;
    use MediaTrait;

    public function __construct(
        private readonly UserService $userService
    ) {
    }

    /**
     * Show the user's profile.
     *
     * @return Response
     */
    public function show()
    {
        $currentUser = auth('api')->user();

        if (!$currentUser) {
            return $this->errorResponse('User record was not found!', 404);
        }

        $user = $this->userService->getUserById($currentUser->id);

        return $this->successResponse([
            'profile' => $user,
            'translations' => getCodesTranslations([
                'USER_' . $user->id . '_FIRST_NAME',
                'USER_' . $user->id . '_MIDDLE_NAMES',
                'USER_' . $user->id . '_LAST_NAME',
            ])
        ]);
    }

    /**
     * Update the user's profile.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'    => 'required|max:255',
            'middle_names'  => 'max:255',
            'last_name'     => 'required|max:255',
            'email'         => 'required',
            // 'user_image'    => 'mimes:' . config('client.images.allowed_mime_types'),
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        $currentUser = auth('api')->user();

        $updatePassword = false;

        try {
            if ($request->current_password || $request->new_password || $request->confirm_password) {
                if (!$request->current_password) {
                    throw new Exception('Please enter your current password');
                } else {
                    if (!Hash::check($request->current_password, $currentUser->password)) {
                        throw new Exception('Your current password does not match the password you entered!');
                    }
                }

                if (!$request->new_password) {
                    throw new Exception('You must enter a new password');
                }

                if (!$request->confirm_password) {
                    throw new Exception('You must enter your new password confirmation');
                }

                if ($request->new_password !== $request->confirm_password) {
                    throw new Exception('Your new password must match the confirm password!');
                }

                $updatePassword = true;
            }
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }

        return $this->userService->updateUser($currentUser, $request, true, $updatePassword);
    }
}
