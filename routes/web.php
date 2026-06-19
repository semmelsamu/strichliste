<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SoundController;
use App\Http\Controllers\TallySheet;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureTallySessionRunning;
use App\Http\Middleware\EnsureTallySheetUserSelected;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'login'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'authenticate'])->name('authenticate')->middleware('guest');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    Route::get('/article-list', function () {
        return view('pages.article-list', [
            'categories' => Category::with('articles')->get(),
        ]);
    })->name('article-list');

    Route::get('tally-sheet/start-session', [TallySheet\SessionController::class, 'startSession'])->middleware('role:admin|tally_host')->name('tally-sheet.start-session');

    Route::middleware(['role:admin|tally_host', EnsureTallySessionRunning::class])->name('tally-sheet.')->prefix('tally-sheet')->group(function () {

        Route::get('stop-session', [TallySheet\SessionController::class, 'stopSession'])->name('stop-session');

        Route::name('auth.')->controller(TallySheet\LoginController::class)->group(function () {
            Route::get('/', 'listUsers')->name('list-users');
            Route::get('/login/{user}', 'login')->name('login');
            Route::post('/login/{user}', 'validatePin')->name('validate-pin');
            Route::post('/scan-barcode', 'scanBarcode')->name('scan-barcode');
            Route::get('/logout', 'logout')->name('logout');
        });

        Route::controller(TallySheet\UserController::class)->group(function () {
            Route::get('/users/create', 'create')->name('users.create');
            Route::post('/users', 'store')->name('users.store');
        });

        Route::get('/article/{article}', [TallySheet\ViewController::class, 'showArticleDetails'])->name('article-details');
        Route::post('/article/{article}/buy-by-barcode', [TallySheet\TransactionController::class, 'buyArticleByScannedUser'])->name('article-details.buy-by-barcode');

        Route::middleware(EnsureTallySheetUserSelected::class)->group(function () {

            Route::controller(TallySheet\UserController::class)->group(function () {
                Route::get('/user-settings', 'edit')->name('users.edit');
                Route::put('/user-settings', 'update')->name('users.update');
                Route::delete('/user-settings', 'destroy')->name('users.destroy');
                Route::post('/user-settings/pin', 'updatePin')->name('users.update-pin');
                Route::delete('/user-settings/pin', 'removePin')->name('users.remove-pin');
                Route::post('/user-settings/barcode', 'addBarcode')->name('users.add-barcode');
                Route::delete('/user-settings/barcode/{barcode}', 'removeBarcode')->name('users.remove-barcode');
            });

            Route::controller(TallySheet\ViewController::class)->group(function () {

                Route::get('/buy', 'showBuyOverview')->name('buy-overview');
                Route::get('/buy/category/{category_id}', 'showBuyCategory')->name('buy-categories');

                Route::get('/deposit', 'showDeposit')->name('show-deposit');

                Route::get('/history', 'showHistory')->name('history');

            });

            Route::controller(TallySheet\TransactionController::class)->group(function () {

                Route::post('/deposit', 'depositMoney')->name('deposit');

                Route::post('/buy', 'buyArticle')->name('buy');
                Route::post('/buy-by-barcode', 'buyArticleByBarcode')->name('buy-by-barcode');

                Route::post('/undo', 'undoTransaction')->name('undo');

            });

        });
    });

});

Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::controller(ArticleController::class)->group(function () {

        Route::post('articles/{article}/restore', 'restore')->name('articles.restore')->withTrashed();

        Route::post('articles/{article}/price', 'updatePrice')->name('articles.update-price');

        Route::post('articles/{article}/sounds', 'updateSounds')->name('articles.update-sounds');

        Route::post('articles/{article}/barcode', 'addBarcode')->name('articles.add-barcode');
        Route::delete('articles/{article}/barcode/{barcode}', 'removeBarcode')->name('articles.remove-barcode');

        Route::resource('articles', ArticleController::class)->only([
            'index', 'edit', 'update', 'create', 'store', 'destroy',
        ]);

    });

    Route::resource('categories', CategoryController::class)->only([
        'index', 'edit', 'update', 'create', 'store', 'destroy',
    ]);

    Route::controller(UserController::class)->group(function () {

        Route::put('users/{user}/pin', 'updatePassword')->name('users.update-password')->withTrashed();
        Route::put('users/{user}/roles', 'updateRoles')->name('users.update-roles')->withTrashed();
        Route::delete('users/{user}/pin', 'removePin')->name('users.remove-pin')->withTrashed();
        Route::post('users/{user}/restore', 'restore')->name('users.restore')->withTrashed();

        Route::resource('users', UserController::class)->only([
            'index', 'edit', 'update', 'destroy', 'create', 'store',
        ])->withTrashed();

    });

    Route::resource('sounds', SoundController::class)->only([
        'index', 'create', 'store', 'destroy',
    ]);

    Route::put('system-sounds', [SoundController::class, 'updateSystemSounds'])->name('sounds.update-system-sounds');
});
