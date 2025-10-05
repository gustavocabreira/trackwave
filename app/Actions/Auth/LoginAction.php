<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

final class LoginAction
{
    public function execute($request): array
    {
        $providerUser = Socialite::driver($request->provider)->stateless()->user();

        $user = User::where('email', $providerUser->email)->first();

        if (! $user) {
            $user = User::query()->updateOrCreate([
                'google_id' => $providerUser->id,
            ], [
                'name' => $providerUser->name,
                'email' => $providerUser->email,
                'google_id' => $providerUser->id,
                'email_verified_at' => now(),
            ]);
        } else {
            $user->update(['google_id' => $providerUser->id]);
        }

        Auth::login($user);

        return [
            'access_token' => $user->createToken('auth_token')->plainTextToken,
        ];
    }
}
