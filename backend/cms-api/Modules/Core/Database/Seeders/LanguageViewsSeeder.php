<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageViewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("DROP VIEW IF EXISTS v_languages");
        DB::statement("
            CREATE OR REPLACE VIEW v_languages AS 
                SELECT 
                    `l`.`id` as `id`, 
                    `c`.`formatted_name` as `country_name`, 
                    `lo`.`language` as `locale_name`, 
                    `l`.`name` as `name`, 
                    `l`.`direction` as `direction`, 
                    `l`.`default` as `default`, 
                    `l`.`active` as `active`, 
                    `l`.`available` as `available`, 
                    `l`.`created_at` as `created_at` 
                FROM 
                    `language` as `l` 
                    left join `countries` as `c` on `c`.`id` = `l`.`countries_id` 
                    left join `locales` as `lo` on `lo`.`id` = `l`.`locales_id`
        ");
    }
}
