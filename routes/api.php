<?php

use App\Http\Controllers\Api\DashboardProductController;
use App\Http\Controllers\Api\DashboardUtamaController;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TopSellingController;
use App\Http\Controllers\Api\TransactionsController;
use App\Http\Controllers\Api\UsersController ;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('dashboard')->controller(DashboardUtamaController::class)->group(function () {
        Route::get('kpi', 'kpi');
        Route::get('revenue-sales', 'revenueVsSales');
        Route::get('sales-channel', 'salesByChannel');
        Route::get('top-products', 'topSellingProducts');
    });
    Route::prefix('dashboard-product')->controller(DashboardProductController::class)->group(function () {
        Route::get('top-products', 'topSellingProducts');
        Route::get('channel-overview', 'channelOverview');
        Route::get('sessions-overview', 'sessionsOverview');
    });
    Route::apiResource('users', UsersController::class);
    Route::apiResource('products', ProductsController::class);
    Route::apiResource('transactions', TransactionsController::class);
    Route::post('/transactions/import', [TransactionsController::class, 'import']);
    Route::get('/top-selling', [TopSellingController::class, 'topSelling']);
    Route::prefix('profile')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'show');
        Route::put('/', 'update');
    });
});
