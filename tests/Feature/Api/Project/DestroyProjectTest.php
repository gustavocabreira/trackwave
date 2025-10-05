<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

it('should delete the project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    $project = Project::factory()->create(['user_id' => $user->id, 'organization_id' => $organization->id]);

    $response = $this->actingAs($user)->deleteJson(route('api.projects.destroy', [
        'project' => $project->id,
    ]));

    $response->assertNoContent();

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);

    $this->assertDatabaseCount('projects', 0);
});

it('should return forbidden when trying to delete another organization project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization);

    $project = Project::factory()->create();

    $response = $this->actingAs($user)->deleteJson(route('api.projects.destroy', [
        'project' => $project->id,
    ]));

    $response
        ->assertForbidden()
        ->assertJsonFragment([
            'message' => 'This action is unauthorized.',
        ]);
});
