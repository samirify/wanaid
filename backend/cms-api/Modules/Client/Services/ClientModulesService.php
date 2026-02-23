<?php

namespace Modules\Client\Services;

use App\Traits\AppHelperTrait;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Client\Models\ClientModule;
use Modules\Client\Models\ClientModuleCategory;
use Modules\Core\Models\MediaStore;
use Modules\Core\Services\Constants;
use Modules\PageComponents\Services\PageService;

class ClientModulesService
{
    use AppHelperTrait;

    const RECORD_CODE_PREFIX = 'CMR';

    const MANDATORY_COLUMNS = [
        'code' => [
            'name' => 'code',
            'type' => 'string',
            'unique' => true,
            'required' => true,
        ],
        'name' => [
            'name' => 'name',
            'type' => 'string',
            'required' => true,
        ],
        'slug' => [
            'name' => 'slug',
            'type' => 'string',
            'unique' => true,
            'required' => true,
        ],
        'title' => [
            'name' => 'title',
            'type' => 'string',
            'required' => true,
        ],
        'slogan' => [
            'name' => 'slogan',
            'type' => 'string',
        ],
        'short_description' => [
            'name' => 'short_description',
            'type' => 'mediumText',
            'required' => true,
        ],
        'full_description' => [
            'name' => 'full_description',
            'type' => 'longText',
        ],
        'published_at' => [
            'name' => 'published_at',
            'type' => 'dateTime',
        ],
        'active' => [
            'name' => 'active',
            'type' => 'string',
        ],
        'options' => [
            'name' => 'options',
            'type' => 'longText',
        ],
    ];

    public function __construct(
        private readonly PageService $pageService,
    ) {}

    public function getCategoriesColumnsByCategoryId(int $id): array
    {
        return DB::table('client_module_categories AS cmc')
            ->leftJoin('client_module_category_custom_columns AS cmccc', 'cmc.id', '=', 'cmccc.client_module_categories_id')
            ->select(
                'cmccc.name AS name',
                'cmccc.type AS type',
                'cmccc.foreign_table AS foreign_table',
                'cmccc.foreign_column AS foreign_column',
                'cmccc.required AS required',
                'cmccc.unique AS unique',
                'cmccc.options AS options',
            )
            ->where(['cmc.id' => $id])
            ->get()
            ->toArray();
    }

    public function getCustomColumnsForRecordByModule(ClientModule $module): array
    {
        $categoriesColumns = $this->getCategoriesColumnsByCategoryId($module->category_id);

        $customColumns = [];

        foreach ($categoriesColumns as $column) {
            $columnOptions = json_decode($column->options ?? '', true);

            $_col = [
                'label' => $columnOptions['label'] ?? $this->snakeToReadable($column->name),
                'name' => $column->name,
                'type' => $column->type === 'foreign' ? 'select' : $column->type,
                'required' => $column->required === 1 || $column->required === true,
            ];

            if ($column->type === 'foreign') {
                $q = DB::table($column->foreign_table)
                    ->select(["{$columnOptions['key_column']} AS id", "{$columnOptions['value_column']} AS name"]);

                if ($columnOptions['filters']) {
                    $q->where($columnOptions['filters']);
                }

                $_col['select_data'] = $q->get()->toArray();
            }

            array_push($customColumns, $_col);
        }

        return $customColumns;
    }

    /**
     * Create new module database table 
     * @param ClientModule clientModule
     */
    public function createModuleTable(ClientModule $clientModule)
    {
        try {
            $categoriesColumns = $this->getCategoriesColumnsByCategoryId($clientModule->category_id);

            $tableName = $this->formatModuleTableName($clientModule->code);

            $this->removeModuleTableAndView($clientModule);

            Schema::create($tableName, function (Blueprint $table) use ($categoriesColumns) {

                $table->id();

                $table->string('code')->unique();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('title');
                $table->string('slogan')->nullable();
                $table->mediumText('short_description')->nullable();
                $table->longText('full_description')->nullable();

                foreach ($categoriesColumns as $column) {
                    $columnArray = json_decode(json_encode($column, true), true);
                    $type = $columnArray['type'];
                    $name = $columnArray['name'];

                    if ($type === 'foreign') {
                        $table->unsignedBigInteger($name)->nullable();
                        $table->foreign($name)->references($columnArray['foreign_column'])->on($columnArray['foreign_table']);
                    } else {
                        $colLen = $columnArray['length'] ?? false;
                        $length = $colLen ?: Builder::$defaultStringLength;

                        if ($columnArray['required']) {
                            $table->addColumn($type, $name, compact('length'));
                        } else {
                            $table->addColumn($type, $name, compact('length'))->nullable();
                        }
                    }
                }

                $table->dateTime('published_at')->nullable();
                $table->boolean('active')->default(0);
                $table->longText('options')->nullable();

                $table->timestamps();
                createUserStampFields($table);
            });

            $viewName = $this->formatModuleViewName($clientModule->code);
            DB::statement("CREATE OR REPLACE VIEW {$viewName} AS SELECT * FROM {$tableName} AS c");
        } catch (Exception $ex) {
            $clientModule->delete();
            throw $ex;
        }
    }

    /**
     * Remove module database table 
     * @param ClientModule clientModule
     */
    public function removeModuleTableAndView(ClientModule $clientModule)
    {
        try {
            $tableName = $this->formatModuleTableName($clientModule->code);
            $viewName = $this->formatModuleViewName($clientModule->code);

            DB::statement("DROP VIEW IF EXISTS {$viewName}");
            if (Schema::hasTable($tableName)) Schema::drop($tableName);
        } catch (Exception $ex) {
            $clientModule->delete();
            throw $ex;
        }
    }

