<?php

declare(strict_types=1);

use App\Actions\Auth\GenerateUserVerificationTokenAction;
use App\Models\EmailVerificationToken;
use App\Models\User;

beforeEach(function () {
    $this->freezeTime();
});

it('should generate a token', function () {
    $user = User::factory()->create();

    $token = GenerateUserVerificationTokenAction::execute($user);

    expect($token)->toBeString()->and(decrypt($token))->toBeString();

    $decriptedToken = explode('|', decrypt($token));

    expect($decriptedToken)->toHaveCount(2);

    expect((int) $decriptedToken[0])
        ->toBe($user->id)
        ->and($decriptedToken[1])
        ->toBe(now()->toDateTimeString());

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addHour(1),
        'used_at' => null,
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);
});

it('should generate a new token if the old one is expired', function () {
    $user = User::factory()->create();

    $token = GenerateUserVerificationTokenAction::execute($user);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addHour(1),
        'used_at' => null,
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);

    $token = GenerateUserVerificationTokenAction::execute($user);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addHour(1),
        'used_at' => null,
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);
});

it('should delete the old token if a new one is generated', function () {
    $user = User::factory()->create();

    EmailVerificationToken::create([
        'user_id' => $user->id,
        'token' => 'Teste',
        'expires_at' => now()->subHour(1),
        'used_at' => null,
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);

    $token = GenerateUserVerificationTokenAction::execute($user);

    $this->assertDatabaseHas('email_verification_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addHour(1),
        'used_at' => null,
    ]);

    $this->assertDatabaseCount('email_verification_tokens', 1);
});
