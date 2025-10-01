<?php

declare(strict_types=1);

use App\Actions\Auth\GenerateUserVerificationTokenAction;
use App\Models\EmailVerificationToken;
use App\Models\User;

beforeEach(function () {
    $this->freezeTime();
});

it('should verify the email', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $token = GenerateUserVerificationTokenAction::execute($user);

    $response = $this->postJson(route('api.user.verify-email'), [
        'token' => $token,
    ]);

    $response
        ->assertOk()
        ->assertJsonFragment([
            'message' => 'Email verified successfully.',
        ]);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addHour(1),
        'used_at' => now(),
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email_verified_at' => now(),
    ]);
});

it('should return an error if the token is invalid', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $token = GenerateUserVerificationTokenAction::execute($user);

    $response = $this->postJson(route('api.user.verify-email'), [
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

it('should return an error if the token has expired', function () {
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

    $response = $this->postJson(route('api.user.verify-email'), [
        'token' => $token,
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonFragment([
            'message' => 'The token has expired.',
        ]);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
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

    $response = $this->postJson(route('api.user.verify-email'), [
        'token' => $token,
    ]);

    $response
        ->assertUnauthorized()
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

    $response = $this->postJson(route('api.user.verify-email'), [
        'token' => $token,
    ]);

    $response
        ->assertUnauthorized()
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
