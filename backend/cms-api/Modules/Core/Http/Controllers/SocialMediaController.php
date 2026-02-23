<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\SAAApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Core\Services\SettingsService;
use Modules\Core\Services\SocialMediaService;
use Throwable;

class SocialMediaController extends Controller
{
    use SAAApiResponse;

    private $codes;

    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly SocialMediaService $socialMediaService,
    ) {
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request)
    {
        $mainOrg = getMainOrganisation();

        $socialMedia = $this->socialMediaService->getSocialMediaByContactId($mainOrg->contact_id);

        if ($request->isMethod('post')) {
            try {
                $this->socialMediaService->updateSocialMedia($mainOrg->contact_id, $request);

                return $this->successResponse(['msg' => 'Updated successfully!']);
            } catch (Throwable $th) {
                return $this->handleExceptionResponse($th);
            }
        } else {
            return $this->successResponse([
                'available_social_media' => getAvailableSocialMedia(),
                'social_media' => $socialMedia
            ]);
        }
    }
}
