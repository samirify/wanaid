<?php

use Illuminate\Support\Facades\Route;
use Modules\PageComponents\Http\Controllers\PageBuilderController;
use Modules\PageComponents\Http\Controllers\PillarsController;
use Modules\PageComponents\Http\Controllers\PagesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['auth:api']], function () {
    Route::prefix('page-components/sections')->group(function () {
        Route::post('/', [PillarsController::class, 'index'])->name('page_components.pillars.list');
        Route::get('/new', [PillarsController::class, 'create'])->name('page_components.pillars.create_new');
        Route::post('/create', [PillarsController::class, 'store'])->name('page_components.pillars.save');
        Route::post('/delete/{id}', [PillarsController::class, 'destroy'])->name('page_components.pillars.delete');
        Route::get('/layout', [PillarsController::class, 'layout'])->name('page_components.pillars.layout');
        Route::post('/layout/update', [PillarsController::class, 'updateLayout'])->name('page_components.pillars.update_layout');
        Route::get('/{id}', [PillarsController::class, 'show'])->name('page_components.pillars.show');
        Route::post('/{id}', [PillarsController::class, 'update'])->name('page_components.pillars.update');
    });

    Route::prefix('page-components/pages')->group(function () {
        Route::post('/', [PagesController::class, 'index'])->name('page_components.pages.list');
        Route::get('/new', [PagesController::class, 'create'])->name('page_components.pages.create_new');
        Route::post('/create', [PagesController::class, 'store'])->name('page_components.pages.save');
        Route::post('/delete/{id}', [PagesController::class, 'destroy'])->where('id', '[0-9]+')->name('page_components.pages.delete');
        // Route::get('/layout', 'PagesController@layout')->name('page_components.pages.layout');
        // Route::put('/update-layout', 'PagesController@updateLayout')->name('page_components.pages.update_layout');
        Route::get('/{id}', [PagesController::class, 'show'])->where('id', '[0-9]+')->name('page_components.pages.show');
        Route::post('/{id}', [PagesController::class, 'update'])->name('page_components.pages.update');
    });

    Route::prefix('page-components/builder/page')->group(function () {
        Route::get('/new', [PageBuilderController::class, 'create'])->name('pages.builder.create_new');
        Route::post('/create', [PageBuilderController::class, 'store'])->name('pages.builder.save');
        Route::post('/delete/{id}', [PageBuilderController::class, 'destroy'])->where('id', '[0-9]+')->name('pages.builder.delete');
        Route::get('/{id}', [PageBuilderController::class, 'show'])->where('id', '[0-9]+')->name('pages.builder.show');
        Route::get('/{id}/new', [PageBuilderController::class, 'showNew'])->where('id', '[0-9]+')->name('pages.builder.new');
        Route::post('/{id}', [PageBuilderController::class, 'update'])->where('id', '[0-9]+')->name('pages.builder.update');
    });
});
