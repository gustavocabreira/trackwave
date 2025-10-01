<?php

use App\Actions\Auth\GenerateUserVerificationTokenAction;
use App\Models\EmailVerificationToken;
use App\Models\User;

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
        ->assertStatus(401)
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
        ->assertStatus(401)
        ->assertJsonFragment([
            'message' => 'The token has expired.',
        ]);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addHour(1),
        'used_at' => null,
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);
});
