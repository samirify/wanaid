<?php

namespace Modules\PageComponents\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\PageComponents\Services\PageService;
use Modules\PageComponents\Models\Page;
use Modules\PageComponents\Models\PageSection;
use Illuminate\Support\Str;
use Modules\Client\Services\ClientIdentityThemeService;
use Modules\Core\Models\MediaStore;
use Modules\Core\Services\TranslationService;
use Modules\Core\Traits\MediaTrait;
use Modules\PageComponents\Models\HeaderCta;
use Modules\PageComponents\Models\PageContent;
use Modules\PageComponents\Services\PageBuilderService;

class PageBuilderController extends Controller
{
    use ValidatesRequests;
    use MediaTrait;
    use AppHelperTrait;
    use SAAApiResponse;

    private $pageSections;

    public function __construct(
        private readonly PageService $pageService,
        private readonly PageBuilderService $pageBuilderService,
        private readonly TranslationService $translationService,
        private readonly ClientIdentityThemeService $clientIdentityThemeService,
    ) {
        $this->pageSections = ['HEADER', 'PILLARS'];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $clientIdentityTheme = $this->clientIdentityThemeService->getClientIdentityDefaultTheme();

        return $this->successResponse([
            'page_code' => 'TMP_CODE',
            'page_id' => 'TMP_ID',
            'available_pages' => Page::all(),
            'theme' => $clientIdentityTheme,
            'header_sizes' => $this->pageService->getHeaderSizes(),
            'header_size_codes' => DB::table('application_code AS ac')
                ->select('ac.id', 'ac.code')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PAGE_HEADER_SIZE',
                ])
                ->pluck('ac.code', 'ac.id'),
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

        DB::beginTransaction();

        try {
            $page = new Page();

            $page->page_title = $request->page_title;
            $page->name = $request->name;
            $page->code = strtolower(Str::slug($request->code));
            $page->header_size_id = $request->header_size_id;
            $page->meta_description = $request->meta_description;
            $page->meta_keywords = $request->meta_keywords;

            $page->save();

            $prefix = 'PAGE_' . $page->id . '_';

            foreach ($this->pageSections as $code) {
                if ($code === 'HEADER') {
                    continue;
                }
                $pageSection = new PageSection();
                $pageSection->pages_id = $page->id;
                $pageSection->code = $code;
                $pageSection->name = $page->name . ' ' . ucfirst(strtolower($code));
                $pageSection->save();

                if ($code == 'HEADER') {
                    $pageHeaders = config('client.site.page.headers');

                    $ctas = array_key_exists('ctas', $requestData) ? $requestData['ctas'] : [];

                    $this->pageService->updateCtas($page->id, $ctas, $request->deletedCtas);

                    foreach ($pageHeaders as $code) {
                        $header = PageContent::firstOrNew(array(
                            'code' => $code,
                            'page_sections_id' => $pageSection->id
                        ));
                        $value = $request->$code;
                        $header->value = str_replace('TMPSET_', $prefix, $value);
                        $header->active = $value ? true : false;
                        $header->save();
                    }

                    if ($request->has('header_image') && !is_null($request->header_image)) {
                        $this->uploadFile($request, [
                            'field_name' => 'header_image',
                            'file_name' => $this->formatUniqueTitle($page->name || $page->code),
                        ], [
                            'name' => 'PageHeaderImage',
                            'id' => $page->id
                        ], []);
                    }
                }
            }

            $this->translationService->translateFields([
                'main_header_top',
                'main_header_middle_big',
                'main_header_bottom'
            ], $request->all(), $prefix);

            DB::commit();

            return $this->successResponse([
                'msg' => 'Created successfully!',
                'page' => $page
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
    public function showNew($id)
    {
        $page = Page::find($id);

        if (!$page) {
            return $this->errorResponse('Page not found!', 404);
        }

        $clientIdentityTheme = $this->clientIdentityThemeService->getClientIdentityDefaultTheme();

        return $this->successResponse([
            'page' => $page,
            'available_pages' => Page::all(['id', 'name', 'code']),
            'theme' => $clientIdentityTheme,
            'header_sizes' => $this->pageService->getHeaderSizes(),
            'header_size_codes' => DB::table('application_code AS ac')
                ->select('ac.id', 'ac.code')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PAGE_HEADER_SIZE',
                ])
                ->pluck('ac.code', 'ac.id'),
            'grid_items' => $this->pageBuilderService->formatPageGrid($id),
        ]);
    }


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

        $page['header_image_id'] = $headerImg ? $headerImg->id : null;
        $page['ctas'] = $_headers['header_ctas'] ?? [];
        $page['main_header_img'] = $headerImg ? route('media.image.download', ['id' => $headerImg->id]) : false;

        $clientIdentityTheme = $this->clientIdentityThemeService->getClientIdentityDefaultTheme();

        return $this->successResponse([
            'page' => $page,
            'page_headers' => $_headers,
            'available_pages' => Page::all(['id', 'name', 'code']),
            'theme' => $clientIdentityTheme,
            'header_sizes' => $this->pageService->getHeaderSizes(),
            'header_size_codes' => DB::table('application_code AS ac')
                ->select('ac.id', 'ac.code')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PAGE_HEADER_SIZE',
                ])
                ->pluck('ac.code', 'ac.id'),
            'translations' => getCodesTranslations([
                'PAGE_' . $page->id . '_main_header_top',
                'PAGE_' . $page->id . '_main_header_middle_big',
                'PAGE_' . $page->id . '_main_header_bottom',
            ]),
            'grid_items' => $this->pageBuilderService->formatPageGrid($id),
            'pillars' => $this->pageBuilderService->getPagePillars($id),
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

        DB::beginTransaction();

        try {
            $page->page_title = $request->page_title;
            $page->name = $request->name;

            if (!$request->is_template) {
                $page->code = strtolower(Str::slug($request->code));
            }

            $page->header_size_id = $request->header_size_id;
            $page->meta_description = $request->meta_description;
            $page->meta_keywords = $request->meta_keywords;

            $page->save();

            foreach ($this->pageSections as $code) {
                if ($code == 'HEADER') {
                    DB::table('page_contents AS pc')
                        ->leftJoin('page_sections AS ps', 'ps.id', '=', 'pc.page_sections_id')
                        ->leftJoin('pages AS p', 'p.id', '=', 'ps.pages_id')
                        ->where([
                            'p.id' => $id,
                            'ps.code' => 'HEADER'
                        ])
                        ->delete();
                    $pageSection = PageSection::where([
                        'code' => $code,
                        'pages_id' => $id
                    ])->delete();
                    MediaStore::where([
                        'entity_name' => 'PageHeaderImage',
                        'entity_id' => (string)$id
                    ])->delete();
                    continue;
                }
                $pageSection = PageSection::firstOrNew([
                    'code' => $code,
                    'pages_id' => $id
                ]);
                $pageSection->name = $page->name . ' ' . ucfirst(strtolower($code));
                $pageSection->save();

                if ($code == 'HEADER') {
                    $pageHeaders = config('client.site.page.headers');

                    $ctas = array_key_exists('ctas', $requestData) ? $requestData['ctas'] : [];

                    $this->pageService->updateCtas($page->id, $ctas, $request->deletedCtas);

                    foreach ($pageHeaders as $code) {
                        $header = PageContent::firstOrNew(array(
                            'code' => $code,
                            'page_sections_id' => $pageSection->id
                        ));
                        $value = $request->$code;
                        $header->value = $value;
                        $header->active = $value ? true : false;
                        $header->save();
                    }

                    $removeHeaderImage = isset($request->remove_header_image) && ($request->remove_header_image === true || $request->remove_header_image === 'true');

                    if ($removeHeaderImage) {
                        MediaStore::where([
                            'entity_id' => (string)$page->id,
                            'entity_name' => 'PageHeaderImage'
                        ])->delete();
                    } else {
                        if ($request->has('header_image') && !is_null($request->header_image)) {
                            $this->uploadFile($request, [
                                'field_name' => 'header_image',
                                'file_name' => $this->formatUniqueTitle($page->name || $page->code),
                            ], [
                                'name' => 'PageHeaderImage',
                                'id' => $page->id
                            ], []);
                        }
                    }
                }
            }

            $this->translationService->translateFields([
                'main_header_top',
                'main_header_middle_big',
                'main_header_bottom'
            ], $request->all());

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
    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {
            $page = Page::find($id);

            $pageTitle = $page->name;

            // TODO: Handle CTAs
            HeaderCta::where([
                'pages_id' => $id
            ])->delete();


            $relatedPageContents = DB::table('page_contents AS pc')
                ->select('pc.id AS pageContentId')
                ->leftJoin('page_sections AS ps', 'ps.id', '=', 'pc.page_sections_id')
                ->leftJoin('pages AS p', 'p.id', '=', 'ps.pages_id')
                ->where('p.id', '=', $id)
                ->get();

            foreach ($relatedPageContents as $relatedPageContent) {
                MediaStore::where([
                    'entity_id' => (string)$relatedPageContent->pageContentId,
                    'entity_name' => 'PillarImage'
                ])->delete();

                $pillar = PageContent::find($relatedPageContent->pageContentId);
                $pillar->delete();
            }

            PageSection::where([
                'pages_id' => $id
            ])->delete();

            $page->delete();

            DB::commit();
            return $this->successResponse([
                'msg' => 'Page ' . $pageTitle . ' was deleted successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }
}
