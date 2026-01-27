<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/incubator-routine', function () {
    return view('incubator-routine');
});