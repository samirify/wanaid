<?php

namespace Modules\Core\Services;

use Modules\Client\Models\ClientModule;
use Modules\Client\Services\ClientIdentityService;
use Modules\Client\Services\ClientIdentityThemeService;
use Modules\Core\Models\Language;
use Modules\PageComponents\Services\PageService;
use Modules\Core\Models\Setting;

class SettingsService
{
    public function __construct(
        private readonly SocialMediaService $socialMediaService,
        private readonly ContactService $contactService,
        private readonly PageService $pageService,
        private readonly NavigationService $navigationService,
        private readonly ClientIdentityThemeService $clientIdentityThemeService,
        private readonly ClientIdentityService $clientIdentityService,
        private readonly CurrencyService $currencyService,
    ) {}

    public function mergeSettings($settings, $name = 'name', $value = 'value')
    {
        $mergedSettings = [];

        foreach ($settings as $setting) {
            $mergedSettings[$setting[$name]] = $setting[$value];
        }

        return $mergedSettings;
    }

    public function getLanguages(bool $onWeb): array
    {
        $availableLanguages = getAvailableLanguages($onWeb);

        $countries = [];
        $defaultLang = [];
        $languages = [];
        foreach ($availableLanguages as $lang) {
            if ($lang->default === 1) {
                $defaultLang = $lang;
            }
            $countries[$lang->country_code] = $lang->name;
            $languages[$lang->locale] = $lang;
        }

        return [
            'default' => $defaultLang,
            'countries' => $countries,
            'data' => $languages
        ];
    }

    public function getAppInitialisingData(bool $simple = false, bool $onWeb = true, ?Language $requestedLang = null): array
    {
        try {
            $langId = $requestedLang ? $requestedLang->id : null;

            $result = [
                'languages' => $this->getLanguages($onWeb),
                'currencies' => $this->currencyService->getCurrencies(),
                'available_modules' => $this->getClientModules(),
                'identity' => $this->clientIdentityService->getDefaultClientIdentity($langId)
            ];

            if ($simple) return $result;

            $pagesData = $this->pageService->getPageContents([
                ['p.code', '=', [Constants::PAGE_CODE_MAIN]]
            ], $langId);

            $settingsData = Setting::where('is_public', true)->get()->toArray();

            $settings = [];

            foreach ($settingsData as $setting) {
                $settings[$setting['name']] = $langId ? getLanguageTranslation($setting['value'], $langId) : $setting['value'];
            }

            $mainOrg = getMainOrganisation();

            $clientIdentityTheme = $this->clientIdentityThemeService->getClientIdentityDefaultTheme();

            $result = array_merge($result, [
                'settings' => array_merge($settings, [
                    'organisation' => $mainOrg->name,
                    'social_media' => $this->socialMediaService->getSocialMediaByContactId($mainOrg->contact_id),
                    'main_contacts' => $this->contactService->getMainContactsByContactId($mainOrg->contact_id),
                ]),
                'page_contents' => array_key_exists('page_contents', $pagesData) ? $pagesData['page_contents'] : [],
                'navigation' => $this->navigationService->getNavRecords($requestedLang),
                'theme' => $clientIdentityTheme
            ]);
        } catch (\Throwable $th) {
            $result['error'] = $th->getMessage();
        }

        return $result;
    }

    public function getClientModules(bool $activeRecordsOnly = false)
    {
        $query = ClientModule::select(
            'id',
            'code',
            'name',
            'active',
        )
            ->orderBy('name', 'asc');

        if ($activeRecordsOnly) {
            $query->where('active', '=', true);
        }
        return
            $query->get()
            ->toArray();
    }

    public function checkApplicationInitiated()
    {
        $setting = Setting::where('name', Constants::APP_IS_INITIATED)
            ->first();

        if ($setting && (int)$setting->value === 1) {
            return true;
        }

        return false;
    }
}
