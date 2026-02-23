<?php

namespace Modules\Core\Services;

use Modules\Core\Models\Currency;

class CurrencyService
{
    public function getCurrencies(): array
    {
        $availableCurrencies = $this->getAvailableCurrencies();

        return [
            'available' => $availableCurrencies,
            'default' => array_values(array_filter($availableCurrencies, function ($item) {
                return $item['default'] === 1;
            }))[0]
        ];
    }

    private function getAvailableCurrencies()
    {
        return Currency::select(
            'id',
            'code',
            'name',
            'default'
        )->where([
            'active' => 1
        ])
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }
}
