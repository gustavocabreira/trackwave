<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyEmailRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

final class VerifyEmailController extends Controller
{
    public function __invoke(VerifyEmailRequest $request)
    {
        $validated = $request->validated();

        try {
            $decryptedToken = decrypt($validated['token']);
        } catch (Exception $e) {
            return response([
                'message' => 'The token is invalid.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        [$userId, $expiresAt] = explode('|', $decryptedToken);

        $user = User::findOrFail($userId);

        abort_if($user->verificationToken->token !== $validated['token'], Response::HTTP_UNAUTHORIZED, 'The token is invalid.');

        abort_if(! is_null($user->verificationToken->used_at), Response::HTTP_UNAUTHORIZED, 'The token has already been used.');

        abort_if($user->email_verified_at, Response::HTTP_UNAUTHORIZED, 'The email has already been verified.');

        abort_if($user->verificationToken->token !== $validated['token'], Response::HTTP_UNAUTHORIZED, 'The token is invalid.');

        abort_if($user->verificationToken->expires_at->isPast(), Response::HTTP_UNPROCESSABLE_ENTITY, 'The token has expired.');

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
