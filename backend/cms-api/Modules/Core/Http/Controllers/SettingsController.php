<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\SAAApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Core\Models\User;
use Modules\Core\Services\SettingsService;
use Modules\Core\Services\UserService;

class SettingsController extends Controller
{
    use SAAApiResponse;

    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly UserService $userService,
    ) {}

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $bearerToken = $request->bearerToken();
        $bearerTokenData = explode('|', $bearerToken);

        $token = false;

        if (count($bearerTokenData) === 2) {
            $tokenData = PersonalAccessToken::where([
                'id' => $bearerTokenData[0],
                'token' => hash('sha256', $bearerTokenData[1]),
                'tokenable_type' => 'App\\User'
            ]);

            if ($tokenData->exists()) {
                $token = $tokenData->first();
            }
        }

        $user = null;
        if ($token) {
            $user = User::find($token->tokenable_id);
            if ($user) {
                $user->info = $this->userService->getUserInfo($user->id);
            }
        }

        $lang = getLanguageByLocale($request->get('locale', null));

        // $langId = '0';

        // if ($lang) $langId = $lang->id;

        // $cachedAppInitialisingData = Cache::get('init_data_' . $langId);

        // if ($cachedAppInitialisingData) {
        //     $appInitialisingData = json_decode($cachedAppInitialisingData, true);
        // } else {
        //     $appInitialisingData = $this->settingsService->getAppInitialisingData(false, true, $lang);
        //     Cache::put('init_data_' . $langId, json_encode($appInitialisingData));
        // }

        $appInitialisingData = $this->settingsService->getAppInitialisingData(false, true, $lang);

        if (isset($appInitialisingData['error'])) {
            return $this->errorResponse($appInitialisingData['error'], 500);
        }

        return $this->successResponse(array_merge($appInitialisingData, [
            'user' => $user
        ]));
    }
}
