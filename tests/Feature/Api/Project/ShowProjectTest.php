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
