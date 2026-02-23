<?php

namespace Modules\Core\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Client;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Token;
use Modules\Core\Mail\InitiationCodeEmail;
use Modules\Core\Models\Setting;
use Modules\Core\Models\User;
use Modules\Core\Services\Constants;
use Modules\Core\Services\SettingsService;
use Modules\Core\Services\UserService;

class AuthController extends Controller
{
    use SAAApiResponse, AppHelperTrait;

    /**
     * AuthController constructor.
     * @param AccessTokenController $accessTokenController
     */
    public function __construct(
        private readonly AccessTokenController $accessTokenController,
        private readonly UserService $userService,
        private readonly SettingsService $settingsService,
    ) {
    }

    // PROD-TODO: Disable before releasing to production
    public function resetDb(string $code = null, Request $request)
    {
        try {
            $noAuthCode = config('client.admin_panel.reset_db.no_auth_code');

            if ((!is_null($code) && $code === $noAuthCode) || (Auth::check())) {

                $registration = $request->get('registration', null);

                if ($registration) {
                    config(['client.registration' => $registration]);
                }

                Artisan::call('migrate:fresh', [
                    '--force' => true,
                    '--seed' => true,
                    '--drop-views' => true
                ]);

                $passportPersonalAccessClient = Client::where([
                    'personal_access_client' => true,
                    'password_client' => false,
                ])->first();

                if ($passportPersonalAccessClient) {
                    $this->updateEnvValues([
                        'PASSPORT_PERSONAL_ACCESS_CLIENT_ID' => $passportPersonalAccessClient->id,
                        'PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET' => $passportPersonalAccessClient->secret,
                    ]);
                }

                $passportPasswordGrantClient = Client::where([
                    'personal_access_client' => false,
                    'password_client' => true,
                ])->first();

                if ($passportPasswordGrantClient) {
                    $this->updateEnvValues([
                        'PASSPORT_PASSWORD_CLIENT_ID' => $passportPasswordGrantClient->id,
                        'PASSPORT_PASSWORD_CLIENT_SECRET' => $passportPasswordGrantClient->secret,
                    ]);
                }

                // Allow 'calling' host by default
                $clientIp = $request->ip();
                $clientHost = $request->host();

                $this->updateEnvValues([
                    'CLIENT_API_PRIVATE_ALLOWED_HOSTS' => ($clientIp ? $clientIp . ',' : '') . ($clientHost ? $clientHost : ''),
                ]);

                return $this->successResponse([
                    'msg' => 'Successfully reset database!'
                ]);
            } else {
                throw new Exception('Unable to reset DB!', 403);
            }
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;

        return $this->successResponse($success);
    }

    /**
     * Login api
     *
     */
    public function login(Request $request)
    {
        if (!$this->settingsService->checkApplicationInitiated()) {
            return $this->errorResponse('Account has not been finalised!', 403, [
                'initiated' => false
            ]);
        }

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            /** @var User */
            $user = Auth::user();

            $request = Request::create(route('passport.token'), 'POST', [
                'grant_type' => 'password',
                'client_id' => env("PASSPORT_PASSWORD_CLIENT_ID"),
                'client_secret' => env("PASSPORT_PASSWORD_CLIENT_SECRET"),
                'username' => $user->username,
                'password' => $request->password,
                'scope' => '',
            ]);

            $result = app()->handle($request);
            $refreshTokenResponse = json_decode($result->getContent(), true);

            if (isset($refreshTokenResponse['error'])) {
                return $this->errorResponse($refreshTokenResponse['error_description'], 401);
            }

            $cookie = cookie('refresh_token', $refreshTokenResponse['refresh_token'], 60 * 24); // 1 day

            $loggedInResponseData = $this->userService->getLoggedInResponseData([
                'access_token' => $refreshTokenResponse['access_token'],
                'expires_in' => $refreshTokenResponse['expires_in'],
                'user' => $user,
            ]);

            return $this->successResponse($loggedInResponseData, Response::HTTP_OK, $cookie);
        } else {
            return $this->errorResponse('Incorrect username or password!', 401);
        }
    }

    public function logout(): Response
    {
        $cookie = Cookie::forget('refresh_token');

        return $this->successResponse([
            'message' => 'Successfully logged out'
        ], Response::HTTP_OK, $cookie);
    }

