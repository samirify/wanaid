<?php

namespace Modules\Client\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\ClientModule;
use Modules\Client\Services\ClientModuleRecordService;
use Modules\Client\Services\ClientModulesService;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\MediaStore;
use Modules\Core\Services\CurrencyService;
use Modules\Core\Services\SettingsService;
use Modules\Core\Services\TranslationService;
use Modules\Core\Traits\MediaTrait;
use Modules\PageComponents\Services\PillarsService;
use SoulDoit\DataTable\SSP;

class ClientModuleRecordController extends Controller
{
    use ValidatesRequests;
    use MediaTrait;
    use AppHelperTrait;
    use SAAApiResponse;

    public function __construct(
        private readonly ClientModulesService $clientModulesService,
        private readonly ClientModuleRecordService $clientModuleRecordService,
        private readonly PillarsService $pillarsService,
        private readonly TranslationService $translationService,
        private readonly SettingsService $settingsService,
        private readonly CurrencyService $currencyService,
        private readonly SSP $ssp,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(string $moduleCode, Request $request)
    {
        $module = ClientModule::where(['code' => $moduleCode])->first();

        if (!$module) {
            return $this->errorResponse('Module does not exist or has been removed!', 404);
        }

        $requestedLaguage = DB::table('language AS l')
            ->select('l.id AS id')
            ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
            ->where('lo.locale', $request->get('locale'))
            ->orWhere('l.id', $request->get('langId', getDefaultLanguage()->id))
            ->first();

        if (!$requestedLaguage) {
            return $this->errorResponse('Invalid language trandslation requested!', 500);
        }

        $langId = $requestedLaguage->id;

        $viewName = $this->formatModuleViewName($moduleCode);

        $cols = [
            ['label' => 'ID',               'db' => 'id'],
            ['label' => 'Code',             'db' => 'code'],
            ['label' => 'Name',             'db' => 'name'],
            ['label' => 'Url',              'db' => 'slug'],
            ['label' => 'Title',            'db' => 'title'],
            ['label' => 'Short Description', 'db' => 'short_description'],
            ['label' => 'Active',           'db' => 'active'],
            ['label' => 'Created At',       'db' => 'created_at'],
            ['label' => 'Updated At',       'db' => 'updated_at'],
        ];

        $customColumns = $this->clientModulesService->getCustomColumnsForRecordByModule($module);

        foreach ($customColumns as $column) {
            $columnOptions = json_decode($column->options ?? '', true);
            array_push($cols, [
                'label' => $columnOptions['label'] ?? $this->snakeToReadable($column['name']),
                'db' => $column['name']
            ]);
        }

        $this->ssp->enableSearch();
        $this->ssp->setColumns($cols);

        $this->ssp->setQuery(function ($selected_columns) use ($request, $viewName, $langId) {
            $query = DB::table($viewName . ' AS v')
                ->select($selected_columns);

            if (!empty($langId)) {
                $query
                    ->selectRaw('name, (
                        SELECT lt.text FROM language_translation lt
                                LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                                LEFT JOIN language l ON l.id = lt.language_id
                                WHERE l.id = ?
                                AND lc.code = v.name
                        ) AS name', [$langId])
                    ->selectRaw('title, (
                        SELECT lt.text FROM language_translation lt
                                LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                                LEFT JOIN language l ON l.id = lt.language_id
                                WHERE l.id = ?
                                AND lc.code = v.title
                        ) AS title', [$langId])
                    ->selectRaw('short_description, (
                        SELECT lt.text FROM language_translation lt
                                LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                                LEFT JOIN language l ON l.id = lt.language_id
                                WHERE l.id = ?
                                AND lc.code = v.short_description
                        ) AS short_description', [$langId]);
            }

            $query = $this->filterDataTable($request, $query);


            $clientModuleFilters = $request->get('client_module_filters', []);

            if (!empty($clientModuleFilters)) {
                $activeFilter = $clientModuleFilters['active'] ?? false;

                $activeFilter = filter_var($clientModuleFilters['active'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($activeFilter) {
                    $query
                        ->where("active", '=', $activeFilter);
                }

                $query->take($clientModuleFilters['max_num_of_records']);
            } else {
                $globalFilters = $request->get('filters', []);
                $globalFilterMatchMode = $globalFilters['global']['matchMode'] ?? '';
                $globalFilterValue = $globalFilters['global']['value'] ?? '';

                if (!empty($globalFilterMatchMode)) {
                    switch (strtolower($globalFilterMatchMode)) {
                        case 'contains':
                            $query
                                ->where("code", 'LIKE', "%" . $globalFilterValue . "%")
                                ->orWhere("name", 'LIKE', "%" . $globalFilterValue . "%")
                                ->orWhere("title", 'LIKE', "%" . $globalFilterValue . "%");
                            break;
                    }
                }
            }

            return $query;
        });

        $result = $this->ssp->getData();

        $includeMedia = strtoupper($request->include_media);

        if ($includeMedia === 'Y') {
            $tableName = $this->formatModuleTableName($moduleCode);

            foreach ($result['items'] as $key => $item) {
                $result['items'][$key]['media'] = $this->clientModulesService->getClientRecordMedia($tableName, $item['id']);
            }
        }

        return $this->successResponse([
            'mainPageId' => $module->pages_id,
            'recordsTemplatePageId' => $module->records_template_page_id,
            'records' => $result['items'],
            'totalRecords' => $result['total_item_count'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(string $moduleCode)
    {
        $module = ClientModule::where(['code' => $moduleCode])->first();

        return $this->successResponse([
            'custom_columns' => $this->clientModulesService->getCustomColumnsForRecordByModule($module),
            'translations' => []
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(string $moduleCode, Request $request)
    {
        return $this->clientModuleRecordService->insertModuleRecord($moduleCode, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $moduleCode, int $id)
    {
        return $this->successResponse($this->clientModuleRecordService->getModuleRecord($moduleCode, ['id' => $id]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(string $moduleCode, int $id, Request $request)
    {
        return $this->clientModuleRecordService->updateModuleRecord($moduleCode, $id, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $moduleCode, int $id)
    {
        DB::beginTransaction();
        try {
            $tableName = $this->formatModuleTableName($moduleCode);

            $recordQuery = DB::table($tableName)->where(['id' => $id]);

            $record = $recordQuery->first();

            if (!$record) {
                return $this->errorResponse('Record was not found!', 404);
            }

            // Clean translations
            LanguageCode::whereIn('code', [
                $record->name,
                $record->title,
                $record->slogan,
                $record->short_description,
                $record->full_description,
            ])->delete();

            $recordTitle = getLanguageTranslation($record->name);
            $recordQuery->delete();

            MediaStore::where([
                'entity_id' => (string)$id,
                'entity_name' => $tableName
            ])->delete();

            DB::commit();
            return $this->successResponse([
                'msg' => 'Record ' . $recordTitle . ' was deleted successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Upload files to media_store
     * @param string $moduleCode
     * @param string $moduleId
     * @param Request $request
     * 
     * @return Response
     */
    public function uploadClientModuleMedia(string $moduleCode, Request $request, string $moduleId = null)
    {
        DB::beginTransaction();

        $entityId = $moduleId ?? $request->get('temp_token', '.');

        try {
            $entityName = $this->formatModuleTableName($moduleCode);

            $this->doUploadClientModuleMedia($request, [
                'name' => $this->formatModuleTableName($moduleCode),
                'id' => $entityId
            ], [
                'temp_token' => $request->get('temp_token', null)
            ]);

            DB::commit();

            return $this->successResponse([
                'msg' => 'Uploaded successfully!',
                'media_files' => $this->clientModulesService->getClientRecordMedia($entityName, $entityId, [
                    'temp_token' => $request->get('temp_token', null)
                ])
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    public function deleteClientModuleMedia(int $fileId, string $moduleCode, Request $request, string $moduleId = null)
    {
        DB::beginTransaction();

        try {
            $tableName = $this->formatModuleTableName($moduleCode);

            $file = MediaStore::find($fileId);

            if (!$file) {
                return $this->errorResponse('File was not found!', 404);
            }

            if (!empty($moduleId)) {
                $message = $file->file_name . " was set to be deleted! Don't forget to update the record.";
                $file->update([
                    'to_delete' => 1,
                ]);
            } else {
                $moduleId = $request->get('temp_token', '.');
                $message = $file->file_name . " was deleted!";
                $file->delete();
            }

            DB::commit();

            return $this->successResponse([
                'msg' => $message,
                'media_files' => $this->clientModulesService->getUserClientRecordMedia($tableName, $moduleId, $request->get('temp_token', null))
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }
}
