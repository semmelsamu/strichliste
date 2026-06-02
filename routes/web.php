<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TransactionController;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
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

    Route::name('tally-sheet.')->prefix('strichliste')->group(function () {

        Route::name('auth.')->controller(AuthController::class)->group(function () {
            Route::get('/', 'listUsers')->name('list-users');
            Route::get('/login/{user}', 'login')->name('login');
            Route::post('/login/{user}', 'validatePin')->name('validate-pin');
            Route::get('/register', 'registerForm')->name('show-register');
            Route::post('/register', 'register')->name('register');
            Route::get('/user-settings/{user}', 'settings')->name('show-settings');
            Route::post('/user-settings/{user}/username', 'updateUsername')->name('update-username');
            Route::post('/user-settings/{user}/pin', 'updatePin')->name('update-pin');
            Route::post('/user-settings/{user}/remove-pin', 'removePin')->name('remove-pin');
        });

        Route::name('transaction.')->controller(TransactionController::class)->group(function () {
            Route::post('/deposit', 'depositMoney')->name('deposit');
            Route::post('/buy', 'buyArticle')->name('buy');
            Route::post('/undo', 'undoTransaction')->name('undo');
        });

        Route::prefix('{user}')->group(function () {

            Route::get('/buy', function (User $user) {
                return view('pages.tally-sheet.buy-overview', [
                    'categories' => Category::all(),
                    'user' => $user,
                ]);
            })->name('buy-overview');

            Route::get('/buy/category/{category_id}', function (User $user, $category_id) {
                return view('pages.tally-sheet.buy-category', [
                    'category' => Category::with('articles')->firstWhere('id', $category_id),
                    'user' => $user,
                ]);
            })->name('buy-categories');

            Route::get('/deposit', function (User $user) {
                return view('pages.tally-sheet.deposit', [
                    'user' => $user,
                ]);
            })->name('deposit');

            Route::get('/history', function (User $user) {
                return view('pages.tally-sheet.history', [
                    'normalizedTransactions' => $user->transactions()
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->get()
                        ->map(fn ($t) => Transaction::normalize($t)),
                    'user' => $user,
                ]);
            })->name('history');
        });
    });

    Route::post('articles/{article}/price', [ArticleController::class, 'updatePrice'])->name('articles.update-price');

    Route::resource('articles', ArticleController::class)->only([
        'index', 'edit', 'update', 'create',
    ]);

});
