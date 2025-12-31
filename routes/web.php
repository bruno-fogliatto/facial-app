<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BiometryController;
use App\Http\Controllers\AwsController;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->away('https://www.bluefoxmarina.com.br/', 301);
});

Route::get('/biometry/{step?}', function ($step = 1) {
    return Inertia::render('Layouts/Main', [
        'initialStep' => (int) $step
    ]);
})->where('step', '[0-9]+');

Route::get('/biometry/{uuid}', [BiometryController::class, 'wizard']);
Route::get('/configs-images', [BiometryController::class, 'getImages']);

Route::middleware('verify.guest.token')->group(function () {
    Route::post('/validate-step', [BiometryController::class, 'syncStep']);
    Route::post('/locked', [BiometryController::class, 'lock']);

    Route::post('/upload-image', [AwsController::class, 'upload']);
});