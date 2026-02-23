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
                        $enLangCode->id => '<h1 style="text-align: justify;">Privacy Policy</h1>',
                        $arLangCode->id => '<h1 style="text-align: justify;">سياسة الخصوصية</h1>',
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
