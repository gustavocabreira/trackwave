<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\OauthProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

final class LoginAction
{
    public function execute($request): array
    {
        $provider = OauthProvider::where('name', $request->provider)->first();
        $providerUser = Socialite::driver($request->provider)->stateless()->user();

        $user = User::updateOrCreate([
            'email' => $providerUser->email,
        ], [
            'name' => $providerUser->name,
            'email_verified_at' => now(),
        ]);

        $user->socialAccounts()->updateOrCreate([
            'provider_id' => $provider->id,
            'provider_user_id' => $providerUser->id,
        ], [
            'access_token' => $providerUser->token,
            'refresh_token' => $providerUser->refreshToken,
            'token_expires_at' => now()->addSeconds($providerUser->expiresIn),
            'scopes' => implode(',', $providerUser->approvedScopes ?? []),
            'linked_at' => now(),
        ]);

        Auth::login($user);

        return [
            'access_token' => $user->createToken('auth_token')->plainTextToken,
        ];
    }
}
