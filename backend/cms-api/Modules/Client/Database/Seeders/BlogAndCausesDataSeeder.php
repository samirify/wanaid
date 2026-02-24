<?php

namespace Modules\Client\Database\Seeders;

use App\Traits\AppHelperTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrates blog and charity causes data from production (old schema) into
 * the new module tables cl_blog and cl_causes.
 * Delete-before-insert for make reset workflow.
 */
class BlogAndCausesDataSeeder extends Seeder
{
    use AppHelperTrait;

    private const RECORD_CODE_PREFIX = 'CMR';

    public function run(): void
    {
        $this->seedBlogs();
        $this->seedCauses();
    }

    private function seedBlogs(): void
    {
        $table = 'cl_blog';
        if (!Schema::hasTable($table)) {
            $this->command?->warn("Table {$table} does not exist; skipping blog data seed.");
            return;
        }
        DB::table($table)->delete();

        $rows = [
            [
                'id' => 2,
                'unique_title' => 'wan-aid-report-july-24-2024',
                'page_title' => 'WAN Aid Report July 24, 2024',
                'title' => 'BLOG_2_TITLE',
                'short_description' => 'BLOG_2_SHORT_DESCRIPTION',
                'body' => 'BLOG_2_BODY',
                'created_by_name' => 'Dallya Alhorri',
                'user_id' => 5,
                'published_at' => '2024-07-30 08:12:23',
                'active' => 1,
                'created_at' => '2024-07-30 14:12:23',
                'updated_at' => '2024-08-02 17:38:44',
            ],
            [
                'id' => 3,
                'unique_title' => 'atlak-mbadr-oan-ayd-lldaam-almgtmaay-taazyz-altmask-alagtmaaay-odaam-almtathryn-balhrb-nfsya',
                'page_title' => 'إطلاق مبادرة وان إيد للدعم المجتمعي: تعزيز التماسك الاجتماعي ودعم المتأثرين بالحرب نفسيًا',
                'title' => 'BLOG_3_TITLE',
                'short_description' => 'BLOG_3_SHORT_DESCRIPTION',
                'body' => 'BLOG_3_BODY',
                'created_by_name' => 'Dallya Alhorri',
                'user_id' => 5,
                'published_at' => '2025-02-05 12:31:04',
                'active' => 1,
                'created_at' => '2025-02-05 19:30:48',
                'updated_at' => '2025-02-05 23:22:07',
            ],
            [
                'id' => 4,
                'unique_title' => 'the-psychology-of-war-and-its-impact-on-couples',
                'page_title' => 'The Psychology of War and Its Impact on Couples',
                'title' => 'BLOG_4_TITLE',
                'short_description' => 'BLOG_4_SHORT_DESCRIPTION',
                'body' => 'BLOG_4_BODY',
                'created_by_name' => 'Dallya Alhorri',
                'user_id' => 5,
                'published_at' => '2025-04-02 17:14:40',
                'active' => 1,
                'created_at' => '2025-04-02 23:14:10',
                'updated_at' => '2026-02-17 02:02:33',
            ],
            [
                'id' => 5,
                'unique_title' => 'im-raising-ps500-to-provide-food-water-and-medical-aid-to-the-people-of-sudan',
                'page_title' => 'Iʼm raising £500 to provide food, water and medical aid to the people of Sudan.',
                'title' => 'BLOG_5_TITLE',
                'short_description' => 'BLOG_5_SHORT_DESCRIPTION',
                'body' => 'BLOG_5_BODY',
                'created_by_name' => 'Dallya Alhorri',
                'user_id' => 5,
                'published_at' => '2025-04-02 18:00:39',
                'active' => 1,
                'created_at' => '2025-04-03 00:00:39',
                'updated_at' => '2025-04-03 00:24:37',
            ],
            [
                'id' => 6,
                'unique_title' => 'zakat-al-fitr-ramadan-march-2025',
                'page_title' => 'Zakat Al-Fitr – Ramadan March 2025',
                'title' => 'BLOG_6_TITLE',
                'short_description' => 'BLOG_6_SHORT_DESCRIPTION',
                'body' => 'BLOG_6_BODY',
                'created_by_name' => 'Dallya Alhorri',
                'user_id' => 5,
                'published_at' => '2025-04-10 11:45:32',
                'active' => 1,
                'created_at' => '2025-04-10 17:45:32',
                'updated_at' => '2026-02-16 18:59:17',
            ],
            [
                'id' => 7,
                'unique_title' => 'empowering-sudanese-women-through-leadership-cardiff-workshop-by-wan-aid',
                'page_title' => 'Empowering Sudanese Women Through Leadership – Cardiff Workshop by WAN Aid',
                'title' => 'BLOG_7_TITLE',
                'short_description' => 'BLOG_7_SHORT_DESCRIPTION',
                'body' => 'BLOG_7_BODY',
                'created_by_name' => 'Dallya Alhorri',
                'user_id' => 5,
                'published_at' => '2025-04-17 12:36:37',
                'active' => 1,
                'created_at' => '2025-04-17 18:36:37',
                'updated_at' => '2025-04-17 18:40:44',
            ],
            [
                'id' => 8,
                'unique_title' => 'a-day-of-colour-culture-and-community',
                'page_title' => 'A Day of Colour, Culture, and Community',
                'title' => 'BLOG_8_TITLE',
                'short_description' => 'BLOG_8_SHORT_DESCRIPTION',
                'body' => 'BLOG_8_BODY',
                'created_by_name' => 'Dallya Alhorri',
                'user_id' => 5,
                'published_at' => '2025-04-22 11:09:33',
                'active' => 1,
                'created_at' => '2025-04-22 17:09:33',
                'updated_at' => '2025-04-22 17:17:23',
            ],
            [
                'id' => 9,
                'unique_title' => 'blue-sky-volunteer-organization-in-collaboration-with-wan-aid-launches-a-clothing-distribution-program-for-children-and-mothers-in-omdurman-locality',
                'page_title' => 'Blue Sky Volunteer Organization, in collaboration with WAN Aid, launches a clothing distribution program for children and mothers in Omdurman locality',
                'title' => 'BLOG_9_TITLE',
                'short_description' => 'BLOG_9_SHORT_DESCRIPTION',
                'body' => 'BLOG_9_BODY',
                'created_by_name' => 'WAN Aid',
                'user_id' => 5,
                'published_at' => '2025-10-07 13:53:36',
                'active' => 1,
                'created_at' => '2025-10-07 19:53:36',
                'updated_at' => '2026-02-16 19:16:23',
            ],
        ];

        foreach ($rows as $row) {
            DB::table($table)->insert([
                'id' => $row['id'],
                'code' => $this->generateRecordCodeFromIdAndPrefix($row['id'], self::RECORD_CODE_PREFIX),
                'name' => $row['page_title'],
                'slug' => $row['unique_title'],
                'title' => $row['title'],
                'slogan' => null,
                'short_description' => $row['short_description'],
                'full_description' => $row['body'],
                'published_at' => $row['published_at'],
                'active' => $row['active'],
                'options' => json_encode([
                    'user_id' => $row['user_id'],
                    'created_by_name' => $row['created_by_name'],
                ]),
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'created_by' => $row['user_id'],
                'updated_by' => $row['user_id'],
            ]);
        }
    }

