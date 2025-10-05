<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SocialLoginAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

final class SocialRegisterController extends Controller
{
    public function redirectToProvider($provider): RedirectResponse
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(Request $request, SocialLoginAction $action): RedirectResponse
    {
        $accessToken = $action->execute($request);
        $redirectUrl = sprintf('%s/auth/callback?token=%s', config('app.frontend_url'), $accessToken['access_token']);

        return response()->redirectTo($redirectUrl);
    }
}
