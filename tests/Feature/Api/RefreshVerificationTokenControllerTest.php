<?php

declare(strict_types=1);

use App\Actions\Auth\GenerateUserVerificationTokenAction;
use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->freezeTime();
});

it('should refresh the token', function () {
    Notification::fake();
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $token = encrypt(implode('|', [
        $user->id,
        now()->subHour(1),
    ]));

    EmailVerificationToken::create([
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->subHour(1),
        'used_at' => null,
    ]);

    $response = $this->postJson(route('api.user.refresh-verification-token'), [
        'token' => $token,
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'message',
            ],
        ])
        ->assertJsonFragment([
            'message' => 'Token refreshed successfully.',
        ]);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'used_at' => null,
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);

    Notification::assertSentTo([User::find($user->id)], VerifyEmailNotification::class);
});

it('should return an error if the token is invalid', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $token = GenerateUserVerificationTokenAction::execute($user);

    $response = $this->postJson(route('api.user.refresh-verification-token'), [
        'token' => 'invalid_token',
    ]);

    $response
        ->assertUnauthorized()
        ->assertJsonFragment([
            'message' => 'The token is invalid.',
        ]);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addHour(1),
        'used_at' => null,
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);
});

it('should return an error if the token has already been used', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $token = encrypt(implode('|', [
        $user->id,
        now(),
    ]));

    EmailVerificationToken::create([
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now(),
        'used_at' => now(),
    ]);

    $response = $this->postJson(route('api.user.refresh-verification-token'), [
        'token' => $token,
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonFragment([
            'message' => 'The token has already been used.',
        ]);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'used_at' => now(),
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);
});

it('should return an error if the email has already been verified', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $token = GenerateUserVerificationTokenAction::execute($user);

    $response = $this->postJson(route('api.user.refresh-verification-token'), [
        'token' => $token,
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonFragment([
            'message' => 'The email has already been verified.',
        ]);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addHour(1),
        'used_at' => null,
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);
});

it('should return an error if the token has not expired yet', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $token = encrypt(implode('|', [
        $user->id,
        now(),
    ]));

    EmailVerificationToken::create([
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addHour(1),
        'used_at' => null,
    ]);

    $response = $this->postJson(route('api.user.refresh-verification-token'), [
        'token' => $token,
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonFragment([
            'message' => 'The token has not expired yet.',
        ]);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addHour(1),
        'used_at' => null,
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);
});
