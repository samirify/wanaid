<?php

use Illuminate\Support\Facades\Route;
use Modules\Team\Http\Controllers\TeamController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['api', 'sfy_api_key']], function () {
    Route::prefix('api')->group(function () {
        Route::prefix(config('client.api.version'))->group(function () {
            Route::prefix('team')->group(function () {
                Route::get('/', [TeamController::class, 'teamMembersWebView'])->name('team.active_team_members_data');
                Route::get('/{unique_title}', [TeamController::class, 'teamMemberWebView'])->name('team.single_team_member_data');
            });
        });
    });
});
