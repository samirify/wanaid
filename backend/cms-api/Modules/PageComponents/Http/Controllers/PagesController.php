<?php

namespace Modules\PageComponents\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Client\Models\ClientModule;
use Modules\Client\Services\ClientModuleRecordService;
use Modules\PageComponents\Services\PageService;
use Modules\PageComponents\Models\Page;
use Modules\Core\Models\MediaStore;
use Modules\Core\Services\TranslationService;
use Modules\Core\Traits\MediaTrait;
use SoulDoit\DataTable\SSP;

class PagesController extends Controller
{
    use ValidatesRequests;
    use MediaTrait;
    use AppHelperTrait;
    use SAAApiResponse;

    private $pageSections;

    public function __construct(
        private readonly PageService $pageService,
        private readonly TranslationService $translationService,
        private readonly ClientModuleRecordService $clientModuleRecordService,
    ) {
        $this->pageSections = $this->pageService::PAGE_SECION_TYPES;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SSP $ssp, Request $request)
    {
        $ssp->enableSearch();
        $ssp->setColumns([
            ['label' => 'ID',         'db' => 'id'],
            ['label' => 'Code',       'db' => 'code'],
            ['label' => 'Name',       'db' => 'name'],
            ['label' => 'Created At', 'db' => 'created_at'],
            ['label' => 'Updated At', 'db' => 'updated_at'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('v_pages')
                ->select($selected_columns)
                ->where('is_template', '=', false);

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
        $availablePagesData = $this->pageService->getPageSections();

        return $this->successResponse([
            'page_code' => 'TMP_CODE',
            'page_id' => 'TMP_ID',
            'available_pages' => $availablePagesData['pages'],
            'header_sizes' => $this->pageService->getHeaderSizes(),
            'translations' => getCodesTranslations([
                'LANG_1_NAME',
                'LANG_2_NAME',
                'LANG_3_NAME',
                'TMPSET_main_header_top',
                'TMPSET_main_header_middle_big',
                'TMPSET_main_header_bottom',
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
        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'page_title' => 'max:255',
            'name'       => 'required|max:255',
            'code'       => 'required|unique:pages',
        ], [
            'name.required' => 'Page Internal Name is required',
            'name.max' => 'Page Internal Name must not be greater than 255 characters!',
            'code.required' => 'Page Url is required',
            'code.unique' => 'The Page Url already exists!',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        $pageCreated = $this->pageService->createPage($request);

        if ($pageCreated['success']) {
            $data = $pageCreated['data'];
            $data['id'] = $data['page']->id;

            unset($data['page']);
            return $this->successResponse($data);
        } else {
            return $this->errorResponse($pageCreated['error'], $pageCreated['code']);
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
        $page = Page::find($id);

        if (!$page) {
            return $this->errorResponse('Page not found!', 404);
        }

        $pageHeaders = $this->pageService->getHeaders([
            'p.code' => $page->code
        ]);

        $headerImg = MediaStore::where([
            'entity_name' => 'PageHeaderImage',
            'entity_id' => (string)$page->id
        ])->first();

        $_headers = isset($pageHeaders['headers'][$page->code]) ? $pageHeaders['headers'][$page->code] : [];

        $availablePagesData = $this->pageService->getPageSections();

        $page['header_image_id'] = $headerImg ? $headerImg->id : null;
        $page['ctas'] = $_headers['header_ctas'] ?? [];

        return $this->successResponse([
            'page' => $page,
            'page_headers_count' => count($_headers),
            'page_headers' => $_headers,
            'page_code' => $page->code,
            'page_id' => $page->id,
            'available_pages' => $availablePagesData['pages'],
            'header_sizes' => $this->pageService->getHeaderSizes(),
            'translations' => getCodesTranslations([
                'PAGE_' . $page->id . '_main_header_top',
                'PAGE_' . $page->id . '_main_header_middle_big',
                'PAGE_' . $page->id . '_main_header_bottom',
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
        $requestData = $request->all();
        $page = Page::find($id);

        $validator = Validator::make($requestData, [
            'page_title' => 'max:255',
            'name'       => 'required',
            'code'       => 'required|unique:pages,id,' . $page->id,
        ], [
            'name.required' => 'Page Internal Name is required',
            'name.max' => 'Page Internal Name must not be greater than 255 characters!',
            'code.required' => 'Page Url is required',
            'code.unique' => 'The Page Url already exists!',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        $pageUpdated = $this->pageService->updatePage($page, $request);

        if ($pageUpdated['success']) {
            $data = $pageUpdated['data'];
            $data['id'] = $data['page']->id;

            unset($data['page']);
            return $this->successResponse($data);
        } else {
            return $this->errorResponse($pageUpdated['error'], $pageUpdated['code']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $pageDeleted = $this->pageService->deletePage($id);

        if ($pageDeleted['success']) {
            return $this->successResponse($pageDeleted['data']);
        } else {
            return $this->errorResponse($pageDeleted['error'], $pageDeleted['code']);
        }
    }

    /**
     * Get About us data
     * @param string $code
     * @return Response
     */
    public function page($code, Request $request)
    {
        $requestedLang = getLanguageByLocale($request->get('locale', null));
        $pageData = $this->pageService->getPageData($code, $requestedLang ? $requestedLang->id : null);

        if ($pageData['success']) {

            $clientModule = ClientModule::where([
                'pages_id' => $pageData['page_id'],
            ])->first();

            if ($clientModule && !determineBool($clientModule->active)) {
                return $this->errorResponse('Page not found!', 404);
            }

            unset($pageData['success'], $pageData['page_id']);
            return $this->successResponse($pageData);
        } else {
            return $this->errorResponse('Page not found!', 404);
        }
    }

    public function recordPage(string $code, string $recordSlug, Request $request)
    {
        $requestedLang = getLanguageByLocale($request->get('locale', null));
        $pageData = $this->clientModuleRecordService->getRecordPageData($code . '-template');

        if ($pageData['success']) {
            $clientModule = ClientModule::where([
                'pages_id' => $pageData['page_id'],
            ])->first();

            if ($clientModule && !determineBool($clientModule->active)) {
                return $this->errorResponse('Page not found!', 404);
            }

            $result = $this->clientModuleRecordService->processTemplateRecord($pageData, $code, $recordSlug, $requestedLang ? $requestedLang->id : null);

            return $this->successResponse($result);
        } else {
            return $this->errorResponse('Page not found!', 404);
        }
    }
}
