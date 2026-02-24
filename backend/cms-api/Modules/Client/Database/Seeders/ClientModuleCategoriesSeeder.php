<?php

namespace Modules\Client\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\ClientModuleCategory;

/**
 * Seeds Blog and Charity Causes categories.
 * Delete-before-insert for make reset workflow.
 */
class ClientModuleCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $codes = ['blog', 'charity_causes'];

        foreach ($codes as $code) {
            $category = ClientModuleCategory::where('code', $code)->first();
            if ($category) {
                DB::table('client_module_category_custom_columns')
                    ->where('client_module_categories_id', $category->id)
                    ->delete();
                $category->delete();
            }
        }

        ClientModuleCategory::create([
            'name' => 'Blog',
            'code' => 'blog',
        ]);

        ClientModuleCategory::create([
            'name' => 'Charity Causes',
            'code' => 'charity_causes',
        ]);
    }
}
