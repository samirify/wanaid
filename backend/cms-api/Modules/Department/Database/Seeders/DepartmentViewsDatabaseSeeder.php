<?php

namespace Modules\Department\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentViewsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("DROP VIEW IF EXISTS v_departments");
        DB::statement("
            CREATE OR REPLACE VIEW v_departments AS 
                SELECT 
                    `d`.`id` as `id`, 
                    `d`.`unique_title` as `unique_title`, 
                    (
                        SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.default = 1
                            AND lc.code = d.name
                    ) as `name`,
                    (
                        SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.default = 1
                            AND lc.code = d.sub_header
                    ) AS `sub_header`, 
                    `d`.`order` as `order`, 
                    `d`.`created_at` as `created_at`, 
                    `d`.`updated_at` as `updated_at`
                FROM `departments` as `d`
        ");
    }
}
