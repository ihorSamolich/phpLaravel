<?php

use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\SenderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categories', [CategoriesController::class, 'getList']);
Route::post('/categories/create', [CategoriesController::class, 'create']);

Route::get('/categories/{id}', [CategoriesController::class, 'show']);

Route::post('/categories/edit/{id}', [CategoriesController::class, 'edit']);
Route::delete('/categories/{id}', [CategoriesController::class, 'delete']);

Route::post('/send/email', [SenderController::class, 'send_email']);
