<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

it('should create a new organization', function () {
    $user = User::factory()->create();

    $payload = Organization::factory()->make(['owner_id' => $user->id])->toArray();

    $response = $this->actingAs($user)->postJson(route('api.organizations.store'), $payload);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
            ],
        ]);

    $this->assertDatabaseHas('organizations', [
        'name' => $payload['name'],
        'slug' => $payload['slug'],
        'owner_id' => $user->id,
    ]);

    $this->assertDatabaseCount('organizations', 1);

    $this->assertDatabaseHas('organization_user', [
        'organization_id' => $response->json('data.id'),
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseCount('organization_user', 1);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'organization_id' => $response->json('data.id'),
    ]);
});

dataset('invalid_payload', [
    'invalid name' => [
        ['name' => ''], ['name' => ['The name field is required.']],
    ],
    'name with less than 3 characters' => [
        ['name' => str()->random(2)], ['name' => ['The name field must be at least 3 characters.']],
    ],
    'name with more than 50 characters' => [
        ['name' => str()->random(51)], ['name' => ['The name field must not be greater than 50 characters.']],
    ],
    'slug with more than 50 characters' => [
        ['slug' => str()->random(51)], ['slug' => ['The slug field must not be greater than 50 characters.']],
    ],
]);

it('should return unprocessable entity when payload is invalid', function (array $payload, array $expectedErrors) {
    $user = User::factory()->create();
    $payload = Organization::factory()->make(['owner_id' => $user->id, ...$payload])->toArray();

    $response = $this->actingAs($user)->postJson(route('api.organizations.store'), $payload);

    $response
        ->assertUnprocessable()
        ->assertJsonFragment([
            'errors' => $expectedErrors,
        ]);
})->with('invalid_payload');

it('should return unprocessable entity when slug is already taken', function () {
    $user = User::factory()->create();
    $payload = Organization::factory()->make(['slug' => 'slug'])->toArray();

    Organization::factory()->create(['owner_id' => $user->id, 'slug' => $payload['slug']]);

    $response = $this->actingAs($user)->postJson(route('api.organizations.store'), $payload);

    $response
        ->assertUnprocessable()
        ->assertJsonFragment([
            'errors' => [
                'slug' => ['The slug has already been taken.'],
            ],
        ]);
});

it('should generate a slug if not provided', function () {
    $user = User::factory()->create();
    $payload = Organization::factory()->make(['owner_id' => $user->id, 'slug' => null])->toArray();

    $response = $this->actingAs($user)->postJson(route('api.organizations.store'), $payload);

    $response->assertCreated();

    expect(Organization::find($response->json('data.id'))->slug)->not->toBeNull();

    $this->assertDatabaseHas('organizations', [
        'name' => $payload['name'],
        'slug' => $response->json('data.slug'),
        'owner_id' => $user->id,
    ]);

    $this->assertDatabaseCount('organizations', 1);
});
