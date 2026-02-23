<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\SocialMedia;

class SocialMediaService
{
    // /**
    //  * Get available social media
    //  * @return array
    //  */
    // public function getAvailableSocialMedia(): array
    // {
    //     try {
    //         return DB::table('application_code AS ac')
    //             ->select('ac.id AS id', 'ac.name AS name')
    //             ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
    //             ->where([
    //                 'act.code' => Constants::ACT_SOCIAL_MEDIA_BRANDS,
    //             ])
    //             ->get()
    //             ->toArray();
    //     } catch (Exception $ex) {
    //         throw $ex;
    //     }
    // }

    public function getSocialMediaByContactId(int $contactId)
    {
        return DB::table('social_media AS sm')
            ->leftJoin('application_code AS ac', 'ac.id', '=', 'sm.brand_id')
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            ->select(DB::raw("LOWER(ac.code) AS code"), 'sm.url AS url')
            ->where([
                'sm.contact_id' => $contactId,
                'sm.is_primary' => 1,
                'act.code' => Constants::ACT_SOCIAL_MEDIA_BRANDS,
            ])->get();
    }

    public function updateSocialMedia(int $contactId, Request $request): void
    {
        $availableSocialMedia = getAvailableSocialMedia();

        foreach ($availableSocialMedia as $socialMediaBrand) {
            $requestParameterExists = $request->has('social-media-' . $socialMediaBrand->code);

            if ($requestParameterExists) {
                $url = $request->get('social-media-' . $socialMediaBrand->code) ?? '';

                if (empty($url)) {
                    SocialMedia::where([
                        'contact_id' => $contactId,
                        'brand_id' => $socialMediaBrand->id,
                        'is_primary' => 1
                    ])->delete();
                } else {
                    $obj = SocialMedia::where([
                        'contact_id' => $contactId,
                        'brand_id' => $socialMediaBrand->id,
                        'is_primary' => 1
                    ])->first();

                    if ($obj) {
                        $obj->url = $url;
                        $obj->save();
                    } else {
                        SocialMedia::create([
                            'contact_id' => $contactId,
                            'brand_id' => $socialMediaBrand->id,
                            'is_primary' => 1,
                            'url' => $request->get('social-media-' . $socialMediaBrand->code),
                        ]);
                    }
                }
            }
        }
    }
}
