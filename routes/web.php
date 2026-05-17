<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('start');
});

Route::get('/article-list', function () {
    return view('article-list', [
        'categories' => Category::with('articles')->get(),
    ]);
});

Route::get('/login', function () {
    return view('login', [
        'usersByLetter' => User::all()
            ->groupBy(function ($user) {
                $first = strtoupper(substr($user->name, 0, 1));

                return ctype_alpha($first) ? $first : '*';
            })
            ->sortKeys()
            ->pipe(function ($col) {
                // Move '*' to the end if present
                if ($col->has('*')) {
                    $star = $col->pull('*');
                    $col->put('*', $star);
                }

                return $col;
            }),
    ]);
});

Route::get('/buy', function () {
    return view('buy-overview', [
        'categories' => Category::all(),
    ]);
});

Route::get('/buy/category/{category_id}', function ($category_id) {
    return view('buy-category', [
        'category' => Category::with('articles')->firstWhere('id', $category_id),
    ]);
});

Route::get('/deposit', function () {
    return view('deposit');
});
