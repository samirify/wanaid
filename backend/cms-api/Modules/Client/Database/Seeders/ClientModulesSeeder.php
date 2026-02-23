<?php

namespace Modules\Client\Database\Seeders;

use App\Traits\AppHelperTrait;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Client\Models\ClientModuleCategory;
use Modules\Client\Models\ClientModuleCategoryCustomColumn;

class ClientModulesSeeder extends Seeder
{
    use AppHelperTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $blogCategory = ClientModuleCategory::create([
            'name' => 'Blog',
            'code' => 'blog',
        ]);

        $blogCustomColums = [
            [
                'name' => 'resource_link',
                'type' => 'string',
            ],
            [
                'name' => 'user_id',
                'type' => 'foreign',
                'foreign_table' => 'users',
                'foreign_column' => 'id',
                'options' => json_encode([
                    'label' => 'Author',
                    'key_column' => 'id',
                    'value_column' => 'username',
                    'filters' => [],
                ])
            ],
        ];

        foreach ($blogCustomColums as $column) {
            ClientModuleCategoryCustomColumn::create(array_merge($column, ['client_module_categories_id' => $blogCategory->id]));
        }

        $charityCausesCategory = ClientModuleCategory::create([
            'name' => 'Charity Causes',
            'code' => 'charity_causes',
        ]);

        $charityCausesCustomColums = [
            [
                'name' => 'price',
                'type' => 'float',
            ],
            [
                'name' => 'target',
                'type' => 'float',
            ],
            [
                'name' => 'currencies_id',
                'type' => 'foreign',
                'foreign_table' => 'currencies',
                'foreign_column' => 'id',
                'options' => json_encode([
                    'label' => 'Currency',
                    'key_column' => 'id',
                    'value_column' => 'name',
                    'filters' => [
                        ['active', '=', true]
                    ],
                ])
            ],
        ];

        foreach ($charityCausesCustomColums as $column) {
            ClientModuleCategoryCustomColumn::create(array_merge($column, ['client_module_categories_id' => $charityCausesCategory->id]));
        }
    }
}
