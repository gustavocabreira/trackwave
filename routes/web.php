<?php

use App\Http\Controllers\Api\Auth\SocialRegisterController;
use Illuminate\Support\Facades\Route;

Route::name('api.')
    ->prefix('api')
    ->group(function () {  
        Route::prefix('auth/{provider}')
        ->name('auth.')
        ->whereIn('provider', ['google'])
        ->group(function() {
            Route::get('redirect', [SocialRegisterController::class, 'redirectToProvider'])->name('redirect');
            Route::get('callback', [SocialRegisterController::class, 'callback'])->name('callback');
        });
});