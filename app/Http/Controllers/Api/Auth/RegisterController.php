<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\GenerateUserVerificationTokenAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Auth;

final class RegisterController extends Controller
{
    public function store(RegisterRequest $request)
    {
        $request->validated();

        $user = User::query()->create($request->only('name', 'email', 'password'));

        GenerateUserVerificationTokenAction::execute($user);

        $user->notify(new VerifyEmailNotification);

        Auth::login($user);
        auth()->user()->createToken('auth_token');

        return $user->toResource();
    }
}
