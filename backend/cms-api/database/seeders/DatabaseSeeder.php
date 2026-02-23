<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Client\Database\Seeders\ClientDatabaseSeeder;
use Modules\Core\Database\Seeders\CoreDatabaseSeeder;
use Modules\Core\Database\Seeders\PassportDatabaseSeeder;
use Modules\PageComponents\Database\Seeders\PageComponentsDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PassportDatabaseSeeder::class);
        $this->call(CoreDatabaseSeeder::class);
        $this->call(PageComponentsDatabaseSeeder::class);
        $this->call(ClientDatabaseSeeder::class);
    }
}
