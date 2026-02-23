<?php

namespace Modules\PageComponents\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SitemapService
{
    public function __construct() {}

    public function getSitemap(Request $request): array
    {
        $result = [
            'success' => false
        ];

        DB::beginTransaction();

        try {
            $format = $request->get('format', 'json');

            $sitemapData = [];

            $pages = DB::table('pages AS p')
                ->select(
                    'p.code AS page_code',
                    'p.name AS page_name',
                )
                ->get()
                ->toArray();

            foreach ($pages as $page) {
                array_push($sitemapData, $page);
            }

            switch (strtolower($format)) {
                case 'xml':
                    $sitemap = $this->formatSitemapXML($sitemapData);
                    break;
                case 'json':
                default:
                    $sitemap = $this->formatSitemapJSON($sitemapData);
                    break;
            }

            $result = [
                'success' => true,
                'sitemap' => $sitemap
            ];

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['code'] = $th->getCode();
            $result['error'] = $th->getMessage();
        }

        return $result;
    }

    private function formatSitemapJSON(array $sitemapData): array
    {
        $siteRoot = config('client.site.root');
        $urlsArray = [];

        foreach ($sitemapData as $item) {
            $defaultLang = 'en'; // TODO: Get default language
            $url = $siteRoot . (empty($item->page_code) ? '/' : "/{$defaultLang}/") . rtrim($item->page_code, '/');
            $lastmod = date('Y-m-d');
            $urlsArray[$url] = $lastmod;
        }

        $result = [];

        foreach ($urlsArray as $url => $lastmod) {
            array_push($result, [
                'url' => [
                    'loc' => $url,
                    'lastmod' => $lastmod
                ]
            ]);
        }

        return $result;
    }

    private function formatSitemapXML(array $sitemapData): string
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
            . "\r\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

        $siteRoot = config('client.site.root');
        $urlsArray = [];

        foreach ($sitemapData as $item) {
            $defaultLang = 'en'; // TODO: Get default language
            $url = $siteRoot . (empty($item->page_code) ? '/' : "/{$defaultLang}/") . rtrim($item->page_code, '/');
            $lastmod = date('Y-m-d');
            $urlsArray[$url] = $lastmod;
        }

        foreach ($urlsArray as $url => $lastmod) {
            $xml .= "\r\n    <url>"
                . "\r\n        <loc>$url</loc>"
                . "\r\n        <lastmod>$lastmod</lastmod>"
                . "\r\n    </url>";
        }

        $xml .= "\r\n</urlset>";

        return $xml;
    }
}
