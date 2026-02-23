<?php

namespace Modules\Client\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Client\Services\DashboardService;

class DashboardController extends Controller
{
    use AppHelperTrait, SAAApiResponse;

    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function dashboard()
    {
        $result = $this->dashboardService->getDashboardData();

        return $this->successResponse([
            'dashboard' => $result,
        ]);
    }

    public function getFirebaseUser(Request $request)
    {
        $result = $this->dashboardService->getFirebaseUser('vWQrP9ANlKPlNOw7rqGVBsGvIuI2');

        if ($result['success']) {
            return $this->successResponse([
                'user' => $result['user'],
                'project' => $result['project'],
            ]);
        } else {
            return $this->errorResponse($result['error'], $result['code']);
        }
    }
}
