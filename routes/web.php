<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BiometryController;
use App\Http\Controllers\AwsController;

Route::get('/', function () {
    return redirect()->away('https://www.bluefoxmarina.com.br/', 301);
});

Route::get('/biometry/{uuid}', [BiometryController::class, 'wizard']);
Route::post('/locked', [BiometryController::class, 'lock']);
Route::get('/configs', [BiometryController::class, 'getConfigs']);

Route::middleware('verify.guest.token')->group(function () {
    Route::post('/upload-image', [AwsController::class, 'upload']);
    Route::post('/photo-analysis', [AwsController::class, 'photoAnalysis']);
    Route::get('/search-photo', [AwsController::class, 'getPhoto']);
    Route::get('/analysis-data', [AwsController::class, 'getRekognitionAnalysis']);
});