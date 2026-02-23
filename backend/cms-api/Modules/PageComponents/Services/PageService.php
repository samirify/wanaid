<?php

namespace Modules\PageComponents\Services;

use App\Traits\AppHelperTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\MediaStore;
use Modules\Core\Services\Constants;
use Modules\Core\Services\TranslationService;
use Modules\Core\Traits\MediaTrait;
use Modules\PageComponents\Models\HeaderCta;
use Modules\PageComponents\Models\Page;
use Modules\PageComponents\Models\PageWidget;
use Modules\PageComponents\Models\PageWidgetData;
use Modules\Team\Services\TeamService;
use Illuminate\Support\Str;
use Modules\Client\Models\ClientModule;
use Modules\Client\Traits\ClientModulesTrait;
use Modules\PageComponents\Models\PageContent;
use Modules\PageComponents\Models\PageSection;

class PageService
{
    use AppHelperTrait, MediaTrait, ClientModulesTrait;

    public const PAGE_SECION_TYPES = ['HEADER', 'PILLARS'];

    public function __construct(
        private readonly TranslationService $translationService,
        private readonly TeamService $teamService,
    ) {}

    public function createPage(Request $request, bool $isTemplate = false): array
    {
        $result = [
            'success' => false
        ];

        DB::beginTransaction();

        try {
            $requestData = $request->all();

            $page = new Page();

            $page->page_title = $request->page_title;
            $page->name = $request->name . ($isTemplate ? ' records template' : '');
            $page->code = strtolower(Str::slug($request->code)) . ($isTemplate ? '-template' : '');
            $page->header_size_id = $request->header_size_id;
            $page->meta_description = $request->meta_description;
            $page->meta_keywords = $request->meta_keywords;

            $page->save();

            $prefix = 'PAGE_' . $page->id . '_';

            foreach (self::PAGE_SECION_TYPES as $code) {
                $pageSection = new PageSection();
                $pageSection->pages_id = $page->id;
                $pageSection->code = $code;
                $pageSection->name = $page->name . ' ' . ucfirst(strtolower($code));
                $pageSection->save();

                if ($code === 'HEADER') {
                    $pageHeaders = config('client.site.page.headers');

                    $ctas = array_key_exists('ctas', $requestData) ? $requestData['ctas'] : [];

                    $this->updateCtas($page->id, $ctas, $request->deletedCtas);

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

            $result = [
                'success' => true,
                'data' => [
                    'msg' => 'Created successfully!',
                    'page' => $page
                ]
            ];

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['code'] = $th->getCode();
            $result['error'] = $th->getMessage();
        }

        return $result;
    }

    public function updatePage(Page $page, Request $request): array
    {
        $result = [
            'success' => false
        ];

        DB::beginTransaction();

        try {
            $requestData = $request->all();

            $page->page_title = $request->page_title;
            $page->name = $request->name;

            if (!$request->is_template) {
                $page->code = strtolower(Str::slug($request->code));
            }

            $page->header_size_id = $request->header_size_id;
            $page->meta_description = $request->meta_description;
            $page->meta_keywords = $request->meta_keywords;

            $page->save();

            foreach (self::PAGE_SECION_TYPES as $code) {
                $pageSection = PageSection::firstOrNew([
                    'code' => $code,
                    'pages_id' => $page->id
                ]);
                $pageSection->name = $page->name . ' ' . ucfirst(strtolower($code));
                $pageSection->save();

                if ($code === 'HEADER') {
                    $pageHeaders = config('client.site.page.headers');

                    $ctas = array_key_exists('ctas', $requestData) ? $requestData['ctas'] : [];

                    $this->updateCtas($page->id, $ctas, $request->deletedCtas);

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

            $result = [
                'success' => true,
                'data' => [
                    'msg' => 'Updated successfully!',
                    'page' => $page
                ]
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['code'] = $th->getCode();
            $result['error'] = $th->getMessage();
        }

        return $result;
    }

    public function deletePage(int $id): array
    {
        $result = [
            'success' => false
        ];

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
            $result = [
                'success' => true,
                'data' => [
                    'msg' => 'Page ' . $pageTitle . ' was deleted successfully'
                ]
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            $result = [
                'code' => $th->getCode(),
                'error' => $th->getMessage()
            ];
        }

        return $result;
    }

    /**
     * Fetches page section 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getPageSection($where = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'page_section' => []
        ];

        try {
            $query = DB::table('page_sections AS ps')
                ->leftJoin('pages AS p', 'p.id', '=', 'ps.pages_id')
                ->select(
                    'ps.id AS id',
                    'ps.code AS code',
                    'ps.name AS name',
                    'p.code AS page_code',
                    'p.name AS page_name'
                );

            if (count($where) > 0) {
                $query->where($where);
            }

            $result['page_section'] = $query->first();

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
     * Fetches page contents 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getPageContents(array $where = [], string $requestedLang = null)
    {
        $result = [
            'success' => false,
            'message' => '',
            'page_contents' => []
        ];

        try {
            $query = DB::table('page_contents AS pc')
                ->leftJoin('page_sections AS ps', 'ps.id', '=', 'pc.page_sections_id')
                ->leftJoin('pages AS p', 'p.id', '=', 'ps.pages_id')
                ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.header_size_id')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->leftJoin('media_store AS ms', function ($join) {
                    $join->on('ms.entity_id', '=', 'pc.id');
                    $join->on('ms.entity_name', '=', DB::raw("'PillarImage'"));
                })
                ->orderBy('pc.order', 'asc')
                ->select(
                    'pc.id AS id',
                    'pc.code AS code',
                    'pc.value AS value',
                    'pc.cta_target AS cta_target',
                    'pc.cta_label AS cta_label',
                    'pc.cta_page_sections_id AS cta_page_sections_id',
                    'pc.cta_url AS cta_url',
                    DB::raw('(
                        SELECT p1.code FROM page_sections AS ps1 
                            LEFT JOIN pages AS p1 ON p1.id = ps1.pages_id
                            WHERE ps1.id = pc.cta_page_sections_id
                    ) AS cta_page_code'),
                    'pc.active AS active',
                    'ps.id AS page_sections_id',
                    'ps.code AS page_sections_code',
                    'ps.name AS page_sections_name',
                    'p.id AS pages_id',
                    'p.code AS pages_code',
                    'p.name AS pages_name',
                    'p.page_title AS pages_page_title',
                    'ac.code AS header_size_code',
                    'ac.name AS header_size_name',
                    'p.meta_description AS pages_meta_description',
                    'p.meta_keywords AS pages_meta_keywords',
                    'ms.id AS media_store_id',
                    'ms.mime_type AS mime_type',
                    'ms.content AS img_content'
                );

            if (count($where) > 0) {
                $query->where($where);
            }

            $pageContentsList = $query->get()->toArray();

            foreach ($pageContentsList as $pageContent) {
                $result['page_contents']['id'] = $pageContent->pages_id;
                $result['page_contents'][$pageContent->pages_code]['META'] = [
                    'title' => $pageContent->pages_page_title,
                    'description' => $pageContent->pages_meta_description,
                    'keywords' => $pageContent->pages_meta_keywords,
                ];
                $result['page_contents'][$pageContent->pages_code]['HEADER']['size_code'] = $pageContent->header_size_code;
                $result['page_contents'][$pageContent->pages_code]['HEADER']['size_name'] = $pageContent->header_size_name;
                $result['page_contents'][$pageContent->pages_code]['HEADER']['ctas'] = array_values($this->buildHeaderCtas([
                    'hc.pages_id' => $pageContent->pages_id,
                    'hc.active' => true
                ], $requestedLang));

                switch ($pageContent->page_sections_code) {
                    case 'PILLARS':
                        if ($pageContent->active) {
                            $widgetData = $this->getPageContentWidgetData($pageContent->id, true, $requestedLang);
                            $dataType = $widgetData['data_type'] ?? 'FREE_TEXT';

                            unset($widgetData['data_type']);

                            $result['page_contents'][$pageContent->pages_code][$pageContent->page_sections_code][] = [
                                'data_type' => $dataType,
                                'code' => $pageContent->code,
                                'img' => $pageContent->media_store_id ? route('media.image.download', ['id' => $pageContent->media_store_id, 'resize_width' => 300]) : null,
                                'cta' => $pageContent->cta_target ? [
                                    'target' => $pageContent->cta_target,
                                    'label' => $requestedLang ? getLanguageTranslation($pageContent->cta_label, $requestedLang) : $pageContent->cta_label,
                                    'code' => $pageContent->cta_page_code,
                                    'url' => $pageContent->cta_url,
                                ] : false,
                                'widget_data' => $widgetData,
                                'value' => $dataType === 'FREE_TEXT' ? ($requestedLang ? getLanguageTranslation($pageContent->value, $requestedLang) : $pageContent->value) : ''
                            ];
                        }
                        break;
                    default:
                        $result['page_contents'][$pageContent->pages_code][$pageContent->page_sections_code][$pageContent->code] = $requestedLang ? getLanguageTranslation($pageContent->value, $requestedLang) : $pageContent->value;
                        break;
                }
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
     * Fetches page sections 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getPageSections($where = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'pages' => []
        ];

        try {
            $query = DB::table('page_sections AS ps')
                ->leftJoin('pages AS p', 'p.id', '=', 'ps.pages_id')
                ->select(
                    'ps.id AS id',
                    'ps.code AS code',
                    'ps.name AS name',
                    'p.id AS pages_id',
                    'p.code AS pages_code',
                    'p.name AS pages_name',
                    'p.page_title AS pages_page_title',
                    'p.meta_description AS pages_meta_description',
                    'p.meta_keywords AS pages_meta_keywords'
                )
                ->whereIn('ps.code', ['PILLARS']);

            if (count($where) > 0) {
                $query->where($where);
            }

            $pageSectionsList = $query->get()->toArray();

            foreach ($pageSectionsList as $pageSection) {
                $result['pages'][] = [
                    'id' => $pageSection->id,
                    'code' => $this->getPageCodeUri($pageSection->pages_code),
                    'name' => $pageSection->pages_name
                ];
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
     * Fetches header sizes 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getHeaderSizes($where = [])
    {
        return DB::table('application_code AS ac')
            ->select('ac.id AS id', 'ac.name AS name')
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            ->where([
                'act.code' => 'PAGE_HEADER_SIZE',
            ])->get()->toArray();
    }

    public function getAvailableWidgets()
    {
        return PageWidget::select('id', 'code', 'name')
            ->where([
                'active' => true
            ])
            ->get();
    }

    private function getPageCodeUri(?string $pageCode): string
    {
        // TODO

        // $pageCode = strtolower($pageCode);
        // return $pageCode === 'main' ? '' : $pageCode;

        return strtolower($pageCode);
    }

    /**
     * Fetches page contents 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getPageContentBy($where = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'page_contents' => []
        ];

        try {
            $query = DB::table('page_contents AS pc')
                ->leftJoin('page_sections AS ps', 'ps.id', '=', 'pc.page_sections_id')
                ->leftJoin('pages AS p', 'p.id', '=', 'ps.pages_id')
                ->select(
                    'pc.id AS id',
                    'pc.code AS code',
                    'pc.value AS value',
                    'ps.id AS page_sections_id',
                    'ps.code AS page_sections_code',
                    'ps.name AS page_sections_name',
                    'p.id AS pages_id',
                    'p.code AS pages_code',
                    'p.name AS pages_name',
                    'p.page_title AS pages_page_title',
                    'p.meta_description AS pages_meta_description',
                    'p.meta_keywords AS pages_meta_keywords'
                );

            if (count($where) > 0) {
                $query->where($where);
            }

            $result['page_contents'] = $query->get()->toArray();

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
     * Fetches headers 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getHeaders($where = [], $pages = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'headers' => []
        ];

        $where = array_merge($where, [
            'ps.code' => 'HEADER'
        ]);

        try {
            $query = DB::table('pages AS p')
                ->leftJoin('page_sections AS ps', 'p.id', '=', 'ps.pages_id')
                ->leftJoin('page_contents AS pc', 'ps.id', '=', 'pc.page_sections_id')
                ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.header_size_id')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->leftJoin('media_store AS ms', function ($join) {
                    $join->on('ms.entity_id', '=', 'p.id');
                    $join->on('ms.entity_name', '=', DB::raw("'PageHeaderImage'"));
                })
                ->select(
                    'pc.id AS id',
                    'pc.name AS name',
                    'pc.code AS code',
                    'pc.value AS value',
                    'ps.id AS page_sections_id',
                    'ps.code AS page_sections_code',
                    'ps.name AS page_sections_name',
                    'p.id AS pages_id',
                    'p.code AS pages_code',
                    'p.name AS pages_name',
                    'ac.code AS header_size_code',
                    'ac.name AS header_size_name',
                    'p.page_title AS pages_page_title',
                    'p.meta_description AS pages_meta_description',
                    'p.meta_keywords AS pages_meta_keywords',
                    'ms.id AS media_store_id',
                    'ms.mime_type AS mime_type',
                    'ms.content AS img_content'
                )
                ->where($where);

            if (count($pages) > 0) {
                $query->whereIn('p.code', $pages);
            }


            $data = $query->get()->toArray();

            foreach ($data as $header) {
                $result['headers'][$header->pages_code]['_page_id'] = $header->pages_id;
                $result['headers'][$header->pages_code]['size_code'] = $header->header_size_code;
                $result['headers'][$header->pages_code]['size_name'] = $header->header_size_name;
                $result['headers'][$header->pages_code]['_section_id'] = $header->page_sections_id;
                $result['headers'][$header->pages_code]['_page_name'] = $header->pages_name;
                $result['headers'][$header->pages_code]['_has_header_img'] = $header->media_store_id;
                $result['headers'][$header->pages_code]['_img_src'] = $header->media_store_id ? route('media.image.download', ['id' => $header->media_store_id, 'resize_width' => 300]) : $this->getPlaceholderImageSrc();
                $result['headers'][$header->pages_code][$header->code] = $header->value;
                $result['headers'][$header->pages_code]['header_ctas'] = $this->buildHeaderCtas([
                    'hc.pages_id' => $header->pages_id,
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

    public function getPageData($code, $requestedLang = null)
    {
        $code = strtolower($code);

        $pageContents = $this->getPageContents([
            [DB::raw('lower(p.code)'), '=', $code],
            'pc.active' => '1'
        ], $requestedLang)['page_contents'];

        $contents = $pageContents[$code] ?? [];

        $headerImage = false;
        if (isset($pageContents['id'])) {
            $headerImage = MediaStore::where([
                'entity_name' => 'PageHeaderImage',
                'entity_id' => (string)$pageContents['id']
            ])->first();
        }

        $mainHeaderImg = $headerImage ? route('media.image.download', ['id' => $headerImage->id]) : false;

        $result = [
            'success' => count($contents) > 0 ? true : false,
            'page_id' => $pageContents['id'] ?? null,
            'main_header_img' => $mainHeaderImg,
            'meta' => array_key_exists('META', $contents) ? $contents['META'] : [],
            'headers' => array_key_exists('HEADER', $contents) ? $contents['HEADER'] : [],
            'pillars' => array_key_exists('PILLARS', $contents) ? $contents['PILLARS'] : [],
        ];

        return $result;
    }

    public function buildHeaderCtas($where, $requestedLang = null)
    {
        $ctasQuery = DB::table('header_ctas AS hc')
            ->leftJoin('page_sections AS ps', 'ps.id', '=', 'hc.url')
            ->select(
                'hc.id AS id',
                'hc.name AS name',
                'hc.url AS url',
                'ps.id AS url_id',
                DB::raw("(CASE WHEN hc.url = 'EMPTY_URI' THEN '' ELSE hc.url END) AS url"),
                DB::raw("(CASE WHEN hc.url_type = 'internal' THEN (SELECT LOWER(code) FROM pages WHERE id = ps.pages_id) ELSE hc.url END) AS url"),
                'hc.url_type AS url_type',
                'hc.style AS style',
                'hc.order AS order',
                'hc.active AS active'
            )

            ->where($where)
            ->orderBy('hc.order', 'asc');

        if (!is_null($requestedLang)) {
            $ctasQuery->selectRaw('label, (
                    SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.id = ?
                            AND lc.code = hc.label
                        ) AS label', [$requestedLang]);
        } else {
            $ctasQuery->addSelect('hc.label AS label');
        }

        $result = $ctasQuery->get()->toArray();

        $ctas = [];
        foreach ($result as $rec) {
            $ctas[$rec->id] = $rec;

            $translations = getCodesTranslations([$rec->label], true);

            if ($rec->url_type === 'internal') {
                $ctas[$rec->id]->url = $this->getPageCodeUri($ctas[$rec->id]->url);
            }

            foreach ($translations as $id => $translation) {
                $ctas[$rec->id]->{'header_cta_' . $rec->id . '_label_' . $id} = $translation[$rec->label];
            }
        }

        return $ctas;
    }

    /**
     * Fetches one page 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getOnePage($where = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'page' => []
        ];

        try {
            $query = DB::table('pages AS p')
                ->orderBy('p.updated_at', 'desc')
                ->select(
                    'p.id AS id',
                    'p.code AS code',
                    'p.name AS name',
                    'p.created_at AS created_at',
                    'p.updated_at AS updated_at'
                )
                ->whereNotIn('p.code', [
                    Constants::PAGE_CODE_MAIN,
                    Constants::PAGE_CODE_ABOUT,
                ]);

            if (count($where) > 0) {
                $query->where($where);
            }

            $result['page'] = $query->first();
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

    public function updateCtas($pageId, $ctas, $deletedCtas)
    {
        $deletedCtas = $deletedCtas ?? [];

        HeaderCta::whereIn('id', array_filter($deletedCtas, function ($item) {
            return !str_contains($item, 'new');
        }))->delete();

        foreach ($ctas as $cta) {
            if (!in_array($cta['id'], $deletedCtas)) {
                $submittedCta = $cta;
                $ctaName = $cta['name'] ?? '.';
                $ctaLabel = $cta['label'] ?? '.';

                if (str_contains($cta['id'], 'new')) {
                    $newCta = HeaderCta::create([
                        'pages_id' => $pageId,
                        'name' => $ctaName,
                        'label' => $ctaLabel,
                        'url' => '.',
                        'url_type' => '.',
                        'style' => '.',
                    ]);

                    $ctaName = 'header_cta' . $newCta->id;
                    $ctaLabel = strtoupper($ctaName . '_LABEL');
                    $newCta->update([
                        'name' => $ctaName
                    ]);

                    $submittedCta['url'] = $submittedCta['url'] ?? '.';
                } else {
                    $newCta = HeaderCta::firstOrNew([
                        'pages_id' => $pageId,
                        'name' => $ctaName,
                    ]);
                }

                if ($ctaLabel) {
                    $newCta->label = $ctaLabel;
                    $newCta->style = $submittedCta['style'];
                    $newCta->url_type = $submittedCta['url_type'];

                    if ($submittedCta['url_type'] === 'internal') {
                        $newCta->url = (int)($submittedCta['url_id'] ?? 0);
                    } else {
                        $newCta->url = $submittedCta['url'];
                    }

                    if (!$submittedCta['url'] && $submittedCta['url_type'] === 'external') {
                        throw new Exception('The Url field is required!');
                    }
                    $newCta->order = intval($cta['order']);
                    $newCta->active = determineBool($submittedCta['active']);
                    $newCta->save();

                    $availableLanguages = getAvailableLanguages();

                    $translationData = ['label' => $newCta->label];

                    foreach ($availableLanguages as $lang) {
                        $translationData['label_' . $lang->id] = $submittedCta['header_cta_' . $cta['id'] . '_label_' . $lang->id] ?? '';
                    }

                    $this->translationService->translateFields([
                        'label'
                    ], $translationData);
                } else {
                    HeaderCta::where(array(
                        'pages_id' => $pageId,
                        'name' => $ctaName,
                    ))->delete();
                }
            }
        }
    }

    public function getPageContentWidgetData(int $pageContentId, bool $withData = false, string $requestedLang = null): array
    {
        $widgetData = [];

        $rec = PageWidgetData::where(['page_contents_id' => $pageContentId])
            ->select('data', 'module_id')
            ->first();

        if ($rec) {
            $widgetData =  json_decode($rec->data, true) ?? [];
            $widgetDataLoad = filter_var($widgetData['load_data'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $widgetData['load_data'] = $widgetDataLoad;

            $widgetData['widget_header'] = $widgetData['widget_header'] ?? '';
            $widgetData['widget_sub_header'] = $widgetData['widget_sub_header'] ?? '';
            $widgetData['widget_no_records_message'] = $widgetData['widget_no_records_message'] ?? '';
            $widgetData['widget_more_target'] = $widgetData['widget_more_target'] ?? '';
            $widgetData['widget_more_label'] = $widgetData['widget_more_label'] ?? '';
            $widgetData['widget_more_page_sections_id'] = $widgetData['widget_more_page_sections_id'] ?? '';

            $clientModuleFilters = $widgetData['client_module_filters'] ?? [];

            if ($clientModuleFilters) {
                $filterActive = filter_var($clientModuleFilters['active'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $widgetData['client_module_filters']['active'] = $filterActive;
            }

            $module = ClientModule::find($rec->module_id);

            $widgetData['module_code'] = $module->code;

            if ($withData) {
                $widgetDataType = $widgetData['data_type'] ?? '';

                switch ($widgetDataType) {
                    case Constants::AC_PAGE_SECTION_LIST_WIDGET:
                    case Constants::AC_PAGE_SECTION_SEARCH_WIDGET:
                        $widgetData = array_merge($widgetData, $this->buildWidgetModuleUrl(
                            $rec->module_id,
                            $widgetDataType,
                            $clientModuleFilters,
                            $widgetDataLoad,
                            $requestedLang
                        ));

                        break;
                }
            } else {
                unset($widgetData['data'], $widgetData['url']);
            }
        }


        return $widgetData;
    }
}
