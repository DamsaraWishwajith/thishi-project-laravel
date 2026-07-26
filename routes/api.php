<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\WaterRecordController;
use App\Http\Controllers\Api\PaymentSlipController;
use App\Http\Controllers\Api\MeterStatusController;

// Auth routes (public)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
});

// Public bill routes (no authentication required)
Route::get('/bill/{nic}', [BillController::class, 'show']);
Route::post('/bill', [BillController::class, 'store']);
Route::put('/bill/{nic}', [BillController::class, 'update']);

// Water records routes (public or protected as needed, keeping them public like bills for simple integration)
Route::get('/water-records/{nic}', [WaterRecordController::class, 'index']);
Route::get('/water-records/{nic}/date/{date}', [WaterRecordController::class, 'showByDate']);
Route::get('/water-records/{nic}/summary', [WaterRecordController::class, 'summary']);
Route::get('/water-records/{nic}/predict', [WaterRecordController::class, 'predict']);
Route::post('/water-records', [WaterRecordController::class, 'store']);

// Payment slip upload endpoints
Route::post('/payment-slips/upload', [PaymentSlipController::class, 'upload']);
Route::get('/payment-slips/{nic}', [PaymentSlipController::class, 'index']);

// FCM Token storage endpoint
Route::post('/users/fcm-token', [App\Http\Controllers\Api\FcmTokenController::class, 'updateToken']);

// Meter status endpoints
Route::post('/meter/status', [MeterStatusController::class, 'checkOrUpdateStatus']);
Route::get('/meter/status/{nic}', [MeterStatusController::class, 'showStatus']);


