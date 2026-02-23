<?php

namespace Modules\PageComponents\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Services\Constants;
use Modules\Core\Services\TranslationService;
use Modules\PageComponents\Models\PageWidget;
use Modules\PageComponents\Models\PageWidgetData;

class PillarsService
{
    public function __construct(
        private readonly TranslationService $translationService,
    ) {}

    /**
     * Fetches page contents 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getPillars($where = [], $for_layout = false, array $options = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'pillars' => []
        ];

        try {
            if ($for_layout) {
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

            if ($for_layout) {
                $query->orderBy('pc.order', 'asc');
            } else {
                $query->orderBy('ps.name', 'asc');
            }

            $query->where(array_merge(['ps.code' => 'PILLARS'], $where));

            $pillarsList = $query->get()->toArray();

            foreach ($pillarsList as $pillar) {
                array_push($result['pillars'], [
                    'id' => $pillar->id,
                    'name' => $pillar->name,
                    'code' => $pillar->code,
                    'value' => $pillar->value,
                    'order' => $pillar->order,
                    'active' => $pillar->active ? 'Y' : 'N',
                    'page_sections_id' => $pillar->page_sections_id,
                    'page_sections_code' => $pillar->page_sections_code,
                    'page_sections_name' => $pillar->page_sections_name,
                    'pages_id' => $pillar->pages_id,
                    'pages_code' => $pillar->pages_code,
                    'pages_name' => $pillar->pages_name,
                    'img_url' => $pillar->media_store_id ? route('media.image.download', ['id' => $pillar->media_store_id, 'resize_width' => $options['resize_width'] ?? 300]) : '',
                    'created_at' => $pillar->created_at,
                    'updated_at' => $pillar->updated_at,
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

    /**
     * Fetches one pillar 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getOnePillar($where = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'pillar' => []
        ];

        try {
            $query = DB::table('page_contents AS pc')
                ->leftJoin('page_sections AS ps', 'ps.id', '=', 'pc.page_sections_id')
                ->leftJoin('pages AS p', 'p.id', '=', 'ps.pages_id')
                ->select(
                    'pc.id AS id',
                    'pc.name AS name',
                    'pc.code AS code',
                    'pc.value AS value',
                    'pc.order AS order',
                    'pc.active AS active',
                    'ps.id AS page_sections_id',
                    'ps.code AS page_sections_code',
                    'ps.name AS page_sections_name',
                    'p.id AS pages_id',
                    'p.code AS pages_code',
                    'p.name AS pages_name',
                    'pc.created_at AS created_at'
                );

            if (count($where) > 0) {
                $query->where($where);
            }

            $result['pillar'] = $query->first();
            $result['pillar']->img_url = $result['pillar']->media_store_id ? route('media.image.download', ['id' => $result['pillar']->media_store_id]) : null;
            unset($result['pillar']->img_content);
            $result['success'] = true;
            $result['message'] = 'Record fetched successfully!';
        } catch (Exception $ex) {
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
            ];
        }

        return $result;
    }

    public function sortLayoutPillars($pillars_data)
    {
        $pillars = [];

        foreach ($pillars_data as $pillar) {
            if ($pillar['id']) {
                $pillars[$pillar['pages_code']]['pages_name'] = $pillar['pages_name'];
                $pillars[$pillar['pages_code']]['pillars'][] = array_merge($pillar, [
                    'value' => getLanguageTranslation('PAGE_SECTION_' . $pillar['id'] . '_value')
                ]);
            }
        }

        return $pillars;
    }

    public function updatePillerWidgetData(int $pillarId, Request $request): void
    {
        $useDataType = filter_var($request->get('use_data_type', false), FILTER_VALIDATE_BOOLEAN);

        if (!$useDataType) {
            PageWidgetData::where(['page_contents_id' => $pillarId])->delete();
        } else {

            if (!$request->widget_data) {
                throw new Exception("Missing widget data!", 400);
            }

            $widgetData = $request->widget_data;

            $widget = PageWidget::where(['code' => strtoupper($widgetData['data_type']) ?? ''])->first();

            if (!$widget) {
                throw new Exception("Widget not set!", 400);
            }

            if (!isset($widgetData['data_type'])) {
                throw new Exception("Data Type is required!", 400);
            }

            $loadData = filter_var($widgetData['load_data'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $widgetData['load_data'] = $loadData;

            if ($loadData) {
                $widgetData['widget_more_target'] = $widgetData['widget_more_target'] ?? 'internal';
                $widgetData['client_module_filters']['max_num_of_records'] = $widgetData['client_module_filters']['max_num_of_records'] ?? 1;

                if ($widgetData['widget_more_target'] === 'external') {
                    unset($widgetData['widget_more_page_sections_id']);
                }
            } else {
                unset(
                    $widgetData['client_module_filters'],
                    $widgetData['widget_more_target'],
                    $widgetData['widget_more_page_sections_id'],
                    $widgetData['widget_more_url'],
                );
            }

            $widgetData = $this->processTranslatedWidgetData($widgetData, $pillarId, $request);

            switch ($widgetData['data_type']) {
                case Constants::AC_PAGE_SECTION_LIST_WIDGET:
                case Constants::AC_PAGE_SECTION_SEARCH_WIDGET:
                    if (!isset($widgetData['module_id'])) {
                        throw new Exception("Module is required!", 400);
                    }
                    break;
            }

            // $widgetData['widget_more_target'] = $request->get('widget_more_target', null);
            // $widgetData['widget_more_page_sections_id'] = $request->get('widget_more_page_sections_id', null);
            // $widgetData['widget_more_url'] = $request->get('widget_more_url', null);

            PageWidgetData::updateOrCreate([
                'page_contents_id' => $pillarId,
            ], [
                'page_widgets_id' => $widget->id,
                'module_id' => $widgetData['module_id'],
                'data' => json_encode($widgetData)
            ]);
        }
    }

    private function processTranslatedWidgetData(array $widgetData, int $pillarId, Request $request): array
    {
        $defaultLang = getDefaultLanguage();
        $defaultLangId = $defaultLang->id;

        // Optional widget data
        $widgetHeader = $request->get('widget_header_' . $defaultLangId, '');
        $widgetSubHeader = $request->get('widget_sub_header_' . $defaultLangId, '');
        $widgetNoRecordsMessage = $request->get('widget_no_records_message_' . $defaultLangId, '');
        $widgetMoreLabel = $request->get('widget_more_label_' . $defaultLangId, '');

        $translationPrefix = 'PAGE_SECTION_' . $pillarId . '_WIDGET_OPTIONAL_DATA_';

        if (empty($widgetHeader)) {
            LanguageCode::where('code', $translationPrefix . 'HEADER')->delete();
            $widgetData['widget_header'] = $widgetHeader;
        } else {
            $widgetData['widget_header'] = $translationPrefix . 'HEADER';
            $this->translationService->translateFields(['widget_header'], array_merge(['widget_header' => $translationPrefix . 'HEADER'], $request->all()), $translationPrefix);
        }

        if (empty($widgetSubHeader)) {
            LanguageCode::where('code', $translationPrefix . 'SUB_HEADER')->delete();
            $widgetData['widget_sub_header'] = $widgetSubHeader;
        } else {
            $widgetData['widget_sub_header'] = $translationPrefix . 'SUB_HEADER';
            $this->translationService->translateFields(['widget_sub_header'], array_merge(['widget_sub_header' => $translationPrefix . 'SUB_HEADER'], $request->all()), $translationPrefix);
        }

        if (empty($widgetNoRecordsMessage)) {
            LanguageCode::where('code', $translationPrefix . 'NO_RECORDS_MESSAGE')->delete();
            $widgetData['widget_no_records_message'] = $widgetNoRecordsMessage;
        } else {
            $widgetData['widget_no_records_message'] = $translationPrefix . 'NO_RECORDS_MESSAGE';
            $this->translationService->translateFields(['widget_no_records_message'], array_merge(['widget_no_records_message' => $translationPrefix . 'NO_RECORDS_MESSAGE'], $request->all()), $translationPrefix);
        }

        if (empty($widgetMoreLabel)) {
            LanguageCode::where('code', $translationPrefix . 'MORE_LABEL')->delete();
            $widgetData['widget_more_label'] = $widgetMoreLabel;
        } else {
            $widgetData['widget_more_label'] = $translationPrefix . 'MORE_LABEL';
            $this->translationService->translateFields(['widget_more_label'], array_merge(['widget_more_label' => $translationPrefix . 'MORE_LABEL'], $request->all()), $translationPrefix);
        }

        return $widgetData;
    }
}
