<?php

use Illuminate\Support\Facades\Route;
use Modules\Team\Http\Controllers\TeamController;

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
    Route::prefix('team-members')->group(function () {
        Route::post('/', [TeamController::class, 'index'])->name('team_members.members.list');
        Route::get('/new', [TeamController::class, 'create'])->name('team_members.members.create_new');
        Route::post('/create', [TeamController::class, 'store'])->name('team_members.members.save');
        Route::post('/delete/{id}', [TeamController::class, 'destroy'])->where('id', '[0-9]+')->name('team_members.members.delete');
        Route::get('/{id}', [TeamController::class, 'show'])->where('id', '[0-9]+')->name('team_members.members.show');
        Route::post('/{id}', [TeamController::class, 'update'])->name('team_members.members.update');
    });
});
