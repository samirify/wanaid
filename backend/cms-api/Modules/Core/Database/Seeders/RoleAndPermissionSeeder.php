<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Services\Constants;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Permission::create(['name' => 'all']);

        // Permission::create(['name' => 'create-users']);
        // Permission::create(['name' => 'edit-users']);
        // Permission::create(['name' => 'delete-users']);

        // Permission::create(['name' => 'create-blog-posts']);
        // Permission::create(['name' => 'edit-blog-posts']);
        // Permission::create(['name' => 'delete-blog-posts']);

        $ownerRole = Role::create(['name' => Constants::USER_ROLE_OWNER, 'guard_name' => 'api']);
        $adminRole = Role::create(['name' => Constants::USER_ROLE_ADMIN, 'guard_name' => 'api']);
        $editorRole = Role::create(['name' => Constants::USER_ROLE_EDITOR, 'guard_name' => 'api']);
        $userRole = Role::create(['name' => Constants::USER_ROLE_USER, 'guard_name' => 'api']);

        $ownerRole->givePermissionTo(Permission::all());
        $adminRole->givePermissionTo(Permission::all());
        $editorRole->givePermissionTo(Permission::all());
        $userRole->givePermissionTo(Permission::all());

        // $adminRole->givePermissionTo([
        //     'create-users',
        //     'edit-users',
        //     'delete-users',
        //     'create-blog-posts',
        //     'edit-blog-posts',
        //     'delete-blog-posts',
        // ]);

        // $editorRole->givePermissionTo([
        //     'create-blog-posts',
        //     'edit-blog-posts',
        //     'delete-blog-posts',
        // ]);
    }
}
