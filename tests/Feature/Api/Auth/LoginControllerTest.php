<?php

declare(strict_types=1);

use App\Models\User;

it('should login a user using email and password', function () {
    $model = new User();
    $user = $model->factory()->create(['password' => 'P@ssw0rd123']);

    $payload = [
        'email' => $user->email,
        'password' => 'P@ssw0rd123',
    ];

    $response = $this->postJson(route('api.auth.login'), $payload);

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'access_token',
            ],
        ]);
});

it('should return an error if the email or password is incorrect', function () {
    $email = fake()->safeEmail();

    $payload = [
        'email' => $email,
        'password' => 'wrong_password',
    ];

    $response = $this->postJson(route('api.auth.login'), $payload);

    $response
        ->assertUnauthorized()
        ->assertJsonFragment([
            'errors' => [
                'email' => [
                    'Authentication failed. Please check your credentials and try again.',
                ],
            ],
        ]);
});
