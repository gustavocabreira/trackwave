<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

final class LogoutController extends Controller
{
    public function __invoke(): Response
    {
        request()->user()->tokens()->delete();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->noContent();
    }
}
