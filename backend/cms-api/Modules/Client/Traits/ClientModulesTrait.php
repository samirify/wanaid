<?php

namespace Modules\Client\Traits;

use Illuminate\Http\Request;
use Modules\Client\Http\Controllers\ClientModuleRecordController;
use Modules\Client\Models\ClientModule;
use Modules\Core\Services\Constants;

trait ClientModulesTrait
{
    public function buildWidgetModuleUrl(
        int $moduleId,
        string $dataType,
        array $clientModuleFilters = [],
        bool $loadData = false,
        string $requestedLang = null,
    ): array {
        $module = ClientModule::find($moduleId);

        $result = [];

        switch ($dataType) {
            case Constants::AC_PAGE_SECTION_LIST_WIDGET:
            case Constants::AC_PAGE_SECTION_SEARCH_WIDGET:
                $result['url'] = route('public_client_modules.list', ['moduleCode' => $module->code]);
                if ($loadData) {
                    $requestParams = [
                        'client_module_filters' => $clientModuleFilters,
                        'include_media' => 'Y'
                    ];

                    if ($requestedLang) {
                        $requestParams['langId'] = $requestedLang;
                    }

                    $newRequest = new Request();
                    $newRequest->merge($requestParams);
                    $data = app(ClientModuleRecordController::class)->index($module->code, $newRequest);
                    $result['data'] = $data->original['data']['records'] ?? [];
                }
        }

        return $result;
    }
}
