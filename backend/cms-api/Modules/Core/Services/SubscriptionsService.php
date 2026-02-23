<?php

namespace Modules\Core\Services;

class SubscriptionsService
{
    private const STATS_CODE = 'subscriptions';
    private const STATS_MAX_PERIOD_MONTHS = 12;

    public function __construct(
        private readonly StatsService $statsService
    ) {}

    public function updateSubscriptionsStats(): void
    {
        // $periodMonths = (empty($months) ? self::STATS_MAX_PERIOD_MONTHS : $months) - 1;

        $subscriptionsCounts = [];

        $this->statsService->updateStats([
            [
                'code' => self::STATS_CODE,
                'value' => json_encode($subscriptionsCounts)
            ],
        ]);
    }

    public function getSubscriptionsStats(int $months, bool $formatted = true): array
    {
        $stats = $this->statsService->getStatsByCodes(['subscriptions']);

        return $formatted ? $this->formatSubscriptionsStats($stats, $months) : $stats;
    }

    public function formatSubscriptionsStats(array $data, int $months): array
    {
        $formattedData = [];

        $periodMonths = $this->getPeriodMonths($months);

        $formattedData['last_updated'] = $data[self::STATS_CODE]['last_updated'] ?? null;

        $statsData = $data[self::STATS_CODE]['data'] ?? [];

        $pData = [
            'label' => 'Subscriptions'
        ];

        foreach ($statsData as $periodData) {
            $formattedData['labels'][] = $periodData['name'];
            $totalMonthsData = 0;
            foreach ($periodMonths as $month) {
                $totalMonthsData = $totalMonthsData + ($periodData['months'][$month] ?? 0);
            }
            $pData['data'][] = $totalMonthsData;
        }

        $formattedData['datasets'][] = $pData;

        return $formattedData;
    }

    private function getPeriodMonths(int $numOfMonths): array
    {
        $months = [];

        $maxNumOfMonths = $numOfMonths - 1;

        for ($i = $maxNumOfMonths; $i >= 0; $i--) {
            $months[] = substr(date('F', strtotime("-$i month")), 0, 3);
        }

        return $months;
    }
}
