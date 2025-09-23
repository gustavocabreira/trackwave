<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Response;

final class LoginController extends Controller
{
    public function store(LoginRequest $request)
    {
        if (! auth()->attempt($request->validated())) {
            $errorResponse = [
                'errors' => [
                    'email' => [
                        'Authentication failed. Please check your credentials and try again.',
                    ],
                ],
            ];

            return response()->json($errorResponse, Response::HTTP_UNAUTHORIZED);
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $token = $user->createToken('auth_token');

        return response()->json([
            'data' => [
                'access_token' => $token->plainTextToken,
            ],
        ], Response::HTTP_OK);
    }
}
