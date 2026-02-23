<?php

namespace Modules\Core\Database\Seeders;

use App\Traits\AppHelperTrait;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\Database\Factories\ClientFactory;

class PassportDatabaseSeeder extends Seeder
{
    use AppHelperTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ClientFactory::new()
            ->asPersonalAccessTokenClient()
            ->create([
                'name' => 'Samirify CMS Personal Access Client',
                'redirect_uris' => [env('APP_URL', 'http://localhost')],
            ]);

        ClientFactory::new()
            ->asPasswordClient()
            ->create([
                'name' => 'Samirify CMS Password Grant Client',
                'redirect_uris' => [env('APP_URL', 'http://localhost')],
            ]);

        $passportPersonalAccessClient = Client::whereJsonContains('grant_types', 'personal_access')->first();

        if ($passportPersonalAccessClient) {
            $this->updateEnvValues([
                'PASSPORT_PERSONAL_ACCESS_CLIENT_ID' => $passportPersonalAccessClient->id,
                'PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET' => $passportPersonalAccessClient->secret,
            ]);
        }

        $passportPasswordGrantClient = Client::whereJsonContains('grant_types', 'password')->first();

        if ($passportPasswordGrantClient) {
            $this->updateEnvValues([
                'PASSPORT_PASSWORD_CLIENT_ID' => $passportPasswordGrantClient->id,
                'PASSPORT_PASSWORD_CLIENT_SECRET' => $passportPasswordGrantClient->secret,
            ]);
        }
    }
}
