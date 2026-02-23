<?php

namespace Modules\Client\Services;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Client\Models\ClientModule;
use Modules\Client\Models\ClientModuleRecord;
use Modules\Core\Models\MediaStore;
use Modules\Core\Services\CurrencyService;
use Modules\Core\Services\SettingsService;
use Modules\Core\Services\TranslationService;
use Modules\PageComponents\Models\Page;
use Modules\PageComponents\Services\PageService;
use Modules\PageComponents\Services\PillarsService;

class ClientModuleRecordService
{
    use AppHelperTrait, SAAApiResponse;

    public const PAGE_PREFIX = 'CLIENT_MODULE_';

    public function __construct(
        private readonly ClientModulesService $clientModulesService,
        private readonly SettingsService $settingsService,
        private readonly PageService $pageService,
        private readonly PillarsService $pillarsService,
        private readonly CurrencyService $currencyService,
        private readonly TranslationService $translationService,
    ) {}

    public function getModuleRecord(string $moduleCode, array $where, $isRecordPage = false): array
    {
        $tableName = $this->formatModuleTableName($moduleCode);

        $columns = [
            'id',
            'code',
            'name',
            'slug',
            'title',
            'slogan',
            'short_description',
            'full_description',
            'created_at',
            'published_at',
            'active'
        ];

        $module = ClientModule::where(['code' => $moduleCode])->first();

        $customColumns = $this->clientModulesService->getCustomColumnsForRecordByModule($module);

        foreach ($customColumns as $_col) {
            array_push($columns, $_col['name']);
        }

        $record = DB::table($tableName)
            ->select($columns)
            ->where($where)->first();

        if (!$record) {
            throw new Exception('Record was not found!', 404);
        }

        $record->active = $record->active === 1;

        $prefix = self::PAGE_PREFIX . $module->id . '_' . $record->id . '_';

        $this->clientModulesService->cleanUserMediaFiles($tableName, $record->id);

        $result = [
            'record' => $record,
            'media' => $this->clientModulesService->getClientRecordMedia($tableName, $record->id),
        ];

        if (!$isRecordPage) {
            $result['custom_columns'] = $customColumns;
            $result['translations'] = getCodesTranslations([
                $prefix . 'NAME',
                $prefix . 'TITLE',
                $prefix . 'SLOGAN',
                $prefix . 'SHORT_DESCRIPTION',
                $prefix . 'FULL_DESCRIPTION',
            ]);
        }

        return $result;
    }

    public function processTemplateRecord(array $pageData, string $moduleCode, string $recordSlug, ?string $requestedLang): array
    {
        $recordData = $this->getModuleRecord($moduleCode, ['slug' => $recordSlug], true);

        $recordMedia = MediaStore::select('id')->where([
            'entity_id' => (string)$recordData['record']->id,
            'entity_name' => 'cl_' . strtolower($moduleCode),
        ])->get()->toArray();

        unset($pageData['success'], $pageData['page_id']);

        // TODO: Logic is all good so far. Continue template processing!
        $translatedRecordName = getLanguageTranslation($recordData['record']->name, $requestedLang);
        $translatedRecordTitle = getLanguageTranslation($recordData['record']->title, $requestedLang);
        $recordSlogan = getLanguageTranslation($recordData['record']->slogan, $requestedLang);

        $module = ClientModule::where(['code' => $moduleCode])->first();
        $fieldCodePrefix = self::PAGE_PREFIX . $module->id . '_' . $recordData['record']->id . '_';

        $pageData['meta']['title'] = getLanguageTranslation($recordData['record']->title, $requestedLang);
        $pageData['meta']['description'] = $translatedRecordName . ',' . $translatedRecordTitle;
        $pageData['meta']['keywords'] = extractMetaKeywordsFromLangCode([
            $fieldCodePrefix . 'NAME',
            $fieldCodePrefix . 'TITLE',
            $fieldCodePrefix . 'SLOGAN',
            $fieldCodePrefix . 'SHORT_DESCRIPTION',
            $fieldCodePrefix . 'FULL_DESCRIPTION',
        ]);

        $pageData['headers']['main_header_middle_big'] = getLanguageTranslation($recordData['record']->name, $requestedLang);
        $pageData['headers']['slogan'] = $recordSlogan;

        $headerImg = $pageData['main_header_img'];
        if (count($recordMedia) > 0) {
            $headerImg = route('media.image.download', ['id' => $recordMedia[0]['id']]);
        }

        $pageData['main_header_img'] = $headerImg;
        // $pageData['record'] = $recordData['record'];

        return $pageData;
    }

