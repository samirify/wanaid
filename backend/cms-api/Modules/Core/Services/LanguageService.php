<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Language;

class LanguageService
{
    public function setDefaultLang($lang)
    {
        $result = [
            'success' => false,
            'message' => '',
        ];

        DB::beginTransaction();
        try {
            Language::query()->where('default', '=', 1)->update(['default' => 0]);
            Language::find($lang)->update(['default' => 1, 'active' => 1, 'available' => 1]);

            $result['success'] = true;
            $result['message'] = 'Executed successfully!';
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

        return $result;
    }

    public function getGeneralTranslations()
    {
        $result = [
            'success' => false,
            'message' => '',
            'general_translations' => []
        ];

        try {

            $generalTranslations = config('client.site.page.general_translations');

            foreach ($generalTranslations as $code => $item) {
                $translation = getLanguageTranslation($code, null, true);
                array_push($result['general_translations'], [
                    'id' => $translation->id,
                    'code' => $code,
                    'name' => $item['name'],
                    'location' => $item['location'],
                    'text' => $translation ? $translation->translation : ''
                ]);
            }

            $result['success'] = true;
            $result['message'] = 'Executed successfully!';
        } catch (Exception $ex) {
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

        return $result;
    }

    public function getGeneralTranslationByCode($code)
    {
        $result = [
            'success' => false,
            'message' => '',
            'general_translation' => []
        ];

        DB::beginTransaction();
        try {

            $generalTranslations = config('client.site.page.general_translations');

            $result['general_translation'] = [
                'code' => $code,
                'name' => $generalTranslations[$code]['name'],
                'location' => $generalTranslations[$code]['location'],
            ];

            $result['success'] = true;
            $result['message'] = 'Executed successfully!';
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

        return $result;
    }

    public function publishLanguages()
    {
        try {
            $languageTranslationsData = DB::table('language_code AS lc')
                ->leftJoin('language_translation AS lt', 'lc.id', '=', 'lt.language_code_id')
                ->leftJoin('language AS l', 'lt.language_id', '=', 'l.id')
                ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
                ->select(
                    'lo.locale AS locale',
                    'lc.code AS code',
                    'lt.text AS translation'
                )
                ->where([
                    'l.active' => 1
                ])
                ->get()
                ->toArray();

            $ltData = $this->formatLanguageTranslations($languageTranslationsData);

            foreach ($ltData as $locale => $langData) {
                Cache::put('lang_' . $locale . '_translations', json_encode($langData));
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function formatLanguageTranslations(array $languageTranslationsData): array
    {
        $ltData = [];

        foreach ($languageTranslationsData as $languageTranslation) {
            $ltData[$languageTranslation->locale][$languageTranslation->code] = $languageTranslation->translation;
        }

        return $ltData;
    }
}
