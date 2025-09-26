<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [RegisterController::class, 'store'])->name('register');
        Route::post('login', [LoginController::class, 'store'])->name('login');
        Route::post('logout', LogoutController::class)->name('logout');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', fn () => request()->user())->name('me');
        Route::post('logout', LogoutController::class)->name('api.auth.logout');
    });
});
