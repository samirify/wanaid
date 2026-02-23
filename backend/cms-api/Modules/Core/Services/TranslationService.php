<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Language;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;

class TranslationService
{
    public function __construct(
        private readonly LanguageService $languageService
    ) {}

    /**
     * Fetches language translations 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getTranslations($lang)
    {
        $result = [
            'success' => false,
            'message' => '',
            'translations' => []
        ];

        try {
            $translations = Cache::get('lang_' . $lang . '_translations');

            if ($translations) {
                $result['translations'] = json_decode($translations, true);
            } else {
                $query = DB::table('language_translation AS lt')
                    ->leftJoin('language AS l', 'lt.language_id', '=', 'l.id')
                    ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
                    ->leftJoin('language_code AS lc', 'lc.id', '=', 'lt.language_code_id')
                    ->select(
                        'lc.code AS code',
                        'lt.text AS translation'
                    )
                    ->where([
                        'lo.locale' => $lang
                    ])
                    ->where('lc.code', 'not like', '%APP_COUNTRY_%')
                    ->where('lc.code', 'not like', '%SITE_EMAIL_%');

                $translations = $query->get();

                foreach ($translations as $translation) {
                    $result['translations'][$translation->code] = $translation->translation;
                }

                $this->languageService->publishLanguages();
            }

            $result['success'] = true;
            $result['message'] = 'Results fetched successfully!';
        } catch (Exception $ex) {
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

        return $result;
    }

    public function translateFields($fields = [], $data = [], $codePrefix = '')
    {
        $availableanguages = Language::all();

        foreach ($availableanguages as $language) {
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $fieldValue = isset($data[$field . '_' . $language->id]) ? $data[$field . '_' . $language->id] : null;

                    if (strpos($data[$field], 'TMPSET_') !== false) {
                        $langCode = str_replace('TMPSET_', $codePrefix, $data[$field]);
                    } else {
                        $langCode = $data[$field];
                    }

                    // $langCode = strtoupper($langCode);

                    $languageCode = LanguageCode::firstOrNew([
                        'code' => $langCode,
                    ]);

                    $languageCode->code = $langCode;
                    $languageCode->save();

                    $languageTranslation = LanguageTranslation::firstOrNew([
                        'language_id' => $language->id,
                        'language_code_id' => $languageCode->id,
                    ]);

                    $languageTranslation->language_id = $language->id;
                    $languageTranslation->language_code_id = $languageCode->id;
                    $languageTranslation->text = $fieldValue;

                    $languageTranslation->save();
                }
            }
        }

        $this->languageService->publishLanguages();
    }

    public function translateFieldsByLocale(array $translations): void
    {
        foreach ($translations as $locale => $values) {
            $localeObj = DB::table('locales')->where('locale', $locale)->first();
            $lang = DB::table('language')->where('locales_id', $localeObj->id)->first();

            foreach ($values as $translationCode => $translationValue) {
                $languageCode = LanguageCode::firstOrNew([
                    'code' => $translationCode,
                ]);

                $languageCode->code = $translationCode;
                $languageCode->save();

                $languageTranslation = LanguageTranslation::firstOrNew([
                    'language_id' => $lang->id,
                    'language_code_id' => $languageCode->id,
                ]);

                $languageTranslation->language_id = $lang->id;
                $languageTranslation->language_code_id = $languageCode->id;
                $languageTranslation->text = $translationValue;

                $languageTranslation->save();
            }
        }

        $this->languageService->publishLanguages();
    }
}
