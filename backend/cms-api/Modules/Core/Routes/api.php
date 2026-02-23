<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AdministrationController;
use Modules\Core\Http\Controllers\Auth\AuthController;
use Modules\Core\Http\Controllers\Auth\ProfileController;
use Modules\Core\Http\Controllers\PaymentsController;
use Modules\Core\Http\Controllers\ContactController;
use Modules\Core\Http\Controllers\CurrencyController;
use Modules\Core\Http\Controllers\LanguageController;
use Modules\Core\Http\Controllers\MediaController;
use Modules\Core\Http\Controllers\NavigationController;
use Modules\Core\Http\Controllers\SocialMediaController;
use Modules\Core\Http\Controllers\SubscriptionsController;
use Modules\Core\Http\Controllers\UsersController;

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
$middlewares = ['auth:api'];

if ('production' === $environment) {
    // array_push($middlewares, 'cache.headers:private;max_age=3600');
}

Route::prefix('admin')->group(function () {
    Route::match(['GET', 'POST'], '/reset-db/{code}', [AuthController::class, 'resetDb'])->name('admin.reset_database');
});

Route::prefix('media')->group(function () {
    Route::get('/download/{id}/{resize_width?}', [MediaController::class, 'show'])->name('media.image.download');
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/finalise-account', [AuthController::class, 'finaliseAccount']);
    Route::get('/validate-token/{token}', [AuthController::class, 'checkPasswordResetToken']);
    Route::post('/update-password', [AuthController::class, 'updatePassword'])->name('auth.update_password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset_password');
});

Route::group(['middleware' => array_merge($middlewares, ['validateIp'])], function () {
    // Route::get('/translate/{lang}', [TranslationController::class, 'translateLang'])->name('translation_api.translate_language');
});

