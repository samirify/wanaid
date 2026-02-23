<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthViewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("DROP VIEW IF EXISTS v_users");
        DB::statement("
            CREATE OR REPLACE VIEW v_users AS 
                SELECT 
                    `u`.`id` as `id`, 
                    `u`.`username` as `username`, 
                    CONCAT(
                    COALESCE(
                        (
                        SELECT 
                            lt.text 
                        FROM 
                            language_translation lt 
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id 
                            LEFT JOIN language l ON l.id = lt.language_id 
                        WHERE 
                            l.default = 1 
                            AND lc.code = p.first_name
                        ), 
                        ''
                    ), 
                    ' ', 
                    COALESCE(
                        (
                        SELECT 
                            lt.text 
                        FROM 
                            language_translation lt 
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id 
                            LEFT JOIN language l ON l.id = lt.language_id 
                        WHERE 
                            l.default = 1 
                            AND lc.code = p.last_name
                        ), 
                        ''
                    )
                    ) AS full_name, 
                    `ms`.`id` as `media_store_id` 
                FROM 
                    `users` as `u` 
                    inner join `contacts` as `c` on `c`.`id` = `u`.`contact_id` 
                    inner join `persons` as `p` on `c`.`id` = `p`.`contact_id` 
                    left join `media_store` as `ms` on `ms`.`entity_id` = `u`.`id` 
                    and `ms`.`entity_name` = 'UserImage'
        ");
    }
}
