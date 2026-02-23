<?php

namespace Modules\Core\Services;

use App\Traits\AppHelperTrait;
use Illuminate\Http\Request;

class AdministrationService
{
    use AppHelperTrait;

    public function updatePrivateAPIsProps(Request $request): void
    {
        $requestParams = $request->all();

        $allowedHosts = array_values($requestParams);

        $this->updateEnvValues([
            'CLIENT_API_PRIVATE_ALLOWED_HOSTS' => implode(',', array_filter($allowedHosts)),
        ]);
    }

    public function getClientAllowedHosts(): array
    {
        $allowedHosts = explode(',', config('client.api.private.allowed_hosts', ''));

        $clientAllowedHosts = [];

        foreach ($allowedHosts as $host) {
            array_push($clientAllowedHosts, [
                'id' => md5($host),
                'value' => $host
            ]);
        }

        return $clientAllowedHosts;
    }
}
