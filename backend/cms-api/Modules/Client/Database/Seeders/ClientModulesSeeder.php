<?php

namespace Modules\Client\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Client\Models\ClientModule;
use Modules\Client\Models\ClientModuleCategory;
use Modules\Client\Services\ClientModulesService;

/**
 * Seeds Blog and Charity Causes into client_modules and creates their
 * module tables (cl_blog, cl_charity_causes) and views.
 * Delete-before-insert for make reset workflow.
 */
class ClientModulesSeeder extends Seeder
{
    public function run(): void
    {
        $clientModulesService = app(ClientModulesService::class);

        $codes = ['blog', 'causes'];

        foreach ($codes as $code) {
            $module = ClientModule::where('code', $code)->first();
            if ($module) {
                $clientModulesService->removeModuleTableAndView($module);
                $module->delete();
            }
        }

        $blogCategory = ClientModuleCategory::where('code', 'blog')->firstOrFail();
        $charityCausesCategory = ClientModuleCategory::where('code', 'charity_causes')->firstOrFail();

        ClientModule::create([
            'name' => 'Blog',
            'code' => 'blog',
            'category_id' => $blogCategory->id,
            'active' => true,
        ]);

        ClientModule::create([
            'name' => 'Charity Causes',
            'code' => 'causes',
            'category_id' => $charityCausesCategory->id,
            'active' => true,
        ]);

        // Tables cl_blog and cl_causes are created by migration
        // 2025_02_24_000002_create_blog_and_charity_causes_module_tables
    }
}
