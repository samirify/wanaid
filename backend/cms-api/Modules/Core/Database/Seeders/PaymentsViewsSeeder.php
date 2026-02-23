<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentsViewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("DROP VIEW IF EXISTS v_payments");
        DB::statement("
            CREATE OR REPLACE VIEW v_payments AS 
                SELECT 
                    `p`.`id` as `id`, 
                    `p`.`code` as `code`, 
                    `p`.`amount` as `amount`, 
                    `pm_ac`.`name` as `payment_method`, 
                    `ps_ac`.`name` as `payment_status`, 
                    `ps_ac`.`code` as `payment_status_code`, 
                    `p`.`created_at` as `created_at`, 
                    `p`.`updated_at` as `updated_at`, 
                    `p`.`last_modified_at` as `last_modified_at`, 
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
                            AND lc.code = u_per.first_name
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
                            AND lc.code = u_per.middle_names
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
                            AND lc.code = u_per.last_name
                        ), 
                        ''
                    )
                    ) AS updated_by 
                FROM 
                    `payments` as `p` 
                    left join `application_code` as `pm_ac` on `pm_ac`.`id` = `p`.`payment_method_id` 
                    left join `application_code_type` as `pm_act` on `pm_act`.`id` = `pm_ac`.`application_code_type_id` 
                    left join `application_code` as `ps_ac` on `ps_ac`.`id` = `p`.`status_id` 
                    left join `application_code_type` as `ps_act` on `ps_act`.`id` = `ps_ac`.`application_code_type_id` 
                    left join `users` as `u_u` on `u_u`.`id` = `p`.`updated_by` 
                    left join `contacts` as `u_con` on `u_con`.`id` = `u_u`.`contact_id` 
                    left join `persons` as `u_per` on `u_per`.`contact_id` = `u_con`.`id`          
        ");
    }
}
