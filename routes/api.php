<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\RefreshVerificationTokenController;
use App\Http\Controllers\Api\VerifyEmailController;
use App\Http\Middleware\VerifiedEmailMiddleware;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [RegisterController::class, 'store'])->name('register');
        Route::post('login', [LoginController::class, 'store'])->name('login');
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::middleware(VerifiedEmailMiddleware::class)
            ->group(function () {
                Route::get('me', fn () => request()->user())->name('me');
            });

        Route::post('auth/logout', LogoutController::class)->name('api.auth.logout');
    });

    Route::post('user/verify-email', VerifyEmailController::class)->name('user.verify-email');
    Route::post('user/refresh-verification-token', RefreshVerificationTokenController::class)->name('user.refresh-verification-token');
});