    public function refreshToken(Request $request)
    {
        try {
            if (!$this->settingsService->checkApplicationInitiated()) {
                return $this->errorResponse('Account has not been finalised!', 403, [
                    'initiated' => false
                ]);
            }

            $cookies = $request->cookies->all();

            if (!isset($cookies['refresh_token'])) {
                throw new Exception('Unauthenticated!', 401);
            }

            $request = Request::create(route('passport.token'), 'POST', [
                'grant_type' => 'refresh_token',
                'client_id' => env("PASSPORT_PASSWORD_CLIENT_ID"),
                'client_secret' => env("PASSPORT_PASSWORD_CLIENT_SECRET"),
                'refresh_token' => $cookies['refresh_token'],
                'scope' => '',
            ]);

            $result = app()->handle($request);
            $refreshTokenResponse = json_decode($result->getContent(), true);

            $accessToken = $refreshTokenResponse['access_token'] ?? null;

            if (empty($accessToken)) {
                throw new Exception('Unauthenticated!', 401);
            }

            $token_parts = explode('.', $accessToken);
            $token_header = $token_parts[1];
            $token_header_json = base64_decode($token_header);
            $token_header_array = json_decode($token_header_json, true);
            $token_id = $token_header_array['jti'];

            $user = Token::find($token_id)->user;

            $cookie = cookie('refresh_token', $refreshTokenResponse['refresh_token'], 60 * 24); // 1 day

            $loggedInResponseData = $this->userService->getLoggedInResponseData([
                'access_token' => $refreshTokenResponse['access_token'],
                'expires_in' => $refreshTokenResponse['expires_in'],
                'user' => $user,
            ]);

            return $this->successResponse($loggedInResponseData, Response::HTTP_OK, $cookie);
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Finalise account api
     *
     */
    public function finaliseAccount(Request $request)
    {
        $owner = $this->userService->getUserByUsernameOrEmail($request->email);

        if ($owner) {
            $initiated = false;
            $validCode = false;

            $initiationFlag = Setting::firstOrNew([
                'name' => Constants::APP_IS_INITIATED
            ]);

            $initiationCode = Setting::firstOrNew([
                'name' => Constants::APP_INITIATION_CODE,
                'is_public' => false,
            ]);

            if (isset($request->code)) {
                $validCode = $initiationCode && strtolower($initiationCode->value) === strtolower($request->code);

                if (!$validCode) {
                    return $this->errorResponse('Incorrect code! Please check your email.', 403);
                }

                if ($request->new_password !== $request->new_password_confirmation) {
                    return $this->errorResponse('New and Confirmation passwords must match!', 400);
                }

                $user = User::find($owner->id);
                $user->password = bcrypt($request->new_password);
                $user->verified = true;
                $user->save();

                $initiationFlag->value = 1;
                $initiationFlag->save();

                $initiationCode->value = null;
                $initiationCode->save();

                $initiated = true;
            } else {
                $code = rand(10000, 99999);

                Mail::to($request->email)->send(new InitiationCodeEmail([
                    'code' => $code,
                    'username' => $owner->username,
                ]));

                $initiationCode->value = $code;
                $initiationCode->save();
            }

            return $this->successResponse([
                'validEmail' => true,
                'validCode' => $validCode,
                'initiated' => $initiated
            ], Response::HTTP_OK);
        } else {
            return $this->errorResponse('Incorrect email!', 403);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $user = $this->userService->getUserByEmail($request->email);

            if (!$user) {
                throw new Exception('Account does not exist!', 404);
            }

            $this->userService->sendPasswordResetEmail($user->id, $request->email);

            return $this->successResponse();
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }

    public function checkPasswordResetToken(string $token)
    {
        $checkToken = $this->userService->checkPasswordResetToken($token);

        if ($checkToken['success']) {
            return $this->successResponse([
                'validEmail' => true,
                'validToken' => true,
            ], Response::HTTP_OK);
        } else {
            return $this->errorResponse($checkToken['error']['msg'], $checkToken['error']['code']);
        }
    }

    public function updatePassword(Request $request)
    {
        $checkToken = $this->userService->checkPasswordResetToken($request->token, [
            'verificationCode' => $request->verification_code,
            'newPassword' => $request->new_password,
            'confirmNewPassword' => $request->new_password_confirmation,
        ]);

        if ($checkToken['success']) {
            return $this->successResponse([
                'validToken' => true,
            ], Response::HTTP_OK);
        } else {
            return $this->errorResponse($checkToken['error']['msg'], $checkToken['error']['code']);
        }
    }
}
