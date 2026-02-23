<?php

namespace Modules\Client\Database\Seeders;

use App\Traits\AppHelperTrait;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;
use Modules\Core\Models\Navigation;
use Modules\Core\Services\Constants;

class NavigationSeeder extends Seeder
{
    use AppHelperTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $mainPage = DB::table('pages')->where('code', Constants::PAGE_CODE_MAIN)->first();
        $aboutPage = DB::table('pages')->where('code', Constants::PAGE_CODE_ABOUT)->first();
        $termsOfUsePage = DB::table('pages')->where('code', Constants::PAGE_CODE_TERMS_OF_USE)->first();
        $privacyPolicyPage = DB::table('pages')->where('code', Constants::PAGE_CODE_PRIVACY_POLICY)->first();
        $disclaimerPage = DB::table('pages')->where('code', Constants::PAGE_CODE_DISCLAIMER)->first();

        $topMenuItems = [
            [
                'key' => md5('NAV_HOME_LABEL'),
                'label' => 'NAV_HOME_LABEL',
                'path' => '/',
                'pathLocation' => 'internal',
                'pathId' => $mainPage->id
            ],
            [
                'key' => md5('NAV_ABOUT_LABEL'),
                'label' => 'NAV_ABOUT_LABEL',
                'children' => [
                    [
                        'key' => md5('NAV_WHO_ARE_WE_LABEL'),
                        'label' => 'NAV_WHO_ARE_WE_LABEL',
                        'path' => '/' . Constants::PAGE_CODE_ABOUT,
                        'pathLocation' => 'internal',
                        'pathId' => $aboutPage->id
                    ],
                    [
                        'key' => md5('NAV_TERMS_OF_USE_LABEL'),
                        'label' => 'NAV_TERMS_OF_USE_LABEL',
                        'path' => '/' . Constants::PAGE_CODE_TERMS_OF_USE,
                        'pathLocation' => 'internal',
                        'pathId' => $termsOfUsePage->id
                    ],
                ]
            ],
        ];

        Navigation::create([
            'code' => 'top',
            'name' => 'Top Navigation',
            'value' => json_encode($topMenuItems)
        ]);

        $topRightMenuItems = [
            [
                'key' => md5('NAV_GET_STARTED_LABEL'),
                'label' => 'NAV_GET_STARTED_LABEL',
                'path' => '/' . Constants::PAGE_CODE_ABOUT,
                'pathLocation' => 'internal',
                'pathId' => $aboutPage->id,
                'nodeStyle' => 'button'
            ],
        ];

        Navigation::create([
            'code' => 'top-right',
            'name' => 'Top Right Navigation',
            'value' => json_encode($topRightMenuItems)
        ]);

        $footerMenuItems = [
            [
                'key' => md5('NAV_USEFUL_LINKS_LABEL'),
                'label' => 'NAV_USEFUL_LINKS_LABEL',
                'children' => [
                    [
                        'key' => md5('NAV_FAQ_LABEL'),
                        'label' => 'NAV_FAQ_LABEL',
                        'path' => '/' . Constants::PAGE_CODE_ABOUT,
                        'pathLocation' => 'internal',
                        'pathId' => $aboutPage->id
                    ],
                    [
                        'key' => md5('NAV_HELP_LABEL'),
                        'label' => 'NAV_HELP_LABEL',
                        'path' => '/' . Constants::PAGE_CODE_ABOUT,
                        'pathLocation' => 'internal',
                        'pathId' => $aboutPage->id
                    ],
                    [
                        'key' => md5('NAV_SUPPORT_LABEL'),
                        'label' => 'NAV_SUPPORT_LABEL',
                        'path' => '/' . Constants::PAGE_CODE_ABOUT,
                        'pathLocation' => 'internal',
                        'pathId' => $aboutPage->id
                    ],
                ]
            ],
            [
                'key' => md5('NAV_LEGAL_LABEL'),
                'label' => 'NAV_LEGAL_LABEL',
                'children' => [
                    [
                        'key' => md5('NAV_TERMS_OF_USE_LABEL'),
                        'label' => 'NAV_TERMS_OF_USE_LABEL',
                        'path' => '/' . Constants::PAGE_CODE_TERMS_OF_USE,
                        'pathLocation' => 'internal',
                        'pathId' => $termsOfUsePage->id
                    ],
                    [
                        'key' => md5('NAV_PRIVACY_POLICY_LABEL'),
                        'label' => 'NAV_PRIVACY_POLICY_LABEL',
                        'path' => '/' . Constants::PAGE_CODE_PRIVACY_POLICY,
                        'pathLocation' => 'internal',
                        'pathId' => $privacyPolicyPage->id
                    ],
                    [
                        'key' => md5('NAV_DISCLAIMER_LABEL'),
                        'label' => 'NAV_DISCLAIMER_LABEL',
                        'path' => '/' . Constants::PAGE_CODE_DISCLAIMER,
                        'pathLocation' => 'internal',
                        'pathId' => $disclaimerPage->id
                    ],
                ]
            ],
            [
                'key' => md5('NAV_MORE_LABEL'),
                'label' => 'NAV_MORE_LABEL',
                'children' => [
                    [
                        'key' => md5('NAV_ABOUT_LABEL'),
                        'label' => 'NAV_ABOUT_LABEL',
                        'path' => '/' . Constants::PAGE_CODE_ABOUT,
                        'pathLocation' => 'internal',
                        'pathId' => $aboutPage->id
                    ],
                    [
                        'key' => md5('NAV_CONTACT_LABEL'),
                        'label' => 'NAV_CONTACT_LABEL',
                        'path' => '/' . Constants::PAGE_CODE_ABOUT,
                        'pathLocation' => 'internal',
                        'pathId' => $aboutPage->id
                    ],
                ]
            ],
        ];

