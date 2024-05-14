<?php

use Illuminate\Support\Facades\Route;


Route::get('/login', function () {
    return response()->json(['error' => 'Unauthorized'], 401);
})->name('login');