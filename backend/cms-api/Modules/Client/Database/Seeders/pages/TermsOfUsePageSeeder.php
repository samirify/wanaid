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

class TermsOfUsePageSeeder extends Seeder
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

        $termsOfUsePage = Page::create([
            'code' => Constants::PAGE_CODE_TERMS_OF_USE,
            'name' => 'Terms of Use',
            'header_size_id' => $headerQuarterSize->id
        ]);

        $termsOfUsePageHeaderSection = PageSection::create([
            'pages_id' => $termsOfUsePage->id,
            'code' => 'HEADER',
            'name' => 'Terms of Use Page Headers',
        ]);

        $termsOfUsePagePillarSection = PageSection::create([
            'pages_id' => $termsOfUsePage->id,
            'code' => 'PILLARS',
            'name' => 'Terms of Use Page Pillars',
        ]);


        PageContent::create([
            'page_sections_id' => $termsOfUsePageHeaderSection->id,
            'code' => 'main_header_middle_big',
            'value' => 'PAGE_' . $termsOfUsePage->id . '_MAIN_HEADER_MIDDLE_BIG',
            'active' => 1,
        ]);

        $generalTranslations = [
            'PAGE_' . $termsOfUsePage->id . '_MAIN_HEADER_MIDDLE_BIG' => [
                $enLangCode->id => 'Terms Of Use',
                $arLangCode->id => 'تعليمات الاستخدام',
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

        $termsOfUsePageContents = [
            'our-terms-of-use' => [
                'page_sections_id' => $termsOfUsePagePillarSection->id,
                'code' => $this->formatCode('our-terms-of-use' . $termsOfUsePage->id . $termsOfUsePagePillarSection->id),
                'name' => 'Terms Of Use',
                'value' => 'PAGE_SECTION_{PAGE_CONTENT_ID}_VALUE',
                'order' => 1,
                'active' => 1,
                'translations' => [
                    'PAGE_SECTION_{PAGE_CONTENT_ID}_VALUE' => [
                        $enLangCode->id => '<h1 style="text-align: justify;">Terms Of Use</h1>',
                        $arLangCode->id => '<h1 style="text-align: justify;">تعليمات الاستخدام</h1>',
                    ]
                ],
            ]
        ];

        foreach ($termsOfUsePageContents as $pageContentData) {
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
