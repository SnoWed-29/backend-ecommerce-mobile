<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\cartController;
use App\Http\Controllers\categoryController;
use App\Http\Controllers\orderController;
use App\Http\Controllers\productController;

Route::get('/user', function (Request $request) {
    return $request->user();

})->middleware('auth:sanctum');
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::post('/products', [ProductController::class, 'storeProduct']);
    Route::get('/products-category/{category_id}', [CategoryController::class, 'getCategoryProducts']);
    Route::get('/categories', [CategoryController::class, 'getCategories']);
    Route::get('/product/{product_id}', [ProductController::class, 'getProduct']);
    Route::post('/added-tocart', [CartController::class, 'addedToCart']);
    Route::get('/cart/{user_id}', [CartController::class, 'getCartItems']);
    Route::delete('/cart/{cart_id}/product/{product_id}', [cartController::class, 'deleteProductFromCart']);
    Route::post('/orders', [orderController::class , 'createOrder']);
});
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
