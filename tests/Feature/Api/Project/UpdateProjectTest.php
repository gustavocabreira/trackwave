<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

it('should update the project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    $project = Project::factory()->create(['user_id' => $user->id, 'organization_id' => $organization->id]);

    $payload = Project::factory()->make(['user_id' => $user->id, 'organization_id' => $organization->id])->toArray();

    $response = $this->actingAs($user)->patchJson(route('api.projects.update', [
        'project' => $project->id,
    ]), $payload);

    $response
        ->assertOk()
        ->assertJsonFragment([
            'data' => [
                'id' => $project->id,
                'name' => $payload['name'],
                'description' => $payload['description'],
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'organization' => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'owner_id' => $user->id,
                    'slug' => $organization->slug,
                ],
            ],
        ]);

    $this->assertDatabaseHas('projects', [
        'name' => $payload['name'],
        'description' => $payload['description'],
        'user_id' => $user->id,
        'organization_id' => $organization->id,
    ]);

    $this->assertDatabaseCount('projects', 1);
});

dataset('invalid_payload', [
    'null name' => [
        ['name' => null], ['name' => ['The name field must be a string.', 'The name field must be at least 3 characters.']],
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

it('should return unprocessable entity when the updated project payload is invalid', function (array $payload, array $expectedErrors) {
    $user = User::factory()->create();
    $payload = Organization::factory()->make(['owner_id' => $user->id, ...$payload])->toArray();

    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization);

    $project = Project::factory()->create(['user_id' => $user->id, 'organization_id' => $organization->id]);

    $response = $this->actingAs($user)->patchJson(route('api.projects.update', [
        'project' => $project->id,
    ]), $payload);

    $response
        ->assertUnprocessable()
        ->assertJsonFragment([
            'errors' => $expectedErrors,
        ]);
})->with('invalid_payload');
