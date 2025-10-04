<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

it('should update the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    $payload = Organization::factory()->make(['owner_id' => $user->id])->toArray();

    $response = $this->actingAs($user)->putJson(route('api.organizations.update', [
        'organization' => $organization->id,
    ]), $payload);

    $response
        ->assertOk()
        ->assertJsonFragment([
            'data' => [
                'id' => $organization->id,
                'name' => $payload['name'],
                'slug' => $payload['slug'],
            ],
        ]);

    $this->assertDatabaseHas('organizations', [
        'name' => $payload['name'],
        'slug' => $payload['slug'],
        'owner_id' => $user->id,
    ]);

    $this->assertDatabaseCount('organizations', 1);
});

dataset('invalid_payload', [
    'null name' => [
        ['name' => null], ['name' => ['The name field must be a string.', 'The name field must be at least 3 characters.']],
    ],
    'invalid name' => [
        ['name' => ''], ['name' => ['The name field must be a string.', 'The name field must be at least 3 characters.']],
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
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    $response = $this->actingAs($user)->putJson(route('api.organizations.update', [
        'organization' => $organization->id,
    ]), $payload);

    $response
        ->assertUnprocessable()
        ->assertJsonFragment([
            'errors' => $expectedErrors,
        ]);
})->with('invalid_payload');

it('should return unprocessable entity when slug is already taken', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id, 'slug' => 'slug']);
    $user->organizations()->attach($organization);

    $payload = Organization::factory()->make(['owner_id' => $user->id, 'slug' => $organization->slug])->toArray();

    $response = $this->actingAs($user)->putJson(route('api.organizations.update', [
        'organization' => $organization->id,
    ]), $payload);

    $response
        ->assertUnprocessable()
        ->assertJsonFragment([
            'errors' => [
                'slug' => ['The slug has already been taken.'],
            ],
        ]);
});

it('should return not found when trying to access a non-existing organization', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson(route('api.organizations.update', [
        'organization' => str()->random(),
    ]), []);

    $response->assertNotFound();
});

it('should return forbidden when trying to update another user organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    $response = $this->actingAs($user)->putJson(route('api.organizations.update', [
        'organization' => $organization->id,
    ]), []);

    $response
        ->assertForbidden()
        ->assertJsonFragment([
            'message' => 'This action is unauthorized.',
        ]);
});

it('should return forbidden if the user is not the owner of the organization', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);
    $anotherUser->organizations()->attach($organization);

    $response = $this->actingAs($anotherUser)->putJson(route('api.organizations.update', [
        'organization' => $organization->id,
    ]), []);

    $response
        ->assertForbidden()
        ->assertJsonFragment([
            'message' => 'This action is unauthorized.',
        ]);
});