    public function getRecordPageData(string $code): array
    {
        try {
            $page = Page::where(['code' => strtolower($code)])->first();

            if (!$page) {
                throw new Exception('Page template not set!');
            }

            $headerImage = false;
            if (isset($page->id)) {
                $headerImage = MediaStore::where([
                    'entity_name' => 'PageHeaderImage',
                    'entity_id' => (string)$page->id
                ])->first();
            }

            $mainHeaderImg = $headerImage ? route('media.image.download', ['id' => $headerImage->id]) : false;

            $pageHeaders = $this->pageService->getHeaders([
                'p.code' => $page->code
            ]);

            if (!$pageHeaders['success']) {
                throw new Exception($pageHeaders['error']['message'] ?? 'Error retrieving page headers!');
            }

            $pageContents = $this->pageService->getPageContents([
                [DB::raw('lower(p.code)'), '=', $page->code],
                'pc.active' => '1'
            ]);

            if (!$pageContents['success']) {
                throw new Exception($pageContents['error']['message'] ?? 'Error retrieving page contents!');
            }

            $contents = $pageContents['page_contents'][$code] ?? [];

            return [
                'success' => true,
                'page_id' => $page->id,
                'main_header_img' => $mainHeaderImg,
                'meta' => [
                    'title' => $page->page_title,
                    'description' => $page->meta_description,
                    'keywords' => $page->meta_keywords,
                ],
                'headers' => [
                    'size_code' => $pageHeaders['headers'][$page->code]['size_code'],
                    'size_name' => $pageHeaders['headers'][$page->code]['size_name'],
                    // 'main_header_top' => $pageHeaders['headers'][$page->code]['main_header_top'],
                    'main_header_middle_big' => $pageHeaders['headers'][$page->code]['main_header_middle_big'],
                    // 'main_header_bottom' => $pageHeaders['headers'][$page->code]['main_header_bottom'],
                    'ctas' => $pageHeaders['headers'][$page->code]['header_ctas'],
                ],
                'pillars' => array_key_exists('PILLARS', $contents) ? $contents['PILLARS'] : [],
            ];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function insertModuleRecord(string $moduleCode, Request $request): Response|JsonResponse
    {
        $tableName = $this->formatModuleTableName($moduleCode);
        $englishLang = getLanguageByLocale('en');

        $slug = $this->formatUniqueTitle($request->get('title_' . $englishLang->id));

        $validator = Validator::make(array_merge($request->all(), ['slug' => $slug]), [
            'name'               => 'required|max:255',
            'title'              => 'required|max:255',
            'slogan'             => 'max:255',
            'short_description'  => 'required|max:500',
            'slug'               => "unique:{$tableName},slug,NULL,slug",
        ], [
            'name.required' => 'Name is required',
            'name.max' => 'Name must not be greater than 255 characters!',
            'title.required' => 'Title is required',
            'title.max' => 'Title must not be greater than 255 characters!',
            'slogan.max' => 'Slogan must not be greater than 255 characters!',
            'short_description.required' => 'Short description is required',
            'short_description.max' => 'Short description must not be greater than 500 characters!',
            'slug.unique' => 'Title "' . $request->get('title_' . $englishLang->id) . '" has already been assigned to another record.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $fillables = [
                'code' => md5(json_encode([$moduleCode, $request->all(), time()])),
                'name' => $request->name,
                'slug' => $slug,
                'title' => $request->title,
                'slogan' => $request->slogan,
                'short_description' => $request->short_description,
                'full_description' => $request->full_description,
                'active' => isset($request->active) && ($request->active === true || $request->active === 'true'),
                'options' => json_encode($request->options ?? [])
            ];

            $module = ClientModule::where(['code' => $moduleCode])->first();

            $categoriesColumns = $this->clientModulesService->getCategoriesColumnsByCategoryId($module->category_id);

            foreach ($categoriesColumns as $column) {
                $fillables[$column->name] = $request->get($column->name);
            }

            $record = (new ClientModuleRecord())
                ->setTable($tableName)
                ->setDynamicFillable()
                ->fill($fillables);

            $record->save();

            $this->clientModulesService->updateCreatedClientRecordMedia($tableName, $record->id, $request->get('temp_token', null));

            $prefix = self::PAGE_PREFIX . $module->id . '_' . $record->id . '_';

            $record->update([
                'code' => $this->generateRecordCodeFromIdAndPrefix($record->id, $this->clientModulesService::RECORD_CODE_PREFIX),
                'name' => str_replace('TMPSET_', $prefix, $record->name),
                'title' => str_replace('TMPSET_', $prefix, $record->title),
                'slogan' => str_replace('TMPSET_', $prefix, $record->slogan),
                'short_description' => str_replace('TMPSET_', $prefix, $record->short_description),
                'full_description' => str_replace('TMPSET_', $prefix, $record->full_description),
            ]);

            $this->translationService->translateFields([
                'name',
                'title',
                'slogan',
                'short_description',
                'full_description',
            ], $request->all(), $prefix);

            DB::commit();
            return $this->successResponse([
                'msg' => 'Created successfully!'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    public function updateModuleRecord(string $moduleCode, int $id, Request $request): Response|JsonResponse
    {
        $tableName = $this->formatModuleTableName($moduleCode);

        $recordQuery = DB::table($tableName)->where(['id' => $id]);

        $record = $recordQuery->first();

        if (!$record) {
            return $this->errorResponse('Record was not found!', 404);
        }

        $englishLang = getLanguageByLocale('en');
        $slug = $this->formatUniqueTitle($request->get('title_' . $englishLang->id));

        Validator::extend('slug_check', function () use ($tableName, $slug, $id) {
            $rec = DB::table($tableName)->where([
                ['slug', '=', $slug],
                ['id', '<>', $id],
            ])->exists();

            return $rec ? false : true;
        });

        $validator = Validator::make($request->all(), [
            'name'               => 'required|max:255',
            'title'              => 'required|max:255',
            'slogan'             => 'max:255',
            'short_description'  => 'required|max:500',
            'slug'               => 'slug_check'
        ], [
            'name.required' => 'Name is required',
            'name.max' => 'Name must not be greater than 255 characters!',
            'title.required' => 'Title is required',
            'title.max' => 'Title must not be greater than 255 characters!',
            'slogan.max' => 'Slogan must not be greater than 255 characters!',
            'short_description.required' => 'Short description is required',
            'short_description.max' => 'Short description must not be greater than 500 characters!',
            'slug.slug_check' => 'Title "' . $request->get('title_' . $englishLang->id) . '" has already been assigned to another record. Choose a different one.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $fillables = [
                'name' => $request->name,
                'slug' => $slug,
                'title' => $request->title,
                'slogan' => $request->slogan,
                'short_description' => $request->short_description,
                'full_description' => $request->full_description,
                'active' => isset($request->active) && ($request->active === true || $request->active === 'true'),
                'options' => json_encode($request->options ?? [])
            ];

            $module = ClientModule::where(['code' => $moduleCode])->first();

            $categoriesColumns = $this->clientModulesService->getCategoriesColumnsByCategoryId($module->category_id);

            foreach ($categoriesColumns as $column) {
                $fillables[$column->name] = $request->get($column->name);
            }

            $recordQuery->update($fillables);

            $this->clientModulesService->updateClientRecordMedia($tableName, $id, $request->get('temp_token', null));

            $prefix = self::PAGE_PREFIX . $module->id . '_' . $record->id . '_';

            $this->translationService->translateFields([
                'name',
                'title',
                'slogan',
                'short_description',
                'full_description',
            ], $request->all(), $prefix);

            DB::commit();
            return $this->successResponse([
                'msg' => 'Updated successfully!'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }
}
