<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class GenerateUserVerificationTokenAction
{
    public static function execute(User $user): string
    {
        $latest = $user->latestVerificationToken ?? $user->verificationToken ?? null;

        if ($latest && is_null($latest->used_at) && $latest->expires_at->isFuture()) {
            return $latest->token;
        }

        $plaintext = implode('|', [
            $user->id,
            now()->toDateTimeString(),
        ]);

        $encryptedToken = encrypt($plaintext);

        DB::transaction(function () use ($user, $encryptedToken) {
            $user->verificationTokens()->delete();
            $user->verificationTokens()->create([
                'token' => $encryptedToken,
                'expires_at' => now()->addHour(1),
            ]);
        });

        return $encryptedToken;
    }
}
