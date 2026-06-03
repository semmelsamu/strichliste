<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TallySheet;
use App\Http\Controllers\UserController;
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
        return view('pages.tally-sheet.article-list', [
            'categories' => Category::with('articles')->get(),
        ]);
    })->name('article-list');

    Route::name('tally-sheet.')->prefix('tally-sheet')->group(function () {

        Route::name('auth.')->controller(TallySheet\LoginController::class)->group(function () {
            Route::get('/', 'listUsers')->name('list-users');
            Route::get('/login/{user}', 'login')->name('login');
            Route::post('/login/{user}', 'validatePin')->name('validate-pin');
        });

        Route::name('auth.')->controller(AuthController::class)->group(function () {

            Route::post('/user-settings/{user}/pin', 'updatePin')->name('update-pin');
            Route::post('/user-settings/{user}/remove-pin', 'removePin')->name('remove-pin');

            Route::get('/register', 'registerForm')->name('show-register');
            Route::post('/register', 'register')->name('register');
            Route::get('/user-settings/{user}', 'settings')->name('show-settings');
            Route::post('/user-settings/{user}/username', 'updateUsername')->name('update-username');
            Route::delete('/user-settings/{user}/deactivate', 'deactivate')->name('deactivate')->withTrashed();

        });

        Route::prefix('{user}')->controller(TallySheet\ViewController::class)->group(function () {

            Route::get('/buy', 'showBuyOverview')->name('buy-overview');
            Route::get('/buy/category/{category_id}', 'showBuyCategory')->name('buy-categories');

            Route::get('/deposit', 'showDeposit')->name('deposit');

            Route::get('/history', 'showHistory')->name('history');

        });

        Route::prefix('{user}')->controller(TallySheet\TransactionController::class)->group(function () {

            Route::post('/deposit', 'depositMoney')->name('deposit');

            Route::post('/buy', 'buyArticle')->name('buy');

            Route::post('/undo', 'undoTransaction')->name('undo');

        });
    });

    Route::controller(ArticleController::class)->group(function () {

        Route::post('articles/{article}/restore', 'restore')->name('articles.restore')->withTrashed();

        Route::post('articles/{article}/price', 'updatePrice')->name('articles.update-price');

        Route::resource('articles', ArticleController::class)->only([
            'index', 'edit', 'update', 'create', 'store', 'destroy',
        ]);

    });

    Route::resource('categories', CategoryController::class)->only([
        'index', 'edit', 'update', 'create', 'store', 'destroy',
    ]);

    Route::controller(UserController::class)->group(function () {

        Route::put('users/{user}/pin', 'updatePassword')->name('users.update-password')->withTrashed();
        Route::delete('users/{user}/pin', 'removePin')->name('users.remove-pin')->withTrashed();
        Route::post('users/{user}/restore', 'restore')->name('users.restore')->withTrashed();

        Route::resource('users', UserController::class)->only([
            'index', 'edit', 'update', 'destroy', 'create', 'store',
        ])->withTrashed();

    });

});
