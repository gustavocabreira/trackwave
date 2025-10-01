<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;

final class RegisterController extends Controller
{
    public function store(RegisterRequest $request)
    {
        $request->validated();

        $user = User::query()->create($request->only('name', 'email', 'password'));

        $user->notify(new VerifyEmailNotification);

        return $user->toResource();
    }
}
