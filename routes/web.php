<?php

use App\Enums\UserType;
use App\Http\Controllers\TransactionController;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('start');
})->name('index');

Route::get('/article-list', function () {
    return view('article-list', [
        'categories' => Category::with('articles')->get(),
    ]);
})->name('article-list');

Route::name('tally-sheet.')->prefix('strichliste')->group(function () {

    Route::get('/', function () {
        return view('login', [
            'usersByLetter' => User::groupByFirstLetter(
                User::where('type', UserType::NormalUser)->get()
            ),
        ]);
    })->name('login');

    Route::get('/register', function () {
        return view('register');
    })->name('register');

    Route::post('/deposit', [TransactionController::class, 'depositMoney'])->name('deposit-action');

    Route::post('/buy-article', [TransactionController::class, 'buyArticle'])->name('buy-article-action');

    Route::prefix('{user}')->group(function () {

        Route::get('/buy', function (User $user) {
            return view('buy-overview', [
                'categories' => Category::all(),
                'user' => $user,
            ]);
        })->name('buy-overview');

        Route::get('/buy/category/{category_id}', function (User $user, $category_id) {
            return view('buy-category', [
                'category' => Category::with('articles')->firstWhere('id', $category_id),
                'user' => $user,
            ]);
        })->name('buy-categories');

        Route::get('/deposit', function (User $user) {
            return view('deposit', [
                'user' => $user,
            ]);
        })->name('deposit');

        Route::get('/history', function (User $user) {
            return view('history', [
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
