<?php

namespace Modules\PageComponents\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\MediaStore;
use Modules\Core\Services\SettingsService;
use Modules\Core\Services\TranslationService;
use Modules\Core\Traits\MediaTrait;
use Modules\PageComponents\Services\PageService;
use Modules\PageComponents\Models\PageContent;
use Modules\PageComponents\Services\PillarsService;
use SoulDoit\DataTable\SSP;

class PillarsController extends Controller
{
    use ValidatesRequests;
    use MediaTrait;
    use AppHelperTrait;
    use SAAApiResponse;

    public function __construct(
        private readonly PageService $pageService,
        private readonly PillarsService $pillarsService,
        private readonly TranslationService $translationService,
        private readonly SettingsService $settingsService,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SSP $ssp, Request $request)
    {
        $ssp->enableSearch();
        $ssp->setColumns([
            ['label' => 'ID',           'db' => 'id'],
            ['label' => 'Code',         'db' => 'code'],
            ['label' => 'Name',         'db' => 'name'],
            ['label' => 'Page',         'db' => 'pages_name'],
            ['label' => 'Image ID',     'db' => 'media_store_id'],
            ['label' => 'Active',       'db' => 'active'],
            ['label' => 'Created At',   'db' => 'created_at'],
            ['label' => 'Updated At',   'db' => 'updated_at'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('v_pillars')
                ->select($selected_columns);

            $query = $this->filterDataTable($request, $query);

            return $query;
        });

        $result = $ssp->getData();

        return $this->successResponse([
            'records' => $result['items'],
            'totalRecords' => $result['total_item_count'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageSectionsData = $this->pageService->getPageSections();

        return $this->successResponse([
            'available_pages' => $pageSectionsData['pages'],
            'available_widgets' => $this->pageService->getAvailableWidgets(),
            'available_modules' => $this->settingsService->getClientModules(),
            'translations' => getCodesTranslations([
                'TMPSET_VALUE',
                'TMPSET_CTA_LABEL',
                'TMPSET_WIDGET_OPTIONAL_DATA_HEADER',
                'TMPSET_WIDGET_OPTIONAL_DATA_SUB_HEADER',
                'TMPSET_WIDGET_OPTIONAL_DATA_NO_RECORDS_MESSAGE',
                'TMPSET_WIDGET_OPTIONAL_DATA_MORE_LABEL',
            ])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_sections_id' => 'required',
            'name'             => 'required|max:255',
            'value'            => 'required',
            // 'pillar_image'     => 'max:' . config('client.images.max_upload_size') . '|mimes:' . config('client.images.allowed_mime_types'),
        ], [
            'page_sections_id.required' => 'Page is required',
            'name.required' => 'Section Internal Name is required',
            'name.max' => 'Section Internal Name must not be greater than 255 characters!',
            'value.required' => 'Content is required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {

            $pillar = new PageContent();

            $pillar->name = $request->name;
            $pillar->code = $this->formatCode(json_encode($request->all()));
            $pillar->page_sections_id = $request->page_sections_id;
            $pillar->value = $request->value;
            $pillar->cta_target = strtolower($request->cta_target);
            $pillar->cta_label = $request->cta_label;

            if ($pillar->cta_target == 'internal') {
                $pillar->cta_url = null;
                $pillar->cta_page_sections_id = $request->cta_page_sections_id;
                if (empty($pillar->cta_page_sections_id)) {
                    throw new Exception("Please select a target page");
                }
                if (empty($pillar->cta_label)) {
                    throw new Exception("Button label cannot be blank");
                }

                if ($request->page_sections_id == $request->cta_page_sections_id) {
                    throw new Exception("CTA Target page cannot be the same as page section's page!");
                }
            } else if ($pillar->cta_target == 'external') {
                $pillar->cta_url = $request->cta_url;
                $pillar->cta_page_sections_id = null;

                if (empty($pillar->cta_url)) {
                    throw new Exception("Please fill in the URL field");
                }
                if (empty($pillar->cta_label)) {
                    throw new Exception("Button label cannot be blank");
                }
            } else {
                $pillar->cta_target = null;
                $pillar->cta_url = null;
                $pillar->cta_page_sections_id = null;
            }

            $pillar->active = isset($request->active) && ($request->active === true || $request->active === 'true');

            $pillar->save();

            $this->pillarsService->updatePillerWidgetData($pillar->id, $request);

            $prefix = 'PAGE_SECTION_' . $pillar->id . '_';
            $pillar->update([
                'value' => str_replace('TMPSET_', $prefix, $pillar->value),
                'cta_label' => str_replace('TMPSET_', $prefix, $pillar->cta_label),
            ]);

            if ($request->has('pillar_image') && !is_null($request->pillar_image)) {
                $this->uploadFile($request, [
                    'field_name' => 'pillar_image',
                    'file_name' => $this->formatUniqueTitle($pillar->name || $pillar->code),
                ], [
                    'name' => 'PillarImage',
                    'id' => $pillar->id
                ], []);
            }

            $this->translationService->translateFields(['value', 'cta_label'], $request->all(), $prefix);

            DB::commit();
            return $this->successResponse([
                'msg' => 'Created successfully!'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pageSectionsData = $this->pageService->getPageSections();

        $pillar = PageContent::find($id);
        $pillarImg = MediaStore::where([
            'entity_name' => 'PillarImage',
            'entity_id' => (string)$pillar->id
        ])->first();

        $pillar->image_id = $pillarImg ? $pillarImg->id : null;
        $pillar->active = $pillar->active === 1;

        $pillar->widget_data = $this->pageService->getPageContentWidgetData($id);
        $pillar->use_data_type = count($pillar->widget_data) > 0;
        $pillar->cta_label = 'PAGE_SECTION_' . $id . '_CTA_LABEL';

        return $this->successResponse([
            'section' => $pillar,
            'available_pages' => $pageSectionsData['pages'],
            'available_widgets' => $this->pageService->getAvailableWidgets(),
            'available_modules' => $this->settingsService->getClientModules(),
            'translations' => getCodesTranslations([
                'PAGE_SECTION_' . $pillar->id . '_VALUE',
                'PAGE_SECTION_' . $pillar->id . '_CTA_LABEL',
                'PAGE_SECTION_' . $pillar->id . '_WIDGET_OPTIONAL_DATA_HEADER',
                'PAGE_SECTION_' . $pillar->id . '_WIDGET_OPTIONAL_DATA_SUB_HEADER',
                'PAGE_SECTION_' . $pillar->id . '_WIDGET_OPTIONAL_DATA_NO_RECORDS_MESSAGE',
                'PAGE_SECTION_' . $pillar->id . '_WIDGET_OPTIONAL_DATA_MORE_LABEL',
            ])
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate the data
        $pillar = PageContent::find($id);

        $validator = Validator::make($request->all(), [
            'page_sections_id' => 'required',
            'name'             => 'required|max:255',
            'value'            => 'required',
            // 'pillar_image'     => 'max:' . config('client.images.max_upload_size') . '|mimes:' . config('client.images.allowed_mime_types'),
        ], [
            'page_sections_id.required' => 'Page is required',
            'name.required' => 'Section Internal Name is required',
            'name.max' => 'Section Internal Name must not be greater than 255 characters!',
            'value.required' => 'Content is required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $pillar->name = $request->name;
            $pillar->code = $this->formatCode(json_encode($request->all()));
            $pillar->page_sections_id = $request->page_sections_id;
            $pillar->value = $request->value;
            $pillar->cta_target = strtolower($request->cta_target);
            $pillar->cta_label = $request->cta_label;

            if ($pillar->cta_target == 'internal') {
                $pillar->cta_url = null;
                $pillar->cta_page_sections_id = $request->cta_page_sections_id;

                if (empty($pillar->cta_page_sections_id)) {
                    throw new Exception("Please select a target page");
                }
                if (empty($pillar->cta_label)) {
                    throw new Exception("Button label cannot be blank");
                }

                if ($request->page_sections_id == $request->cta_page_sections_id) {
                    throw new Exception("CTA Target page cannot be the same as page section's page!");
                }
            } else if ($pillar->cta_target == 'external') {
                $pillar->cta_url = $request->cta_url;
                $pillar->cta_page_sections_id = null;

                if (empty($pillar->cta_url)) {
                    throw new Exception("Please fill in the URL field");
                }
                if (empty($pillar->cta_label)) {
                    throw new Exception("Button label cannot be blank");
                }
            } else {
                $pillar->cta_target = null;
                $pillar->cta_url = null;
                $pillar->cta_page_sections_id = null;
            }

            $pillar->active = isset($request->active) && ($request->active === true || $request->active === 'true');

            $pillar->save();

            $this->pillarsService->updatePillerWidgetData($pillar->id, $request);

            $removePillarImage = isset($request->remove_pillar_image) && ($request->remove_pillar_image === true || $request->remove_pillar_image === 'true');

            if ($removePillarImage) {
                MediaStore::where([
                    'entity_id' => (string)$pillar->id,
                    'entity_name' => 'PillarImage'
                ])->delete();
            } else {
                if ($request->has('pillar_image') && !is_null($request->pillar_image)) {
                    $this->uploadFile($request, [
                        'field_name' => 'pillar_image',
                        'file_name' => $this->formatUniqueTitle($pillar->name || $pillar->code),
                    ], [
                        'name' => 'PillarImage',
                        'id' => $pillar->id
                    ], []);
                }
            }

            $this->translationService->translateFields(['value', 'cta_label'], $request->all(), 'PAGE_SECTION_' . $pillar->id . '_');

            DB::commit();
            return $this->successResponse([
                'msg' => 'Updated successfully!',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pillar = PageContent::find($id);
            $pillarTitle = $pillar->name;
            $pillar->delete();

            // Clean translations
            LanguageCode::whereIn('code', [
                $pillar->value,
                $pillar->cta_label,
            ])->delete();

            MediaStore::where([
                'entity_id' => (string)$id,
                'entity_name' => 'PillarImage'
            ])->delete();

            DB::commit();
            return $this->successResponse([
                'msg' => 'Page section ' . $pillarTitle . ' was deleted successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function layout()
    {
        $pillarsData = $this->pillarsService->getPillars([
            'ps.code' => 'PILLARS',
            'pc.active' => true
        ], true, [
            'resize_width' => 100
        ]);

        return $this->successResponse([
            'sections' => $this->pillarsService->sortLayoutPillars($pillarsData['pillars'])
        ]);
    }

    public function updateLayout(Request $request)
    {
        try {
            $requestData = $request->all();

            $pillarsOrder = $requestData['sectionsOrder'] ?? [];

            if (!empty($pillarsOrder)) {
                foreach ($pillarsOrder as $sectionId => $sectionOrder) {
                    $pillar = PageContent::find($sectionId);
                    if ($pillar) {
                        $pillar->order = (int)$sectionOrder;
                        $pillar->save();
                    }
                }
            } else {
                return $this->successResponse([
                    'msg' => 'No changes detected! Nothing has been updated',
                ]);
            }

            return $this->successResponse([
                'msg' => 'Page layout has been updated successfully',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }
}
