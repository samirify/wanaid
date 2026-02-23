<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Illuminate\Routing\Controller;
use Modules\Core\Services\StatsService;
use Modules\Core\Services\SubscriptionsService;

class SubscriptionsController extends Controller
{
    use AppHelperTrait, SAAApiResponse;

    /**
     * constructor
     */
    public function __construct(
        private readonly SubscriptionsService $subscriptionsService,
        private readonly StatsService $statsService,
    ) {}

    public function getDashboardStatsByMonths(int $months)
    {
        $result = $this->subscriptionsService->getSubscriptionsStats(months: $months);

        return $this->successResponse([
            'statsData' => $result,
        ]);
    }
}
