<?php

namespace Modules\PageComponents\Services;

use Exception;
use Illuminate\Support\Facades\DB;

class PageBuilderService
{
    public function __construct(
        private readonly PageService $pageService,
    ) {
    }

    /**
     * Fetches page contents 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function formatPageGrid(int $pageId): array
    {
        $headerData = $this->getPageHeader($pageId);
        $pillarsData = $this->getPagePillars($pageId);

        return array_merge(
            $this->formatPageHeaderForGrid($headerData),
            $this->formatPagePillarsForGrid(array_values($pillarsData))
        );
    }

    private function getPageHeader(int $pageId): array
    {
        $query = DB::table('pages AS p')
            ->leftJoin('page_sections AS ps', 'p.id', '=', 'ps.pages_id')
            ->leftJoin('page_contents AS pc', 'ps.id', '=', 'pc.page_sections_id')
            ->leftJoin('media_store AS ms', function ($join) {
                $join->on('ms.entity_id', '=', 'p.id');
                $join->on('ms.entity_name', '=', DB::raw("'PageHeaderImage'"));
            })
            ->select(
                'pc.id AS page_content_id',
                'pc.code AS page_content_code',
                'pc.name AS page_content_name',
                'pc.value AS page_content_value',
                'pc.order AS page_content_order',
                'pc.active AS page_content_active',
                'ps.id AS page_sections_id',
                'ps.code AS page_sections_code',
                'ps.name AS page_sections_name',
                'p.id AS pages_id',
                'p.code AS pages_code',
                'p.name AS pages_name',
                'pc.created_at AS page_content_created_at',
                'pc.updated_at AS page_content_updated_at',
                'ms.id AS media_store_id'
            )
            ->where([
                'p.id' => $pageId,
                'ps.code' => 'HEADER'
            ])
            ->orderBy('pc.order', 'asc');

        return $query->get()->toArray();
    }

    private function formatPageHeaderForGrid(array $headerData = []): array
    {
        $header = [];

        if (!empty($headerData)) {
            $headerImgId = $headerData[0]->media_store_id;

            $tmpHeader = [
                'id' => md5(json_encode($headerData)),
                'type' => [
                    'code' => 'header',
                    'height' => 4,
                    'label' => 'Page Header',
                ],
                'pagePart' => [
                    'image_url' => $headerImgId ? route('media.image.download', ['id' => $headerImgId]) : '',
                ],
                'w' => 12,
                'isDroppable' => false,
                'isDraggable' => false,
                'isResizable' => false,
                'isBounded' => false,
            ];

            $header[] = $tmpHeader;
        }

        return $header;
    }

    private function formatPagePillarsForGrid(array $pillarsData): array
    {
        $pillars = [];

        foreach ($pillarsData as $pillar) {
            $dataType = 'FREE_TEXT';
            $widgetData = [];

            if ($pillar->page_content_id) {
                $widgetData = $this->pageService->getPageContentWidgetData($pillar->page_content_id, true);
                $dataType = $widgetData['data_type'] ?? 'FREE_TEXT';

                unset($widgetData['data_type']);
            }

            $pillar->data_type = $dataType;
            $pillar->widget_data = $widgetData;
            $pillar->translations = getCodesTranslations([
                'PAGE_SECTION_' . $pillar->page_content_id . '_VALUE',
            ]);

            $pillar->image_url = $pillar->media_store_id ? route('media.image.download', ['id' => $pillar->media_store_id]) : '';

            $pillars[] = [
                'id' => md5(json_encode($pillar)),
                'type' => [
                    'code' => 'pageSection',
                    'height' => 3,
                    'label' => 'Page Section',
                ],
                'pagePart' => $pillar,
                'w' => 12
            ];
        }

        return $pillars;
    }

    public function getPagePillars(int $pageId): array
    {
        $query = DB::table('pages AS p')
            ->leftJoin('page_sections AS ps', 'p.id', '=', 'ps.pages_id')
            ->leftJoin('page_contents AS pc', 'ps.id', '=', 'pc.page_sections_id')
            ->leftJoin('media_store AS ms', function ($join) {
                $join->on('ms.entity_id', '=', 'pc.id');
                $join->on('ms.entity_name', '=', DB::raw("'PillarImage'"));
            })
            ->select(
                'pc.id AS page_content_id',
                'pc.code AS page_content_code',
                'pc.name AS page_content_name',
                'pc.value AS page_content_value',
                'pc.order AS page_content_order',
                'pc.active AS page_content_active',
                'ps.id AS page_sections_id',
                'ps.code AS page_sections_code',
                'ps.name AS page_sections_name',
                'p.id AS pages_id',
                'p.code AS pages_code',
                'p.name AS pages_name',
                'pc.created_at AS page_content_created_at',
                'pc.updated_at AS page_content_updated_at',
                'ms.id AS media_store_id'
            )
            ->where([
                'p.id' => $pageId,
                'ps.code' => 'PILLARS'
            ])
            ->whereNotNull(['pc.id'])
            ->orderBy('pc.order', 'asc');

        $pillars = [];
        $res = $query->get()->toArray();
        $availableLanguages = getAvailableLanguages();

        foreach ($res as $pillar) {
            $value = $pillar->page_content_value;
            unset($pillar->page_content_value);
            foreach ($availableLanguages as $lang) {
                // $valKey = "pillars[{$pillar->page_content_id}][page_content_value]_{$lang->id}";
                // $pillar->{$valKey} = getLanguageTranslation($pillar->page_content_value, $lang->id);
                $pillar->page_content_value['_' . $lang->id] = getLanguageTranslation($value, $lang->id);
            }

            $pillars[$pillar->page_content_id] = $pillar;
        }

        return $pillars;
    }

    public function getPillars($where = [], $byPageContent = false, array $options = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'pillars' => []
        ];

        try {
            if ($byPageContent) {
                $query = DB::table('pages AS p')
                    ->leftJoin('page_sections AS ps', 'p.id', '=', 'ps.pages_id')
                    ->leftJoin('page_contents AS pc', 'ps.id', '=', 'pc.page_sections_id');
            } else {
                $query = DB::table('page_contents AS pc')
                    ->leftJoin('page_sections AS ps', 'ps.id', '=', 'pc.page_sections_id')
                    ->leftJoin('pages AS p', 'p.id', '=', 'ps.pages_id');
            }

            $query->leftJoin('media_store AS ms', function ($join) {
                $join->on('ms.entity_id', '=', 'pc.id');
                $join->on('ms.entity_name', '=', DB::raw("'PillarImage'"));
            })
                ->select(
                    'pc.id AS id',
                    'pc.code AS code',
                    'pc.name AS name',
                    'pc.value AS value',
                    'pc.order AS order',
                    'pc.active AS active',
                    'ps.id AS page_sections_id',
                    'ps.code AS page_sections_code',
                    'ps.name AS page_sections_name',
                    'p.id AS pages_id',
                    'p.code AS pages_code',
                    'p.name AS pages_name',
                    'pc.created_at AS created_at',
                    'pc.updated_at AS updated_at',
                    'ms.id AS media_store_id',
                    'ms.mime_type AS mime_type',
                    'ms.content AS img_content'
                );

            if ($byPageContent) {
                $query->orderBy('pc.order', 'asc');
            } else {
                $query->orderBy('ps.name', 'asc');
            }

            if (count($where) > 0) {
                $query->where($where);
            }

            $pillarsList = $query->get()->toArray();

            foreach ($pillarsList as $pillar) {
                $dataType = 'FREE_TEXT';
                $widgetData = [];

                if ($pillar->id) {
                    $widgetData = $this->pageService->getPageContentWidgetData($pillar->id, true);
                    $dataType = $widgetData['data_type'] ?? 'FREE_TEXT';

                    unset($widgetData['data_type']);
                }


                array_push($result['pillars'], [
                    'id' => $pillar->id,
                    'name' => $pillar->name,
                    'code' => $pillar->code,
                    'value' => $pillar->value,
                    'order' => $pillar->order,
                    'active' => $pillar->active ? true : false,
                    'page_sections_id' => $pillar->page_sections_id,
                    'page_sections_code' => $pillar->page_sections_code,
                    'page_sections_name' => $pillar->page_sections_name,
                    'pages_id' => $pillar->pages_id,
                    'pages_code' => $pillar->pages_code,
                    'pages_name' => $pillar->pages_name,
                    'img_url' => $pillar->media_store_id ? route('media.image.download', ['id' => $pillar->media_store_id, 'resize_width' => $options['resize_width'] ?? 300]) : '',
                    'created_at' => $pillar->created_at,
                    'updated_at' => $pillar->updated_at,
                    'data_type' => $dataType,
                    'widget_data' => $widgetData,
                ]);
            }

            $result['success'] = true;
            $result['message'] = 'Results fetched successfully!';
        } catch (Exception $ex) {
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

        return $result;
    }
}
