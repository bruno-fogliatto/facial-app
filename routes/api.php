<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('verify.api.token')->group(function () {
    Route::post('/auth/token', [AuthController::class, 'createToken']);
    Route::put('/auth/token/revoke', [AuthController::class, 'revokeToken']);
});

Route::middleware('verify.guest.token')->group(function () {
    Route::get('/biometry-link', [AuthController::class, 'getBiometryLink']);
});