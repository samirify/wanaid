<?php

namespace Modules\PageComponents\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageComponentsViewsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("DROP VIEW IF EXISTS v_pages");
        DB::statement("
            CREATE OR REPLACE VIEW v_pages AS 
                SELECT 
                    `p`.`id` as `id`, 
                    `p`.`code` as `code`, 
                    `p`.`name` as `name`, 
                    `p`.`page_title` as `page_title`, 
                    `p`.`header_size_id` as `header_size_id`, 
                    `p`.`meta_description` as `meta_description`, 
                    `p`.`meta_keywords` as `meta_keywords`, 
                    `p`.`is_template` as `is_template`, 
                    `p`.`created_at` as `created_at`, 
                    `p`.`updated_at` as `updated_at`
                FROM `pages` as `p`
        ");

        DB::statement("DROP VIEW IF EXISTS v_pillars");
        DB::statement("
            CREATE OR REPLACE VIEW v_pillars AS 
                SELECT 
                    `pc`.`id` as `id`, 
                    `pc`.`code` as `code`, 
                    `pc`.`name` as `name`, 
                    `pc`.`value` as `value`, 
                    `pc`.`order` as `order`, 
                    `pc`.`active` as `active`, 
                    `ps`.`id` as `page_sections_id`, 
                    `ps`.`code` as `page_sections_code`, 
                    `ps`.`name` as `page_sections_name`, 
                    `p`.`id` as `pages_id`, 
                    `p`.`code` as `pages_code`, 
                    `p`.`name` as `pages_name`, 
                    `pc`.`created_at` as `created_at`, 
                    `pc`.`updated_at` as `updated_at`, 
                    `ms`.`id` as `media_store_id`, 
                    `ms`.`mime_type` as `mime_type`, 
                    `ms`.`content` as `img_content` 
                FROM 
                    `page_contents` as `pc` 
                    LEFT JOIN `page_sections` as `ps` on `ps`.`id` = `pc`.`page_sections_id` 
                    LEFT JOIN `pages` as `p` on `p`.`id` = `ps`.`pages_id` 
                    LEFT JOIN `media_store` as `ms` on `ms`.`entity_id` = `pc`.`id` and `ms`.`entity_name` = 'PillarImage' 
                WHERE `ps`.`code` = 'PILLARS'
        ");
    }
}
