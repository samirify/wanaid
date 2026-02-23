<?php

use Illuminate\Support\Facades\Route;
use Modules\PageComponents\Http\Controllers\PagesController;
use Modules\PageComponents\Http\Controllers\SitemapController;

Route::group(['middleware' => ['api', 'sfy_api_key']], function () {
    Route::prefix('api')->group(function () {
        Route::prefix(config('client.api.version'))->group(function () {
            Route::prefix('pages')->group(function () {
                Route::get('/{code}', [PagesController::class, 'page'])->name('pages.single_page_data');
                Route::get('/{code}/{recordSlug}', [PagesController::class, 'recordPage'])->name('pages.inner_single_page_data');
            });

            Route::prefix('sitemap')->group(function () {
                Route::get('/', [SitemapController::class, 'sitemap'])->name('sitemap.get_website_sitemap');
            });
        });
    });
});
