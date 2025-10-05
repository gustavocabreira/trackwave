<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

it('should return the project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    $project = Project::factory()->create(['user_id' => $user->id, 'organization_id' => $organization->id]);

    $response = $this->actingAs($user)->getJson(route('api.projects.show', [
        'project' => $project->id,
    ]));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'organization_id',
                'user_id',
                'name',
                'description',
                'organization' => [
                    'id',
                    'name',
                ],
                'user' => [
                    'id',
                    'name',
                ],
            ],
        ]);
});

it('should return forbidden when trying to access another organization project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization);

    $project = Project::factory()->create();

    $response = $this->actingAs($user)->getJson(route('api.projects.show', [
        'project' => $project->id,
    ]));

    $response
        ->assertForbidden()
        ->assertJsonFragment([
            'message' => 'This action is unauthorized.',
        ]);
});

it('should return not found when trying to access a non-existing project', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('api.projects.show', [
        'project' => str()->random(),
    ]));

    $response->assertNotFound();
});
