<?php

use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\UsersController ;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UsersController::class);
    Route::apiResource('products', ProductsController::class);
});
