<?php

namespace Modules\Client\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Client\Database\Seeders\pages\DisclaimerPageSeeder;
use Modules\Client\Database\Seeders\pages\PrivacyPolicyPageSeeder;
use Modules\Client\Database\Seeders\pages\TermsOfUsePageSeeder;

class ClientDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(CountriesTranslationSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(ProjectPlannerSeeder::class);
        $this->call(ClientIdentitySeeder::class);
        $this->call(MainPagesSeeder::class);
        $this->call(DepartmentsAndTeamsSeeder::class);
        $this->call(PaymentsSeeder::class);
        $this->call(DisclaimerPageSeeder::class);
        $this->call(TermsOfUsePageSeeder::class);
        $this->call(PrivacyPolicyPageSeeder::class);
        $this->call(NavigationSeeder::class);
        $this->call(ClientModulesSeeder::class);
    }
}
