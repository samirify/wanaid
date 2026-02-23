<?php

namespace Modules\Client\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Country;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;

class CountriesTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        // $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();

        // // English Language
        // $enLangCode = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        // // Arabic Language
        // $arLangCode = DB::table('language')->where('locales_id', $arabicLocale->id)->first();

        // $pageCode = 'APP_COUNTRY_';

        // $countries = DB::table('countries')->get()->toArray();

        // $enPath = config('client.admin_root_folder_location') . '/resources/world-countries/data/en/countries.json';
        // $arPath = config('client.admin_root_folder_location') . '/resources/world-countries/data/ar/countries.json';

        // $finalEnLangCountries = [];
        // $enLangCountries = json_decode(file_get_contents($enPath), true);

        // foreach ($enLangCountries as $enCountry) {
        //     $finalEnLangCountries[strtolower($enCountry['alpha2'])] = $enCountry['name'];
        // }

        // $finalArLangCountries = [];
        // $arLangCountries = json_decode(file_get_contents($arPath), true);

        // foreach ($arLangCountries as $arCountry) {
        //     $finalArLangCountries[strtolower($arCountry['alpha2'])] = $arCountry['name'];
        // }

        // foreach ($countries as $country) {
        //     $langNameTxt = $pageCode . strtoupper(str_replace(' ', '_', $country->name));
            
        //     $_c = Country::find($country->id);
        //     $_c->formatted_name = $langNameTxt;
        //     $_c->save();

        //     if (
        //         array_key_exists(strtolower($country->iso), $finalEnLangCountries)
        //         &&
        //         array_key_exists(strtolower($country->iso), $finalArLangCountries)
        //     ) {
        //         $langCode = LanguageCode::create([
        //             'code' => $langNameTxt,
        //         ]);

        //         LanguageTranslation::create([
        //             'language_id' => $enLangCode->id,
        //             'language_code_id' => $langCode->id,
        //             'text' => $finalEnLangCountries[strtolower($country->iso)]
        //         ]);

        //         LanguageTranslation::create([
        //             'language_id' => $arLangCode->id,
        //             'language_code_id' => $langCode->id,
        //             'text' => $finalArLangCountries[strtolower($country->iso)]
        //         ]);
        //     }
        // }

        // $this->command->info('Countries translations seeded!');
    }
}
