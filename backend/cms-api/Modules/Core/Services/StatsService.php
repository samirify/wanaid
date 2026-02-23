<?php

namespace Modules\Core\Services;

use Modules\Core\Models\Stats;

class StatsService
{
    public function __construct(
        private readonly ProcessService $processService
    ) {}

    public function getStatsByCodes(array $codes = []): array
    {
        $stats = [];

        $statsRecords = Stats::whereIn('code', $codes)->get()->toArray();

        foreach ($statsRecords as $stat) {
            $stats[$stat['code']]['last_updated'] = $stat['updated_at'];
            $stats[$stat['code']]['data'] = json_decode($stat['value'] ?? '{}', true);
        }

        return $stats;
    }

    public function updateStats(array $data = []): void
    {
        Stats::upsert($data, uniqueBy: ['code'], update: ['value']);
    }

    public function deleteStatsByCodes(array $codes = []): void
    {
        Stats::whereIn('code', $codes)->delete();
    }

    public function updateStatsCategory(string $category): void
    {
        $this->processService->runBackgroundProcess('stats:update', [
            'category' => $category
        ]);
    }
}
