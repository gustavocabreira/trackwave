<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            $decryptedToken = decrypt($validated['token']);
        } catch(\Exception $e) {
            return response([
                'message' => 'The token is invalid.',
            ], 401);
        }

        [$userId, $expiresAt] = explode('|', $decryptedToken);

        abort_if(now()->toDateTimeString() > $expiresAt, 401, 'The token has expired.');
        
        $user = User::findOrFail($userId);

        abort_if($user->email_verified_at, 401, 'The email has already been verified.');

        abort_if($user->verificationToken->token !== $validated['token'], 401, 'The token is invalid.');

        abort_if($user->verificationToken->expires_at->isPast(), 401, 'The token has expired.');

        DB::transaction(function () use ($user) {
            $user->verificationToken()->update([
                'used_at' => now(),
            ]);

            $user->update([
                'email_verified_at' => now(),
            ]);
        });

        return response([
            'message' => 'Email verified successfully.',
        ]);
    }
}
