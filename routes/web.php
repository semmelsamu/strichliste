<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('start');
});


Route::get('/preisliste', function () {
    return view('preisliste');
});
