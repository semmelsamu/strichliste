<?php

use App\Http\Controllers\AuthController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('start');
});

Route::get('/article-list', function () {
    return view('article-list', [
        'categories' => Category::with('articles')->get(),
    ]);
});

Route::get('/login', [AuthController::class, 'showUsers']);
Route::get('/login/{userId}', [AuthController::class, 'loginAs']);
Route::get('/logout', [AuthController::class, 'logout']);

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
