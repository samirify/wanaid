<?php

namespace Modules\Client\Services;

use App\Traits\AppHelperTrait;
use Exception;
use Modules\Client\Exception\RecordNotFoundException;
use Modules\Client\Models\ClientModule;
use Modules\Client\Services\Firebase\FirebaseUtil;
use Modules\PageComponents\Models\Page;

class DashboardService
{
    use AppHelperTrait;

    public function __construct(
        private readonly FirebaseUtil $firebaseUtil,
    ) {}

    public function getDashboardData(): array
    {
        return [
            'modules' => $this->getModulesStats(),
            'pages' => $this->getPagesStats(),
            'plan' => [
                'plans' => [
                    'free' => [
                        'active' => true,
                        'label' => 'Free'
                    ],
                    'standard' => [
                        'active' => false,
                        'label' => 'Standard'
                    ],
                    'premium' => [
                        'active' => false,
                        'label' => 'Premium'
                    ],
                ]
            ]
        ];
    }

    private function getModulesStats(): array
    {
        return [
            'maxAllowedModules' => 1,
            'count' => ClientModule::count(),
        ];
    }

    private function getPagesStats(): array
    {
        return [
            'maxAllowedPages' => 10,
            'count' => Page::where(['is_template' => false])->count(),
        ];
    }

    public function getFirebaseUser(string $id): array
    {
        try {
            /** @var ?FirebaseUser $user */
            $user = $this->firebaseUtil->getUser([
                'localId' => $id,
            ]);

            $project = $this->firebaseUtil->getCustomerProjectBySubDomain($id, 'c1');

            return [
                'success' => true,
                'user' => $user->toArray(),
                'project' => $project,
            ];
        } catch (Exception | RecordNotFoundException $e) {
            return [
                'success' => false,
                'code' => $e->getCode(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
