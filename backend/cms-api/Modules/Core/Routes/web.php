<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\MediaController;
use Modules\Core\Http\Controllers\SettingsController;
use Modules\Core\Http\Controllers\TranslationController;
use Modules\PageComponents\Http\Controllers\PagesController;

$environment = config('client.app_environment');
$middlewares = [];

if ('production' === $environment) {
    // $middlewares = ['cache.headers:private;max_age=300'];
}

Route::group(['middleware' => array_merge(['sfy_api_key'], $middlewares)], function () {
    Route::prefix('api')->group(function () {
        Route::prefix(config('client.api.version'))->group(function () {
            Route::prefix('pages')->group(function () {
                Route::get('/{code}', [PagesController::class, 'page'])->name('pages.single_page_data_api');
            });

            Route::prefix('initialize')->group(function () {
                Route::get('/', [SettingsController::class, 'index']);
            });

            Route::group(['middleware' => ['validateIp']], function () {
                Route::get('/translate/{lang}', [TranslationController::class, 'translateLang'])->name('translation_api.public');
            });
        });
    });
});

Route::group(['middleware' => $middlewares], function () {
    Route::prefix('api')->group(function () {
        Route::prefix(config('client.api.version'))->group(function () {
            Route::prefix('media')->group(function () {
                Route::get('/download/{id}/{resize_width?}', [MediaController::class, 'show'])->name('media.file.download');
            });
        });
    });
});
