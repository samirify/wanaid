<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Query\Builder;

trait AppHelperTrait
{
    const TABLE_PREFIX = 'cl_';

    /**
     * @param string $title
     * 
     * @return string
     */
    public function formatUniqueTitle(string $title): string
    {
        return strtolower(Str::slug(getLanguageTranslation(translation_code: $title, supportEmptyTranslation: false)));
    }

    /**
     * @param string $code
     * 
     * @return string
     */
    public function formatCode(string $code): string
    {
        return strtoupper(md5($code));
    }

    public function updateAppSitemap()
    {
        try {
            $filePath = config('client.sitemap_location');

            $siteRoot = config('client.site.root');
            $urls = [];

            $standardPageCodes = config('client.site.standard_pages');

            $pages = DB::table('pages AS p')
                ->select(
                    'p.code AS pages_code',
                )
                ->get()
                ->toArray();

            foreach ($pages as $page) {
                if (!in_array($page->pages_code, $standardPageCodes)) {
                    array_push($urls, $page->pages_code);
                }
            }

            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
                . "\r\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

            $urlsArray = [];

            foreach ($urls as $url) {
                $defaultLang = 'en'; // TODO: Get default language
                $url = $siteRoot . (empty($url) ? '/' : "/{$defaultLang}/") . rtrim($url, '/');
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

            file_put_contents($filePath, $xml);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function updateEnvValues(array $values): void
    {
        $path = base_path('.env');

        if (file_exists($path) && !empty($values)) {
            $contents = file_get_contents($path);

            foreach ($values as $key => $value) {
                $key = $key . '=';

                if (str_contains($contents, $key)) {
                    file_put_contents($path, implode(
                        '',
                        array_map(function ($data) use ($key, $value) {
                            return stristr($data, $key) ?  $key . "{$value}" . PHP_EOL : $data;
                        }, file($path))
                    ));
                } else {
                    file_put_contents($path, PHP_EOL . $key . "{$value}", FILE_APPEND);
                }
            }

            file_put_contents($path, PHP_EOL, FILE_APPEND);

            Cache::flush();
        }
    }

    public function formatModuleCode(string $code): string
    {
        return str_replace('-', '_', $this->formatUniqueTitle($code));
    }

    public function formatModuleTableName(string $code): string
    {
        return self::TABLE_PREFIX . $this->formatModuleCode($code);
    }

    public function formatModuleViewName(string $code): string
    {
        return self::TABLE_PREFIX . 'v_' . $this->formatModuleCode($code);
    }

    public function generateRecordCodeFromIdAndPrefix(int $id, string $prefix = ''): string
    {
        return $prefix . sprintf('%08d', $id);
    }

    public function filterDataTable(Request $request, Builder $query, ?string $alias = null): Builder
    {
        $filters = $request->get('filters', []);

        $globalFilterValue = $filters['global']['value'] ?? null;
        unset($filters['global']);

        $query->where(function ($query) use ($filters, $globalFilterValue, $alias) {
            if ($globalFilterValue) {
                foreach ($filters as $column => $filter) {
                    $column = strtolower($column);
                    $columnName = $alias ? "{$alias}.{$column}" : $column;
                    $query->orWhere($columnName, 'LIKE', "%" . strtolower($globalFilterValue) . "%");
                }
            } else {
                foreach ($filters as $column => $filter) {
                    $column = strtolower($column);
                    $columnName = $alias ? "{$alias}.{$column}" : $column;
                    if ($filter['value']) $query->where($columnName, 'LIKE', "%" . strtolower($filter['value']) . "%");
                }
            }
        });



        return $query;
    }

    public function snakeToReadable(string $str): string
    {
        return title_case(str_replace('_', ' ', $str));
    }

    // public function addAliasToColumns(array $columns, string $alias): array
    // {
    //     $colmNamesWithAlias = [];

    //     foreach ($columns as $column) {
    //         array_push($colmNamesWithAlias, "{$alias}.{$column} AS {$column}");
    //     }

    //     return $colmNamesWithAlias;
    // }
}
