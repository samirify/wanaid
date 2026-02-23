<?php

namespace Modules\Team\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamViewsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("DROP VIEW IF EXISTS v_team");
        DB::statement("
        CREATE OR REPLACE VIEW v_team AS 
        SELECT 
          `t`.`id` as `id`, 
          `t`.`unique_title` as `unique_title`, 
          (
            SELECT 
              lt.text 
            FROM 
              language_translation lt 
              LEFT JOIN language_code lc ON lc.id = lt.language_code_id 
              LEFT JOIN language l ON l.id = lt.language_id 
            WHERE 
              l.default = 1 
              AND lc.code = d.name
          ) AS department_name, 
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
                  AND lc.code = p.middle_names
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
          (
            SELECT 
              lt.text 
            FROM 
              language_translation lt 
              LEFT JOIN language_code lc ON lc.id = lt.language_code_id 
              LEFT JOIN language l ON l.id = lt.language_id 
            WHERE 
              l.default = 1 
              AND lc.code = t.position
          ) AS position, 
          (
            SELECT 
              lt.text 
            FROM 
              language_translation lt 
              LEFT JOIN language_code lc ON lc.id = lt.language_code_id 
              LEFT JOIN language l ON l.id = lt.language_id 
            WHERE 
              l.default = 1 
              AND lc.code = t.short_description
          ) AS short_description, 
          (
            SELECT 
              lt.text 
            FROM 
              language_translation lt 
              LEFT JOIN language_code lc ON lc.id = lt.language_code_id 
              LEFT JOIN language l ON l.id = lt.language_id 
            WHERE 
              l.default = 1 
              AND lc.code = t.description
          ) AS description, 
          `t`.`order` as `order`, 
          `t`.`show_on_web` as `show_on_web`, 
          `ms`.`id` as `media_store_id`, 
          `ms`.`mime_type` as `mime_type`, 
          `ms`.`content` as `img_content`, 
          `t`.`created_at` as `created_at`, 
          `t`.`updated_at` as `updated_at` 
        FROM 
          `team` as `t` 
          left join `persons` as `p` on `t`.`contact_id` = `p`.`contact_id` 
          left join `departments` as `d` on `t`.`departments_id` = `d`.`id` 
          left join `media_store` as `ms` on `ms`.`entity_id` = `t`.`id` 
          and `ms`.`entity_name` = 'TeamMemberImage'        
        ");
    }
}
