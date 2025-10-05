<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

it('should be able to store a project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
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