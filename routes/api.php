<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\SenderController;
use App\Http\Controllers\Api\SpecialOffersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/verify/{id}', 'App\Http\Controllers\Auth\VerificationController@verify')->name('verification.verify');


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categories', [CategoriesController::class, 'getList']);
Route::get('/categories/names', [CategoriesController::class, 'getListCategoryNames']);

Route::post('/categories/create', [CategoriesController::class, 'create']);
Route::get('/categories/{id}', [CategoriesController::class, 'show']);
Route::post('/categories/edit/{id}', [CategoriesController::class, 'edit']);
Route::delete('/categories/{id}', [CategoriesController::class, 'delete']);

Route::post('/send/email', [SenderController::class, 'send_email']);

Route::get('/products/discounts', [ProductsController::class, 'getListDiscounts']);
Route::get('/products', [ProductsController::class, 'getList']);
Route::get('/products/{id}', [ProductsController::class, 'getByCategory']);
Route::post('/products/create', [ProductsController::class, 'create']);

Route::get('/specialOffers', [SpecialOffersController::class, 'getList']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/google', [AuthController::class, 'loginGoogle']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verification', [AuthController::class, 'verificationEmail']);

Route::get('/users', [AuthController::class, 'getList']);
