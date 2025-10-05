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
