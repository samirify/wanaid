<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->delete();

        $now = date('Y-m-d H:i:s');
        $currencies = array(
            array('name' => 'Australian dollar', 'code' => 'AUD', 'symbol' => '$', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Brazilian real', 'code' => 'BRL', 'symbol' => 'R$', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Canadian dollar', 'code' => 'CAD', 'symbol' => '$', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Chinese Renmenbi', 'code' => 'CNY', 'symbol' => '¥', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Czech koruna', 'code' => 'CZK', 'symbol' => 'Kč', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Danish krone', 'code' => 'DKK', 'symbol' => 'kr', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Hong Kong dollar', 'code' => 'HKD', 'symbol' => '$', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Hungarian forint', 'code' => 'HUF', 'symbol' => 'Ft', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Indian rupee', 'code' => 'INR', 'symbol' => 'Rp', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Israeli new shekel', 'code' => 'ILS', 'symbol' => '₪', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Japanese yen', 'code' => 'JPY', 'symbol' => '¥', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Malaysian ringgit', 'code' => 'MYR', 'symbol' => 'RM', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Mexican peso', 'code' => 'MXN', 'symbol' => '$', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'New Taiwan dollar', 'code' => 'TWD', 'symbol' => 'NT$', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'New Zealand dollar', 'code' => 'NZD', 'symbol' => '$', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Norwegian krone', 'code' => 'NOK', 'symbol' => 'kr', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Philippine peso', 'code' => 'PHP', 'symbol' => 'Php', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Polish złoty', 'code' => 'PLN', 'symbol' => 'zł', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Pound sterling', 'code' => 'GBP', 'symbol' => '£', 'active' => 1, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Russian ruble', 'code' => 'RUB', 'symbol' => 'руб', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Singapore dollar', 'code' => 'SGD', 'symbol' => '$', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Swedish krona', 'code' => 'SEK', 'symbol' => 'kr', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Swiss franc', 'code' => 'CHF', 'symbol' => 'CHF', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'Thai baht', 'code' => 'THB', 'symbol' => '฿', 'active' => 0, 'created_at' => $now, 'updated_at' => $now),
            array('name' => 'United States dollar', 'code' => 'USD', 'symbol' => '$', 'active' => 1, 'created_at' => $now, 'updated_at' => $now),
        );

        DB::table('currencies')->insert($currencies);

        DB::table('currencies')->where('code', 'USD')->update(['default' => 1]);
    }
}
