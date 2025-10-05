<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

it('should be able to store a project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    $payload = Project::factory()->make()->toArray();

    $response = $this
        ->actingAs($user)
        ->postJson(route('api.organizations.projects.store', [
            'organization' => $organization->id,
        ]), $payload);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'organization_id',
                'user_id',
                'name',
                'description',
            ],
        ]);

    $this->assertDatabaseHas('projects', [
        'id' => $response->json('data.id'),
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'name' => $payload['name'],
        'description' => $payload['description'],
    ]);

    $this->assertDatabaseCount('projects', 1);
});

dataset('invalid_payload', [
    'invalid name' => [
        ['name' => ''], ['name' => ['The name field is required.']],
    ],
    'name with less than 3 characters' => [
        ['name' => str()->random(2)], ['name' => ['The name field must be at least 3 characters.']],
    ],
    'name with more than 100 characters' => [
        ['name' => str()->random(101)], ['name' => ['The name field must not be greater than 100 characters.']],
    ],
    'description with less than 3 characters' => [
        ['description' => str()->random(2)], ['description' => ['The description field must be at least 3 characters.']],
    ],
    'description with more than 255 characters' => [
        ['description' => str()->random(256)], ['description' => ['The description field must not be greater than 255 characters.']],
    ],
]);

it('should return unprocessable entity when payload is invalid', function (array $payload, array $expectedErrors) {
    $user = User::factory()->create();
    $payload = Organization::factory()->make(['owner_id' => $user->id, ...$payload])->toArray();

    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization);

    $response = $this->actingAs($user)->postJson(route('api.organizations.projects.store', [
        'organization' => $organization->id,
    ]), $payload);

    $response
        ->assertUnprocessable()
        ->assertJsonFragment([
            'errors' => $expectedErrors,
        ]);
})->with('invalid_payload');

test('only the owner can create a project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization);

    $payload = Project::factory()->make()->toArray();

    $response = $this->actingAs($user)->postJson(route('api.organizations.projects.store', [
        'organization' => $organization->id,
    ]), $payload);

    $response->assertForbidden();
});
