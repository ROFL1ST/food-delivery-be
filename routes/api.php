<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// user register
Route::post('/user/register', [\App\Http\Controllers\Api\AuthController::class, 'userRegister']);

// restaurant register
Route::post('/restaurant/register', [\App\Http\Controllers\Api\AuthController::class, 'registerRestaurant']);

// driver register
Route::post('/driver/register', [\App\Http\Controllers\Api\AuthController::class, 'registerDriver']);

// login
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

// logout
Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');

// update latlong
Route::put('/user/update/latlong', [\App\Http\Controllers\Api\AuthController::class, 'updateLatLong'])->middleware('auth:sanctum');

// get all restaurants
Route::get('/restaurants', [\App\Http\Controllers\Api\AuthController::class, 'getAllRestaurants']);

Route::get('/producst', [\App\Http\Controllers\Api\ProductController::class, 'index'])->middleware('auth:sanctum');

// create product
Route::post('/products', [\App\Http\Controllers\Api\ProductController::class, 'store'])->middleware('auth:sanctum');

// delete product
Route::delete('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'destroy'])->middleware('auth:sanctum');

// update product
Route::post('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'update'])->middleware('auth:sanctum');

// order
Route::post('/order', [\App\Http\Controllers\Api\OrderController::class, 'createOrder'])->middleware('auth:sanctum');

// update purchase status
Route::put('/order/{id}', [\App\Http\Controllers\Api\OrderController::class, 'updatePurchaseStatus'])->middleware('auth:sanctum');

// get order by user id
Route::get('/order/user', [\App\Http\Controllers\Api\OrderController::class, 'orderHistory'])->middleware('auth:sanctum');

// get order by restaurant id
Route::get('/order/restaurant', [\App\Http\Controllers\Api\OrderController::class, 'getOrderByStatus'])->middleware('auth:sanctum');

// get order by driver id
Route::get('/order/driver', [App\Http\Controllers\Api\OrderController::class, 'getOrdersByStatusDriver'])->middleware('auth:sanctum');

// update order status
Route::put('/order/restaurant/update-status/{id}', [App\Http\Controllers\Api\OrderController::class, 'updateOrderStatus'])->middleware('auth:sanctum');

// update order status for driver
Route::put('/order/driver/update-status/{id}', [App\Http\Controllers\Api\OrderController::class, 'updateOrderStatusDriver'])->middleware('auth:sanctum');

//update purchase status
Route::put('/order/user/update-status/{id}', [App\Http\Controllers\Api\OrderController::class, 'updatePurchaseStatus'])->middleware('auth:sanctum');



