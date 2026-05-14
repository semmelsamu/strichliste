<?php

use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('start');
});

Route::get('/preisliste', function () {
    return view('preisliste', [
        'categories' => Category::with('products')->get(),
    ]);
});
