<?php

use Illuminate\Support\Facades\Route;
use Modules\Client\Http\Controllers\ClientIdentityController;
use Modules\Client\Http\Controllers\ClientModulesController;
use Modules\Client\Http\Controllers\ClientModuleRecordController;
use Modules\Client\Http\Controllers\ClientThemeController;
use Modules\Client\Http\Controllers\DashboardController;

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
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'dashboard'])->name('client_dashboard.dashboard');
        Route::get('/test-firebase', [DashboardController::class, 'getFirebaseUser'])->name('client_dashboard.test_firebase');
    });

    Route::prefix('client-modules')->group(function () {
        Route::post('/{moduleCode}', [ClientModuleRecordController::class, 'index'])->name('client_modules.list');
        Route::get('/{moduleCode}/new', [ClientModuleRecordController::class, 'create'])->name('client_modules.create_new');
        Route::post('/{moduleCode}/create', [ClientModuleRecordController::class, 'store'])->name('client_modules.save');
        Route::post('/{moduleCode}/delete/{id}', [ClientModuleRecordController::class, 'destroy'])->name('client_modules.delete');
        Route::get('/{moduleCode}/view/{id}', [ClientModuleRecordController::class, 'show'])->name('client_modules.show');
        Route::post('/{moduleCode}/{id}', [ClientModuleRecordController::class, 'update'])->name('client_modules.update');

        Route::prefix('media')->group(function () {
            Route::post('/upload/{moduleCode}/{moduleId?}', [ClientModuleRecordController::class, 'uploadClientModuleMedia'])->name('media.files.upload_client_module_media');
            Route::post('/delete/{fileId}/{moduleCode}/{moduleId?}', [ClientModuleRecordController::class, 'deleteClientModuleMedia'])->name('media.files.delete_client_module_media');
        });
    });

    Route::prefix('available-client-modules')->group(function () {
        Route::get('/', [ClientModulesController::class, 'index'])->name('available_client_modules.list');
        Route::get('/new', [ClientModulesController::class, 'create'])->name('available_client_modules.create_new');
        Route::post('/create', [ClientModulesController::class, 'store'])->name('available_client_modules.save');
        Route::post('/delete/{code}', [ClientModulesController::class, 'destroy'])->name('available_client_modules.delete');
        Route::get('/view/{code}', [ClientModulesController::class, 'show'])->name('available_client_modules.show');
        Route::put('/update/{code}', [ClientModulesController::class, 'update'])->name('available_client_modules.update');
    });

    Route::group(['middleware' => ['role:owner|admin,api']], function () {
        Route::prefix('administration')->group(function () {
            Route::prefix('client')->group(function () {
                Route::prefix('identity')->group(function () {
                    Route::post('/', [ClientIdentityController::class, 'index'])->name('client.admin.identity.list');
                    Route::get('/new', [ClientIdentityController::class, 'create'])->name('client.admin.identity.create_new');
                    Route::post('/create', [ClientIdentityController::class, 'store'])->name('client.admin.identity.save');
                    Route::post('/delete/{id}', [ClientIdentityController::class, 'destroy'])->where('id', '[0-9]+')->name('client.admin.identity.delete');
                    Route::get('/{id}', [ClientIdentityController::class, 'show'])->where('id', '[0-9]+')->name('client.admin.identity.show');
                    Route::post('/{id}', [ClientIdentityController::class, 'update'])->name('client.admin.identity.update');
                });
                Route::prefix('theme')->group(function () {
                    Route::post('/', [ClientThemeController::class, 'index'])->name('client.admin.theme.list');
                    Route::get('/new', [ClientThemeController::class, 'create'])->name('client.admin.theme.create_new');
                    Route::post('/create', [ClientThemeController::class, 'store'])->name('client.admin.theme.save');
                    Route::post('/delete/{id}', [ClientThemeController::class, 'destroy'])->where('id', '[0-9]+')->name('client.admin.theme.delete');
                    Route::get('/{id}', [ClientThemeController::class, 'show'])->where('id', '[0-9]+')->name('client.admin.theme.show');
                    Route::post('/{id}', [ClientThemeController::class, 'update'])->name('client.admin.theme.update');
                });
            });
        });
    });
});


Route::group(['middleware' => ['api', 'sfy_api_key']], function () {
    Route::prefix(config('client.api.version'))->group(function () {
        Route::prefix('module')->group(function () {
            Route::post('/{moduleCode}', [ClientModuleRecordController::class, 'index'])->name('public_client_modules.list');
            // Route::get('/{moduleCode}/new', [ClientModuleRecordController::class, 'create'])->name('public_client_modules.create_new');
            // Route::post('/{moduleCode}/create', [ClientModuleRecordController::class, 'store'])->name('public_client_modules.save');
            // Route::post('/{moduleCode}/delete/{id}', [ClientModuleRecordController::class, 'destroy'])->name('public_client_modules.delete');
            // Route::get('/{moduleCode}/view/{id}', [ClientModuleRecordController::class, 'show'])->name('public_client_modules.show');
            // Route::post('/{moduleCode}/{id}', [ClientModuleRecordController::class, 'update'])->name('public_client_modules.update');
        });
    });
});
