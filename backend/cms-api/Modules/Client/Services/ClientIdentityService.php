<?php

namespace Modules\Client\Services;

use Modules\Client\Models\ClientIdentity;
use Modules\Core\Models\Language;

class ClientIdentityService
{
    public function __construct() {}

    public function getDefaultClientIdentity(Language|int $lang = null): array
    {
        $clientIdentity = ClientIdentity::where(['default' => true])->first();

        $langId = (string)$lang instanceof Language ? $lang->id : $lang;

        return [
            'name' => $lang ? getLanguageTranslation($clientIdentity->business_name, $langId) : $clientIdentity->business_name,
            'slogan' => $lang ? getLanguageTranslation($clientIdentity->business_slogan, $langId) : $clientIdentity->business_slogan,
            'short_description' => $lang ? getLanguageTranslation($clientIdentity->business_short_description, $langId) : $clientIdentity->business_short_description,
        ];
    }
}
