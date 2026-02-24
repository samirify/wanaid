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

class DisclaimerPageSeeder extends Seeder
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

        $disclaimerPage = Page::create([
            'code' => Constants::PAGE_CODE_DISCLAIMER,
            'name' => 'Disclaimer',
            'header_size_id' => $headerQuarterSize->id
        ]);

        $disclaimerPageHeaderSection = PageSection::create([
            'pages_id' => $disclaimerPage->id,
            'code' => 'HEADER',
            'name' => 'Disclaimer Page Headers',
        ]);

        $disclaimerPagePillarSection = PageSection::create([
            'pages_id' => $disclaimerPage->id,
            'code' => 'PILLARS',
            'name' => 'Disclaimer Page Pillars',
        ]);


        PageContent::create([
            'page_sections_id' => $disclaimerPageHeaderSection->id,
            'code' => 'main_header_middle_big',
            'value' => 'PAGE_' . $disclaimerPage->id . '_MAIN_HEADER_MIDDLE_BIG',
            'active' => 1,
        ]);

        $generalTranslations = [
            'PAGE_' . $disclaimerPage->id . '_MAIN_HEADER_MIDDLE_BIG' => [
                $enLangCode->id => 'Disclaimer',
                $arLangCode->id => 'إخلاء المسؤولية',
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

        $disclaimerPageContents = [
            'our-disclaimer' => [
                'page_sections_id' => $disclaimerPagePillarSection->id,
                'code' => $this->formatCode('our-disclaimer' . $disclaimerPage->id . $disclaimerPagePillarSection->id),
                'name' => 'Disclaimer',
                'value' => 'PAGE_SECTION_{PAGE_CONTENT_ID}_VALUE',
                'order' => 1,
                'active' => 1,
                'translations' => [
                    'PAGE_SECTION_{PAGE_CONTENT_ID}_VALUE' => [
                        $enLangCode->id => '<p><strong>Disclaimer of Warranty and Limitation of Liability</strong></p>
                <p>The Website is provided &quot;AS IS.&quot; appfigures, its suppliers, officers, directors, employees, and agents
                exclude and disclaim all representations and warranties, express or implied, related to this Website
                or in connection with the Services. You exclude WAN from all liability for damages related to or
                arising out of the use of this Website.</p>',
                        $arLangCode->id => '<p><strong>Disclaimer of Warranty and Limitation of Liability</strong></p>
                <p>The Website is provided &quot;AS IS.&quot; appfigures, its suppliers, officers, directors, employees, and agents
                exclude and disclaim all representations and warranties, express or implied, related to this Website
                or in connection with the Services. You exclude WAN from all liability for damages related to or
                arising out of the use of this Website.</p>',
                    ]
                ],
            ]
        ];

        foreach ($disclaimerPageContents as $pageContentData) {
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
