<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleRegisterController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                $user = User::where('email', $googleUser->getEmail())->first();

                if (!$user) {
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId()
                    ]);
                } else {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }

            return view('auth.callback', [
                'token' => $user->createToken('auth_token')->plainTextToken;
            ]);

        } catch (\Throwable $t) {
            dd('Something went wrong! ' . $t->getMessage());
        }
    }
}