    public function getClientRecordMedia(string $entityName, string $entityId, array $options = []): array
    {
        $tempToken = $options['temp_token'] ?? null;

        $query = MediaStore::where([
            'entity_name' => $entityName,
            'entity_id' => (string)$entityId,
        ])->whereNull('temp_token');

        if (!empty($tempToken)) {
            $query->orWhere('temp_token', '=', $tempToken);
        }

        if (isset($options['to_delete'])) {
            $query->where('to_delete', '=', $options['to_delete']);
        }

        return $this->formatMediaFiles($query->get());
    }

    public function getUserClientRecordMedia(string $entityName, string $entityId, string $tempToken): array
    {
        $user = auth('api')->user();

        $query = MediaStore::where([
            'entity_name' => $entityName,
            'entity_id' => (string)$entityId,
            'to_delete' => false,
            'updated_by' => $user->id
        ])
            ->where(function ($query) use ($tempToken) {
                $query->whereNull('temp_token')
                    ->orWhere('temp_token', '=', $tempToken);
            });

        return $this->formatMediaFiles($query->get());
    }

    private function formatMediaFiles(Collection $mediaFiles): array
    {
        $media = [];

        $thumbnailExtensions = config('client.images.thumbnail_extensions');

        foreach ($mediaFiles as $file) {
            $_file = [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'file_extension' => $file->file_extension,
                'url' => route('media.image.download', ['id' => $file->id]),
            ];

            if (in_array($file->file_extension, $thumbnailExtensions)) {
                $_file['preview_url'] = route('media.image.download', ['id' => $file->id, 'resize_width' => 48]);
            }

            array_push($media, $_file);
        }

        return $media;
    }

    public function updateCreatedClientRecordMedia(string $entityName, string $entityId, string $tempToken): void
    {
        MediaStore::where([
            'entity_name' => $entityName,
            'temp_token' => $tempToken,
        ])->update([
            'entity_id' => (string)$entityId,
            'temp_token' => null,
        ]);
    }

    public function updateClientRecordMedia(string $entityName, string $entityId, string $tempToken): void
    {
        MediaStore::where([
            'entity_name' => $entityName,
            'entity_id' => (string)$entityId,
            'to_delete' => true,
        ])->delete();

        $mediaFiles = MediaStore::where([
            'entity_name' => $entityName,
            'entity_id' => (string)$entityId,
            'temp_token' => $tempToken,
        ])->get();

        foreach ($mediaFiles as $file) {
            if (!empty($file->temp_token)) {
                MediaStore::where([
                    'entity_name' => $entityName,
                    'entity_id' => (string)$entityId,
                    'file_name' => $file->file_name,
                ])
                    ->whereNull('temp_token')
                    ->where('id', '!=', $file->id)
                    ->delete();

                $file->temp_token = null;
                $file->save();
            }
        }
    }

    public function cleanUserMediaFiles(string $entityName, string $entityId): void
    {
        MediaStore::where([
            'entity_name' => $entityName,
            'entity_id' => (string)$entityId,
            'temp_token' => null,
        ])->update([
            'to_delete' => false,
        ]);

        $user = auth('api')->user();

        if ($user) {
            MediaStore::where([
                'updated_by' => $user->id,
            ])
                ->whereNotNull('temp_token')
                ->delete();
        }
    }

    public function handleModuleMainPage(
        ClientModule $clientModule,
        Request $request,
        bool $includeDelete = false
    ): void {
        $mainPageEnabled = determineBool($request->has_main_page);

        if ($mainPageEnabled) {
            if (!$clientModule->pages_id) {
                $page = $this->pageService->createPage($request);

                if (!$page['success']) {
                    throw new Exception($page['error'], $page['code']);
                }

                $clientModule->pages_id = $page['data']['page']->id;
                $clientModule->save();
            }
        } else {
            if ($clientModule->pages_id && $includeDelete) {

                $mainPageId = $clientModule->pages_id;
                $clientModule->pages_id = null;
                $clientModule->save();

                $pageDeleted = $this->pageService->deletePage($mainPageId);

                if (!$pageDeleted['success']) {
                    throw new Exception($pageDeleted['error'], $pageDeleted['code']);
                }
            }
        }
    }

    public function handleModuleRecordsTemplatePage(
        ClientModule $clientModule,
        Request $request,
        bool $includeDelete = false
    ): void {
        $recordsTemplatePageEnabled = determineBool($request->has_records_template_page);

        if ($recordsTemplatePageEnabled) {
            if (!$clientModule->records_template_page_id) {
                $pageCreated = $this->pageService->createPage($request, true);

                if (!$pageCreated['success']) {
                    throw new Exception($pageCreated['error'], $pageCreated['code']);
                }

                $clientModule->records_template_page_id = $pageCreated['data']['page']->id;
                $clientModule->save();

                $pageCreated['data']['page']->is_template = true;
                $pageCreated['data']['page']->save();
            }
        } else {
            if ($clientModule->records_template_page_id && $includeDelete) {

                $mainPageId = $clientModule->records_template_page_id;
                $clientModule->records_template_page_id = null;
                $clientModule->save();

                $pageDeleted = $this->pageService->deletePage($mainPageId);

                if (!$pageDeleted['success']) {
                    throw new Exception($pageDeleted['error'], $pageDeleted['code']);
                }
            }
        }
    }

    public function getAvailableModuleCategories(): array
    {
        return ClientModuleCategory::all(['id', 'name'])->toArray();
    }
}
