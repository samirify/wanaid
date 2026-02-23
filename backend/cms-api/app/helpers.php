<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\ApplicationCode;
use Modules\Core\Models\ApplicationCodeType;
use Modules\Core\Models\Language;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;
use Modules\Core\Models\Organisation;
use Modules\Core\Models\Person;
use Modules\Core\Models\StatusChange;
use Modules\Core\Services\Constants;

if (!function_exists('determineBool')) {
    function determineBool(mixed $val): bool
    {
        return $val === true || (string)$val === 'true' || (string)$val === '1' || (int)$val === 1;
    }
}

if (!function_exists('activeLanguages')) {
    function activeLanguages()
    {
        $languages = [];

        try {
            $query = DB::table('language AS l', 'lt.language_id', '=', 'l.id')
                ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
                ->select(
                    'l.id AS id',
                    'l.name AS name',
                    'l.direction AS direction',
                    'l.default AS default',
                    'lo.locale AS locale'
                )
                ->where([
                    'l.active' => 1
                ])
                ->orderBy('l.default', 'desc');

            $langs = $query->get();

            foreach ($langs as $language) {
                $languages[$language->id] = $language;
            }
        } catch (Exception $ex) {
            throw $ex;
        }

        return $languages;
    }
}

if (!function_exists('getDefaultLanguage')) {
    function getDefaultLanguage()
    {
        try {
            return Language::where(['default' => 1])->first();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}

if (!function_exists('getLanguageTranslation')) {
    function getLanguageTranslation(
        string $translation_code,
        ?string $lang = null,
        bool $returnObject = false,
        bool $supportEmptyTranslation = true
    ) {
        $translation = $translation_code;

        $record = null;

        if (!empty($translation_code)) {
            $params = [
                'lc.code' => $translation_code
            ];

            try {
                if (is_null($lang)) {
                    $defaultLang = getDefaultLanguage();
                    $params['l.default'] = $defaultLang->id;
                } else {
                    $params['l.id'] = $lang;
                }

                $query = DB::table('language_translation AS lt')
                    ->leftJoin('language AS l', 'lt.language_id', '=', 'l.id')
                    ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
                    ->leftJoin('language_code AS lc', 'lc.id', '=', 'lt.language_code_id')
                    ->select(
                        'lc.id AS id',
                        'lc.code AS code',
                        'lt.text AS translation'
                    )
                    ->where($params);

                $record = $query->first();

                $translation = $record ? $record->translation : ($supportEmptyTranslation ? '' : $translation);
            } catch (Exception $ex) {
                throw $ex;
            }
        }

        return $returnObject ? $record : $translation;
    }
}

if (!function_exists('getLanguageByLocale')) {
    function getLanguageByLocale(?string $locale = null): ?Language
    {
        $language = null;

        if (!is_null($locale)) {
            $language = Language::from('language AS l')
                ->join('locales AS lo', 'lo.id', '=', 'l.locales_id')
                ->select(
                    'l.id AS id',
                )
                ->where([
                    'lo.locale' => $locale
                ])
                ->first();
        }

        return $language;
    }
}

if (!function_exists('updateStatusChange')) {
    function updateStatusChange($data = [])
    {
        try {
            $message = $data['message'] ?? null;
            $statusFromId = $data['status_from_id'] ?? null;
            $statusToId = $data['status_to_id'] ?? null;

            if (
                !empty($message)
                ||
                ($statusFromId !== $statusToId)
            ) {
                StatusChange::create($data);
            }
        } catch (Exception $ex) {
            throw $ex;
        }

        return true;
    }
}

if (!function_exists('createNewApplicationCode')) {
    function createNewApplicationCode($applicationCodeType, $applicationCodes = [])
    {
        try {
            $newApplicationCodeType = ApplicationCodeType::create($applicationCodeType);

            foreach ($applicationCodes as $code => $value) {
                ApplicationCode::create([
                    'application_code_type_id' => $newApplicationCodeType->id,
                    'code' => $code,
                    'name' => $value,
                ]);
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}

if (!function_exists('createUserStampFields')) {
    function createUserStampFields(Blueprint $table): void
    {
        try {
            $table->unsignedBigInteger('created_by')->nullable();
            // $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable();
            // $table->foreign('updated_by')->references('id')->on('users');
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}


if (!function_exists('getCodesTranslations')) {
    function getCodesTranslations(array $translationCodes = [], bool $useId = false): array
    {
        $translations = [];

        if (!empty($translationCodes)) {
            $translationCodes = array_flip($translationCodes);

            // Step 2: change case of new keys to upper
            $translationCodes = array_change_key_case($translationCodes, CASE_UPPER);

            // Step 3: reverse the flip process to 
            // regain strings as value
            $translationCodes = array_flip($translationCodes);

            try {
                $query = DB::table('language_translation AS lt')
                    ->leftJoin('language AS l', 'lt.language_id', '=', 'l.id')
                    ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
                    ->leftJoin('language_code AS lc', 'lc.id', '=', 'lt.language_code_id')
                    ->select(
                        'l.id AS lang_id',
                        'lo.locale AS locale',
                        'lc.code AS code',
                        'lt.text AS translation'
                    )
                    ->whereIn('lc.code', $translationCodes);

                $recs = $query->get()->toArray();

                foreach ($recs as $record) {
                    $translations[$useId ? $record->lang_id : $record->locale][$record->code] = $record->translation;
                }

                // $translation = $record ? $record->translation : $translation;
            } catch (Exception $ex) {
                throw $ex;
            }
        }

        return $translations;
    }
}


if (!function_exists('updateLanguageLabelTranslations')) {
    function updateLanguageLabelTranslations(): void
    {
        $allLanguages = Language::all();
        foreach ($allLanguages as $language) {
            $lc = 'LANG_' . $language->id . '_NAME';

            $langCode = LanguageCode::where([
                'code' => $lc,
            ])->first();

            $translation = LanguageTranslation::where([
                'language_id' => $language->id,
                'language_code_id' => $langCode->id,
            ])->first();

            foreach ($allLanguages as $lang) {
                LanguageTranslation::updateOrCreate([
                    'language_id' => $lang->id,
                    'language_code_id' => $langCode->id,
                ], [
                    'text' =>  $translation->text
                ]);
            }
        }
    }
}

if (!function_exists('generateRandomString')) {
    function generateRandomString(int $length = 10, string $case = 'upper'): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        switch (strtolower($case)) {
            case 'upper':
                return strtoupper($randomString);
            case 'lower':
                return strtoupper($randomString);
            default:
                return $randomString;
        }
    }
}


if (!function_exists('insertApplicationCodes')) {
    function insertApplicationCodes(string $typeCode, array $codes = []): void
    {
        $applicationCodeType = DB::table('application_code_type')->where('code', $typeCode)->first();

        foreach ($codes as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $applicationCodeType->id,
                'code' => $code,
                'name' => $value,
            ]);
        }
    }
}

if (!function_exists('getPersonFullName')) {
    function getPersonFullName(Person $person, string $locale = 'en'): string
    {
        $language = DB::table('language AS l')
            ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
            ->select('l.id AS id', 'l.direction AS direction')
            ->where([
                'lo.locale' => $locale
            ])
            ->first();

        return
            getLanguageTranslation($person->first_name, $language->id) . ' ' .
            getLanguageTranslation($person->middle_names, $language->id) . ' ' .
            getLanguageTranslation($person->last_name, $language->id);
    }
}

if (!function_exists('getTitles')) {
    function getTitles(string $locale = 'en'): array
    {
        $titleType = DB::table('application_code_type')->where('code', 'TITLES')->first();

        return
            DB::table('application_code')
            ->select('id', 'name')
            ->where('application_code_type_id', $titleType->id)
            ->get()
            ->toArray();
    }
}

if (!function_exists('getAvailableSocialMedia')) {
    function getAvailableSocialMedia(): array
    {
        return
            DB::table('application_code AS ac')
            ->select('ac.id AS id', 'ac.name AS name', DB::raw("LOWER(ac.code) AS code"))
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            ->where([
                'act.code' => Constants::ACT_SOCIAL_MEDIA_BRANDS,
            ])
            ->get()
            ->toArray();
    }
}

if (!function_exists('getMainOrganisation')) {
    function getMainOrganisation(): ?Organisation
    {
        return Organisation::where(['is_main' => 1])->first();
    }
}


if (!function_exists('getAvailableLanguages')) {
    function getAvailableLanguages(bool $onWeb = true): array
    {
        $query = DB::table('language AS l')
            ->select('l.id AS id', 'c.iso AS country_code', 'lo.locale AS locale', 'l.name AS name', 'l.direction AS direction', 'l.default AS default')
            ->leftJoin('countries AS c', 'c.id', '=', 'l.countries_id')
            ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
            ->where('active', 1);

        if ($onWeb) $query->where('available', 1);

        return $query->get()->toArray();
    }
}

if (!function_exists('extractMetaKeywordsFromLangCode')) {
    function extractMetaKeywordsFromLangCode(array $langCodes): string
    {
        $keywords = [];

        $translations = getCodesTranslations($langCodes);

        foreach ($translations as $translation) {
            foreach ($translation as $value) {
                if (!empty($value)) $keywords = array_merge($keywords, explode(' ', $value));
            }
        }

        return implode(',', array_unique($keywords));
    }
}

if (!function_exists('getClientSubDomain')) {
    function getClientSubDomain(): ?string
    {
        $subdomain = null;

        if ($_ENV['APP_ENV'] === 'production') { //development
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $hostParts = explode('.', $host);

            if (empty($host) || !array_intersect(['samirify', 'net'], $hostParts)) {
                dd('Error locating client!');
                throw new Exception('Error locating client!');
            }

            $subdomain = $hostParts[0];
        }


        return $subdomain;
    }
}
