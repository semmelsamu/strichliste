<?php

use App\Models\Category;
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

Route::name('strichliste.')->prefix('strichliste')->group(function () {

    Route::get('/', function () {
        return view('login', [
            'usersByLetter' => User::groupByFirstLetter(User::all()),
        ]);
    })->name('login');

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
                'transactions' => $user->transactions()->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get(),
                'user' => $user,
            ]);
        })->name('history');
    });
});
