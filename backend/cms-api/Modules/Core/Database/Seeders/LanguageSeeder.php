<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $UKCountry = DB::table('countries')->where('iso', 'GB')->first();

        Language::create([
            'countries_id' => $UKCountry->id,
            'name' => 'English',
            'locales_id' => $englishLocale->id,
            'direction' => 'ltr',
            'default' => 1,
            'active' => 1,
            'available' => 1
        ]);

        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();
        $SudanCountry = DB::table('countries')->where('iso', 'SD')->first();

        Language::create([
            'countries_id' => $SudanCountry->id,
            'name' => 'العربية',
            'locales_id' => $arabicLocale->id,
            'direction' => 'rtl',
            'default' => 0,
            'active' => 1,
            'available' => 1
        ]);

        // $italianLocale = DB::table('locales')->where('locale', 'it')->first();
        // $ItalyCountry = DB::table('countries')->where('iso', 'IT')->first();

        // Language::create([
        //     'countries_id' => $ItalyCountry->id,
        //     'name' => 'Italiano',
        //     'locales_id' => $italianLocale->id,
        //     'direction' => 'ltr',
        //     'default' => 0,
        //     'active' => 1,
        //     'available' => 1
        // ]);

        $this->call(LanguageViewsSeeder::class);
    }
}
