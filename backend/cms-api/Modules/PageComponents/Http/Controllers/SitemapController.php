<?php

namespace Modules\PageComponents\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\PageComponents\Services\SitemapService;

class SitemapController extends Controller
{
    use SAAApiResponse;

    public function __construct(
        private readonly SitemapService $sitemapService,
    ) {}

    /**
     * Get website's sitemap
     */
    public function sitemap(Request $request): Response|JsonResponse
    {
        try {
            $sitemap = $this->sitemapService->getSitemap($request);

            if ($sitemap['success']) {
                return $this->successResponse([
                    'sitemap' => $sitemap['sitemap']
                ]);
            }

            throw new Exception($sitemap['error'] ?? 'Could not retrieve sitemap!', $sitemap['code'] ?? 500);
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }
}
