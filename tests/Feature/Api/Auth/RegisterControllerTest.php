<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

it('should register a user using email and password', function () {
    Notification::fake();
    Queue::fake();

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

    Notification::assertSentTo([User::find($response->json('data.id'))], VerifyEmailNotification::class);
});

it('should return an error if the email is already taken', function () {
    $model = new User();

    $email = fake()->safeEmail();

    User::factory()->create(['email' => $email]);

    $password = 'P@ssw0rd123';

    $payload = [
        'name' => fake()->name(),
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
    ];

    $response = $this->postJson(route('api.auth.register'), $payload);

    $response
        ->assertStatus(422)
        ->assertJsonFragment([
            'errors' => [
                'email' => [
                    'The email has already been taken.',
                ],
            ],
        ]);

    $this->assertDatabaseMissing($model->getTable(), [
        'name' => $payload['name'],
        'email' => $payload['email'],
    ]);

    $this->assertDatabaseCount($model->getTable(), 1);
});

it('should return an error if the password is not confirmed', function () {
    $model = new User();

    $password = 'P@ssw0rd123';

    $payload = [
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'password' => $password,
        'password_confirmation' => 'wrong_password',
    ];

    $response = $this->postJson(route('api.auth.register'), $payload);

    $response
        ->assertStatus(422)
        ->assertJsonFragment([
            'errors' => [
                'password' => [
                    'The password field confirmation does not match.',
                ],
                'password_confirmation' => [
                    'The password confirmation field must match password.',
                ],
            ],
        ]);

    $this->assertDatabaseMissing($model->getTable(), [
        'name' => $payload['name'],
        'email' => $payload['email'],
    ]);

    $this->assertDatabaseCount($model->getTable(), 0);
});