Route::group(['middleware' => $middlewares], function () {
    Route::prefix('contacts')->group(function () {
        Route::get('/ac', [ContactController::class, 'contactsAC']);
        Route::get('/non-users/ac', [ContactController::class, 'nonUsersContactsAC']);
    });

    Route::prefix('account')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('user_profile_page_show');
        Route::post('/profile/update', [ProfileController::class, 'update'])->name('user_profile_page_update');
    });

    Route::group(['middleware' => ['role:owner|admin,api']], function () {
        Route::prefix('administration')->group(function () {
            Route::prefix('languages')->group(function () {
                Route::prefix('general-translations')->group(function () {
                    Route::post('/', [LanguageController::class, 'generalTranslations'])->name('admin.languages.general.translations.list');
                    Route::get('/{code}', [LanguageController::class, 'showGeneralTranslation'])->where('id', '[0-9]+')->name('admin.languages.general.translations.show');
                    Route::post('/{code}', [LanguageController::class, 'updateGeneralTranslation'])->name('admin.languages.general.translations.update');
                });

                // Route::get('/publish', 'LanguageController@publishLanguages')->name('admin.languages.publish');
                Route::post('/', [LanguageController::class, 'index'])->name('admin.languages.list');
                Route::get('/new', [LanguageController::class, 'create'])->name('admin.languages.create_new');
                Route::post('/create', [LanguageController::class, 'store'])->name('admin.languages.save');
                Route::post('/delete/{id}', [LanguageController::class, 'destroy'])->where('id', '[0-9]+')->name('admin.languages.delete');
                Route::get('/{id}', [LanguageController::class, 'show'])->where('id', '[0-9]+')->name('admin.languages.show');
                Route::post('/{id}', [LanguageController::class, 'update'])->name('admin.languages.update');
            });

            Route::prefix('currencies')->group(function () {
                Route::post('/', [CurrencyController::class, 'index'])->name('admin.currencies.list');
                Route::get('/new', [CurrencyController::class, 'create'])->name('admin.currencies.create_new');
                Route::post('/create', [CurrencyController::class, 'store'])->name('admin.currencies.save');
                Route::post('/delete/{id}', [CurrencyController::class, 'destroy'])->where('id', '[0-9]+')->name('admin.currencies.delete');
                Route::get('/view/{id}', [CurrencyController::class, 'show'])->where('id', '[0-9]+')->name('admin.currencies.show');
                Route::post('/update/{id}', [CurrencyController::class, 'update'])->name('admin.currencies.update');
            });

            Route::prefix('users')->group(function () {
                Route::post('/', [UsersController::class, 'index'])->name('admin.users.list');
                Route::get('/new', [UsersController::class, 'create'])->name('admin.users.create_new');
                Route::post('/create', [UsersController::class, 'store'])->name('admin.users.save');
                Route::post('/delete/{id}', [UsersController::class, 'destroy'])->name('admin.users.delete');
                Route::get('/{id}', [UsersController::class, 'show'])->name('admin.users.show');
                Route::post('/{id}', [UsersController::class, 'update'])->name('admin.users.update');
            });

            Route::get('/social-media-links', [SocialMediaController::class, 'edit'])->name('admin.social_media.edit');
            Route::post('/social-media-links/update', [SocialMediaController::class, 'edit'])->name('admin.social_media.update');

            Route::get('/main-contacts', [ContactController::class, 'editMainContacts'])->name('admin.main_contacts.edit');
            Route::post('/main-contacts/update', [ContactController::class, 'editMainContacts'])->name('admin.main_contacts.update');

            Route::prefix('navigation')->group(function () {
                Route::post('/', [NavigationController::class, 'index'])->name('admin.navigation.list');
                Route::get('/new', [NavigationController::class, 'create'])->name('admin.navigation.create_new');
                Route::post('/create', [NavigationController::class, 'store'])->name('admin.navigation.save');
                Route::post('/delete/{id}', [NavigationController::class, 'destroy'])->where('id', '[0-9]+')->name('admin.navigation.delete');
                Route::get('/view/{id}', [NavigationController::class, 'show'])->where('id', '[0-9]+')->name('admin.navigation.show');
                Route::post('/update/{id}', [NavigationController::class, 'update'])->name('admin.navigation.update');
            });

            Route::get('/api-manager', [AdministrationController::class, 'index'])->name('admin.api.manager');
            Route::post('/refresh-api-key', [AdministrationController::class, 'refreshApiKey'])->name('admin.api.refresh_key');

            Route::get('/private-apis', [AdministrationController::class, 'editPrivateAPIsProps'])->name('admin.api.private_apis.edit');
            Route::post('/private-apis/update', [AdministrationController::class, 'editPrivateAPIsProps'])->name('admin.api.private_apis.update');
        });
    });

    Route::prefix('finance')->group(function () {
        Route::prefix('payments')->group(function () {
            Route::post('/', [PaymentsController::class, 'index'])->name('finance.payments.list');
            Route::get('/new', [PaymentsController::class, 'create'])->name('finance.payments.create_new');
            Route::post('/create', [PaymentsController::class, 'store'])->name('finance.payments.save');
            Route::post('/delete/{id}', [PaymentsController::class, 'destroy'])->where('id', '[0-9]+')->name('finance.payments.delete');
            Route::get('/{id}', [PaymentsController::class, 'show'])->where('id', '[0-9]+')->name('finance.payments.show');
            Route::post('/{id}', [PaymentsController::class, 'update'])->name('finance.payments.update');

            Route::get('/stats/months/{months}', [PaymentsController::class, 'getDashboardStatsByMonths'])->where('months', '[0-9]+')->name('finance.payments.stats.months');
        });
    });

    Route::prefix('subscriptions')->group(function () {
        Route::prefix('stats')->group(function () {
            Route::get('/months/{months}', [SubscriptionsController::class, 'getDashboardStatsByMonths'])->where('months', '[0-9]+')->name('subscriptions.stats.months');
        });
    });
});
