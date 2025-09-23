<?php

declare(strict_types=1);

use App\Models\User;

it('should register a user using email and password', function () {
    $model = new User();

    $password = 'P@ssw0rd123';

    $payload = [
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'password' => $password,
        'password_confirmation' => $password,
    ];

    $response = $this->postJson(route('api.auth.register'), $payload);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);

    $this->assertDatabaseHas($model->getTable(), [
        'name' => $payload['name'],
        'email' => $payload['email'],
    ]);
});
