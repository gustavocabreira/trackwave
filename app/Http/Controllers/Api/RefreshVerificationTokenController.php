<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RefreshVerificationTokenRequest;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Exception;
use Illuminate\Http\Response;

final class RefreshVerificationTokenController extends Controller
{
    public function __invoke(RefreshVerificationTokenRequest $request)
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

        abort_if(! is_null($user->verificationToken->used_at), Response::HTTP_UNPROCESSABLE_ENTITY, 'The token has already been used.');

        abort_if($user->email_verified_at, Response::HTTP_UNPROCESSABLE_ENTITY, 'The email has already been verified.');

        abort_if($user->verificationToken->expires_at->isFuture(), Response::HTTP_UNPROCESSABLE_ENTITY, 'The token has not expired yet.');

        $user->notify(new VerifyEmailNotification);

        return response([
            'data' => [
                'message' => 'Token refreshed successfully.',
            ],
        ], Response::HTTP_OK);
    }
}
