<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [RegisterController::class, 'store'])->name('register');
    });
});
