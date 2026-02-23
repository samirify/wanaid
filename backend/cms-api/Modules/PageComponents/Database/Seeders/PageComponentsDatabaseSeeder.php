<?php

namespace Modules\PageComponents\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\ApplicationCode;
use Modules\Core\Models\ApplicationCodeType;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;
use Modules\Core\Services\Constants;
use Modules\PageComponents\Models\Page;
use Modules\PageComponents\Models\PageContent;
use Modules\PageComponents\Models\PageSection;
use Modules\PageComponents\Models\PageWidget;

class PageComponentsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        PageWidget::create([
            'code' => Constants::AC_PAGE_SECTION_LIST_WIDGET,
            'name' => Constants::AC_PAGE_SECTION_WIDGET_ARRAY[Constants::AC_PAGE_SECTION_LIST_WIDGET],
            'active' => true
        ]);

        PageWidget::create([
            'code' => Constants::AC_PAGE_SECTION_PRICING_WIDGET,
            'name' => Constants::AC_PAGE_SECTION_WIDGET_ARRAY[Constants::AC_PAGE_SECTION_PRICING_WIDGET],
            'active' => true
        ]);

        PageWidget::create([
            'code' => Constants::AC_PAGE_SECTION_SEARCH_WIDGET,
            'name' => Constants::AC_PAGE_SECTION_WIDGET_ARRAY[Constants::AC_PAGE_SECTION_SEARCH_WIDGET],
            'active' => true
        ]);

        ApplicationCodeType::create([
            'code' => 'PAGE_HEADER_SIZE',
            'name' => 'Page header sizes',
        ]);

        $pageHeaderSizesType = DB::table('application_code_type')->where('code', 'PAGE_HEADER_SIZE')->first();

        $pageHeaderSizesTypes = [
            'Q' => 'Quarter page',
            'H' => 'Half page',
            'F' => 'Full page',
        ];

        foreach ($pageHeaderSizesTypes as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $pageHeaderSizesType->id,
                'code' => $code,
                'name' => $value,
            ]);
        }

        $headerFullSize = DB::table('application_code AS ac')
            ->select('ac.id', 'ac.name')
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            ->where([
                'act.code' => 'PAGE_HEADER_SIZE',
                'ac.code' => 'F'
            ])
            ->first();

        $headerHalfSize = DB::table('application_code AS ac')
            ->select('ac.id', 'ac.name')
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            ->where([
                'act.code' => 'PAGE_HEADER_SIZE',
                'ac.code' => 'H'
            ])
            ->first();

        Page::create([
            'code' => Constants::PAGE_CODE_MAIN,
            'name' => 'Main Page',
            'header_size_id' => $headerFullSize->id
        ]);

        Page::create([
            'code' => Constants::PAGE_CODE_ABOUT,
            'name' => 'About Us',
            'header_size_id' => $headerHalfSize->id
        ]);

        $mainPage = DB::table('pages')->where('code', Constants::PAGE_CODE_MAIN)->first();

        PageSection::create([
            'pages_id' => $mainPage->id,
            'code' => 'HEADER',
            'name' => 'Main Page Headers',
        ]);

        PageSection::create([
            'pages_id' => $mainPage->id,
            'code' => 'PILLARS',
            'name' => 'Main Page Pillars',
        ]);

        $mainPageHeaderSection = DB::table('page_sections')->where([
            'code' => 'HEADER',
            'pages_id' => $mainPage->id,
        ])->first();

        PageContent::create([
            'page_sections_id' => $mainPageHeaderSection->id,
            'code' => 'main_header_top',
            'value' => 'PAGE_' . $mainPage->id . '_MAIN_HEADER_TOP',
            'active' => 1,
        ]);

        PageContent::create([
            'page_sections_id' => $mainPageHeaderSection->id,
            'code' => 'main_header_middle_big',
            'value' => 'PAGE_' . $mainPage->id . '_MAIN_HEADER_MIDDLE_BIG',
            'active' => 1,
        ]);

        PageContent::create([
            'page_sections_id' => $mainPageHeaderSection->id,
            'code' => 'main_header_bottom',
            'value' => 'PAGE_' . $mainPage->id . '_MAIN_HEADER_BOTTOM',
            'active' => 1,
        ]);

        $aboutUsPage = DB::table('pages')->where('code', Constants::PAGE_CODE_ABOUT)->first();

        PageSection::create([
            'pages_id' => $aboutUsPage->id,
            'code' => 'PILLARS',
            'name' => 'About Us Page Pillars',
        ]);

        PageSection::create([
            'pages_id' => $aboutUsPage->id,
            'code' => 'HEADER',
            'name' => 'About Us Page Headers',
        ]);

        $aboutUsPageHeaderSection = DB::table('page_sections')->where([
            'code' => 'HEADER',
            'pages_id' => $aboutUsPage->id,
        ])->first();

        PageContent::create([
            'page_sections_id' => $aboutUsPageHeaderSection->id,
            'code' => 'main_header_middle_big',
            'value' => 'PAGE_' . $aboutUsPage->id . '_MAIN_HEADER_MIDDLE_BIG',
            'active' => 1,
        ]);

        PageContent::create([
            'page_sections_id' => $aboutUsPageHeaderSection->id,
            'code' => 'main_header_bottom',
            'value' => 'PAGE_' . $aboutUsPage->id . '_MAIN_HEADER_BOTTOM',
            'active' => 1,
        ]);


        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();

        // English Language
        $enLangCode = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        // Arabic Language
        $arLangCode = DB::table('language')->where('locales_id', $arabicLocale->id)->first();

        $generalTranslations = [
            'PAGE_' . $mainPage->id . '_MAIN_HEADER_TOP' => [
                $enLangCode->id => "Welcome to Samirify",
                $arLangCode->id => 'مرحبًا بكم في Samirify',
            ],
            'PAGE_' . $mainPage->id . '_MAIN_HEADER_MIDDLE_BIG' => [
                $enLangCode->id => 'One, two, three ...',
                $arLangCode->id => 'واحد، اثنين، ثلاثة ...',
            ],
            'PAGE_' . $mainPage->id . '_MAIN_HEADER_BOTTOM' => [
                $enLangCode->id => 'Join the club and start tracking now :)',
                $arLangCode->id => 'انضم الينا وابدأ التتبع الآن :)',
            ],
            'PAGE_' . $aboutUsPage->id . '_MAIN_HEADER_MIDDLE_BIG' => [
                $enLangCode->id => 'Who we are',
                $arLangCode->id => 'من نحن',
            ],
            'PAGE_' . $aboutUsPage->id . '_MAIN_HEADER_BOTTOM' => [
                $enLangCode->id => '',
                $arLangCode->id => '',
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

        $this->call(PageComponentsViewsDatabaseSeeder::class);
    }
}
