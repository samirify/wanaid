<?php

use Illuminate\Support\Facades\Route;
use Modules\Department\Http\Controllers\DepartmentController;

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

$environment = config('client.app_environment');
$middlewares = ['api'];

if ('production' === $environment) {
    // array_push($middlewares, 'cache.headers:private;max_age=3600');
}

Route::group(['middleware' => $middlewares], function () {
    Route::prefix('departments')->group(function () {
        Route::post('/', [DepartmentController::class, 'index'])->name('departments.list');
        Route::get('/new', [DepartmentController::class, 'create'])->name('departments.create_new');
        Route::post('/create', [DepartmentController::class, 'store'])->name('departments.save');
        Route::post('/delete/{id}', [DepartmentController::class, 'destroy'])->where('id', '[0-9]+')->name('departments.delete');
        Route::get('/{id}', [DepartmentController::class, 'show'])->where('id', '[0-9]+')->name('departments.show');
        Route::post('/{id}', [DepartmentController::class, 'update'])->name('departments.update');
    });
});
