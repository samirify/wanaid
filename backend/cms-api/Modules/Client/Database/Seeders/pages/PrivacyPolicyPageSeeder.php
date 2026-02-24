<?php

namespace Modules\Client\Database\Seeders\pages;

use App\Traits\AppHelperTrait;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;
use Modules\Core\Services\Constants;
use Modules\PageComponents\Models\Page;
use Modules\PageComponents\Models\PageContent;
use Modules\PageComponents\Models\PageSection;

class PrivacyPolicyPageSeeder extends Seeder
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

        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();

        // English Language
        $enLangCode = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        // Arabic Language
        $arLangCode = DB::table('language')->where('locales_id', $arabicLocale->id)->first();


        $headerQuarterSize = DB::table('application_code AS ac')
            ->select('ac.id', 'ac.name')
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            ->where([
                'act.code' => 'PAGE_HEADER_SIZE',
                'ac.code' => 'Q'
            ])
            ->first();

        $privacyPolicyPage = Page::create([
            'code' => Constants::PAGE_CODE_PRIVACY_POLICY,
            'name' => 'Privacy Policy',
            'header_size_id' => $headerQuarterSize->id
        ]);

        $privacyPolicyPageHeaderSection = PageSection::create([
            'pages_id' => $privacyPolicyPage->id,
            'code' => 'HEADER',
            'name' => 'Privacy Policy Page Headers',
        ]);

        $privacyPolicyPagePillarSection = PageSection::create([
            'pages_id' => $privacyPolicyPage->id,
            'code' => 'PILLARS',
            'name' => 'Privacy Policy Page Pillars',
        ]);


        PageContent::create([
            'page_sections_id' => $privacyPolicyPageHeaderSection->id,
            'code' => 'main_header_middle_big',
            'value' => 'PAGE_' . $privacyPolicyPage->id . '_MAIN_HEADER_MIDDLE_BIG',
            'active' => 1,
        ]);

        $generalTranslations = [
            'PAGE_' . $privacyPolicyPage->id . '_MAIN_HEADER_MIDDLE_BIG' => [
                $enLangCode->id => 'Privacy Policy',
                $arLangCode->id => 'سياسة الخصوصية',
            ],
        ];

        foreach ($generalTranslations as $code => $translation) {
            $langCode = LanguageCode::create([
                'code' => $code,
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

        $privacyPolicyPageContents = [
            'our-privacy-policy' => [
                'page_sections_id' => $privacyPolicyPagePillarSection->id,
                'code' => $this->formatCode('our-privacy-policy' . $privacyPolicyPage->id . $privacyPolicyPagePillarSection->id),
                'name' => 'Privacy Policy',
                'value' => 'PAGE_SECTION_{PAGE_CONTENT_ID}_VALUE',
                'order' => 1,
                'active' => 1,
                'translations' => [
                    'PAGE_SECTION_{PAGE_CONTENT_ID}_VALUE' => [
                        $enLangCode->id => '<p><strong>When we will get in touch</strong></p><p>When you give us your personal details - for example, when you make a donation or register for an event - you may receive follow up information from us directly relevant to this activity, including how your support is helping.</p><p>If you have provided us with consent to use your details for marketing purposes, then we may also contact you about our projects, fundraising activities and appeals. You can opt out at any time.</p><p>We always act on your instructions and we aim to put you in control of your relationship with us. We will not share your data for others\' marketing purposes.</p><p><strong>How to stop or change how we communicate with you</strong></p><p>If at any time you wish to stop or change how we communicate with you, or update the information we hold, please get in touch.</p>',
                        $arLangCode->id => '<p><strong>When we will get in touch</strong></p><p>When you give us your personal details - for example, when you make a donation or register for an event - you may receive follow up information from us directly relevant to this activity, including how your support is helping.</p><p>If you have provided us with consent to use your details for marketing purposes, then we may also contact you about our projects, fundraising activities and appeals. You can opt out at any time.</p><p>We always act on your instructions and we aim to put you in control of your relationship with us. We will not share your data for others\' marketing purposes.</p><p><strong>How to stop or change how we communicate with you</strong></p><p>If at any time you wish to stop or change how we communicate with you, or update the information we hold, please get in touch.</p>',
                    ]
                ],
            ]
        ];

        foreach ($privacyPolicyPageContents as $pageContentData) {
            $translations = $pageContentData['translations'];
            unset($pageContentData['translations']);

            $pageContent = PageContent::create($pageContentData);
            $pageContent->value = str_replace('{PAGE_CONTENT_ID}', $pageContent->id, $pageContentData['value']);
            $pageContent->save();


            foreach ($translations as $code => $translation) {
                $code = str_replace('{PAGE_CONTENT_ID}', $pageContent->id, $code);
                $langCode = LanguageCode::create([
                    'code' => $code,
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
}