    private function seedCauses(): void
    {
        $table = 'cl_causes';
        if (!Schema::hasTable($table)) {
            $this->command?->warn("Table {$table} does not exist; skipping causes data seed.");
            return;
        }
        DB::table($table)->delete();

        $rows = [
            [
                'id' => 1,
                'unique_title' => 'help-us',
                'page_title' => 'help-us',
                'title' => 'CAUSE_1_TITLE',
                'target' => 999999.99,
                'currencies_id' => 20,
                'short_description' => 'CAUSE_1_SHORT_DESCRIPTION',
                'body' => 'CAUSE_1_BODY',
                'created_by_name' => '',
                'order' => 1,
                'user_id' => 1,
                'published_at' => null,
                'active' => 1,
                'created_at' => '2021-01-20 07:06:44',
                'updated_at' => '2021-01-20 07:06:44',
            ],
            [
                'id' => 2,
                'unique_title' => 'clean-drinking-water',
                'page_title' => 'clean-drinking-water',
                'title' => 'CAUSE_2_TITLE',
                'target' => 6000.00,
                'currencies_id' => 26,
                'short_description' => 'CAUSE_2_SHORT_DESCRIPTION',
                'body' => 'CAUSE_2_BODY',
                'created_by_name' => 'Dallya Alhorri',
                'order' => 2,
                'user_id' => 1,
                'published_at' => null,
                'active' => 0,
                'created_at' => '2021-01-20 07:06:44',
                'updated_at' => '2025-10-29 18:57:03',
            ],
            [
                'id' => 3,
                'unique_title' => 'school-meals',
                'page_title' => 'school-meals',
                'title' => 'CAUSE_3_TITLE',
                'target' => 9000.00,
                'currencies_id' => 26,
                'short_description' => 'CAUSE_3_SHORT_DESCRIPTION',
                'body' => 'CAUSE_3_BODY',
                'created_by_name' => 'Samir Ibrahim',
                'order' => 1,
                'user_id' => 2,
                'published_at' => '2025-10-14 15:11:45',
                'active' => 1,
                'created_at' => '2021-01-20 07:06:44',
                'updated_at' => '2025-10-15 15:22:11',
            ],
            [
                'id' => 4,
                'unique_title' => 'school-uniform',
                'page_title' => 'school-uniform',
                'title' => 'CAUSE_4_TITLE',
                'target' => 15000.00,
                'currencies_id' => 26,
                'short_description' => 'CAUSE_4_SHORT_DESCRIPTION',
                'body' => 'CAUSE_4_BODY',
                'created_by_name' => 'Dallya Alhorri',
                'order' => 1,
                'user_id' => 2,
                'published_at' => null,
                'active' => 0,
                'created_at' => '2021-01-20 07:06:44',
                'updated_at' => '2025-10-29 18:57:21',
            ],
            [
                'id' => 5,
                'unique_title' => 'school-books',
                'page_title' => 'school-books',
                'title' => 'CAUSE_5_TITLE',
                'target' => 0.00,
                'currencies_id' => 26,
                'short_description' => 'CAUSE_5_SHORT_DESCRIPTION',
                'body' => 'CAUSE_5_BODY',
                'created_by_name' => '',
                'order' => 5,
                'user_id' => 2,
                'published_at' => null,
                'active' => 0,
                'created_at' => '2021-01-20 07:06:44',
                'updated_at' => '2021-01-20 07:06:44',
            ],
            [
                'id' => 6,
                'unique_title' => 'sanitary-towels',
                'page_title' => 'sanitary-towels',
                'title' => 'CAUSE_6_TITLE',
                'target' => 9000.00,
                'currencies_id' => 26,
                'short_description' => 'CAUSE_6_SHORT_DESCRIPTION',
                'body' => 'CAUSE_6_BODY',
                'created_by_name' => 'Samir Ibrahim',
                'order' => 6,
                'user_id' => 1,
                'published_at' => null,
                'active' => 0,
                'created_at' => '2021-01-20 07:06:45',
                'updated_at' => '2025-10-14 21:13:54',
            ],
            [
                'id' => 7,
                'unique_title' => 'zakat-alfitr',
                'page_title' => 'zakat-alfitr',
                'title' => 'CAUSE_7_TITLE',
                'target' => 9000.00,
                'currencies_id' => 26,
                'short_description' => 'CAUSE_7_SHORT_DESCRIPTION',
                'body' => 'CAUSE_7_BODY',
                'created_by_name' => 'Samir Ibrahim',
                'order' => 7,
                'user_id' => 2,
                'published_at' => '2026-02-19 13:55:14',
                'active' => 1,
                'created_at' => '2021-01-20 07:06:45',
                'updated_at' => '2026-02-19 20:55:14',
            ],
        ];

        foreach ($rows as $row) {
            DB::table($table)->insert([
                'id' => $row['id'],
                'code' => $this->generateRecordCodeFromIdAndPrefix($row['id'], self::RECORD_CODE_PREFIX),
                'name' => $row['page_title'],
                'slug' => $row['unique_title'],
                'title' => $row['title'],
                'slogan' => null,
                'short_description' => $row['short_description'],
                'full_description' => $row['body'],
                'published_at' => $row['published_at'],
                'active' => $row['active'],
                'options' => json_encode([
                    'target' => $row['target'],
                    'currencies_id' => $row['currencies_id'],
                    'order' => $row['order'],
                    'user_id' => $row['user_id'],
                    'created_by_name' => $row['created_by_name'],
                ]),
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'created_by' => $row['user_id'],
                'updated_by' => $row['user_id'],
            ]);
        }
    }
}