        Navigation::create([
            'code' => 'footer',
            'name' => 'Footer Navigation',
            'value' => json_encode($footerMenuItems)
        ]);

        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();

        // English Language
        $enLangCode = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        // Arabic Language
        $arLangCode = DB::table('language')->where('locales_id', $arabicLocale->id)->first();

        $generalTranslations = [
            // Navigation labels
            'NAV_HOME_LABEL' => [
                $enLangCode->id => 'Home',
                $arLangCode->id => 'الرئيسية',
            ],
            'NAV_ABOUT_LABEL' => [
                $enLangCode->id => 'About',
                $arLangCode->id => 'حول الموقع',
            ],
            'NAV_CONTACT_LABEL' => [
                $enLangCode->id => 'Contact',
                $arLangCode->id => 'اتصل بنا',
            ],
            'NAV_ABOUT_LABEL' => [
                $enLangCode->id => 'About',
                $arLangCode->id => 'حول الموقع',
            ],
            'NAV_USEFUL_LINKS_LABEL' => [
                $enLangCode->id => 'Useful Links',
                $arLangCode->id => 'روابط مفيدة',
            ],
            'NAV_HELP_LABEL' => [
                $enLangCode->id => 'Help',
                $arLangCode->id => 'المساعدة',
            ],
            'NAV_FAQ_LABEL' => [
                $enLangCode->id => 'FAQ',
                $arLangCode->id => 'التعليمات',
            ],
            'NAV_SUPPORT_LABEL' => [
                $enLangCode->id => 'Support',
                $arLangCode->id => 'الدعم',
            ],
            'NAV_LEGAL_LABEL' => [
                $enLangCode->id => 'Legal links',
                $arLangCode->id => 'الروابط القانونية',
            ],
            'NAV_TERMS_OF_USE_LABEL' => [
                $enLangCode->id => 'Terms Of Use',
                $arLangCode->id => 'تعليمات الاستخدام',
            ],
            'NAV_DISCLAIMER_LABEL' => [
                $enLangCode->id => 'Disclaimer',
                $arLangCode->id => 'إخلاء المسؤولية',
            ],
            'NAV_PRIVACY_POLICY_LABEL' => [
                $enLangCode->id => 'Privacy Policy',
                $arLangCode->id => 'سياسة الخصوصية',
            ],
            'NAV_MORE_LABEL' => [
                $enLangCode->id => 'More',
                $arLangCode->id => 'المزيد',
            ],
            'NAV_GET_STARTED_LABEL' => [
                $enLangCode->id => 'Get Started',
                $arLangCode->id => 'لنبدأ',
            ],
            'NAV_WHO_ARE_WE_LABEL' => [
                $enLangCode->id => 'Who are we?',
                $arLangCode->id => 'من نحن؟',
            ],
        ];

        foreach ($generalTranslations as $code => $translation) {
            $langCode = LanguageCode::create([
                'code' => $code,
                'is_html' => $translation['is_html'] ?? false
            ]);

            LanguageTranslation::create([
                'language_id' => $enLangCode->id,
                'language_code_id' => $langCode->id,
                'text' => $translation[$enLangCode->id]
            ]);

            LanguageTranslation::create([
                'language_id' => $arLangCode->id,
                'language_code_id' => $langCode->id,
                'text' => $translation[$arLangCode->id]
            ]);
        }
    }
}
