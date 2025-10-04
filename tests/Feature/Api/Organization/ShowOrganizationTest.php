<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

it('should return the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    $response = $this->actingAs($user)->getJson(route('api.organizations.show', [
        'organization' => $organization->id,
    ]));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'owner' => [
                    'id',
                    'name',
                ],
            ],
        ]);
});

it('should return forbidden when trying to access another user organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    $response = $this->actingAs($user)->getJson(route('api.organizations.show', [
        'organization' => $organization->id,
    ]));

    $response
        ->assertForbidden()
        ->assertJsonFragment([
            'message' => 'This action is unauthorized.',
        ]);
});

it('should return not found when trying to access a non-existing organization', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('api.organizations.show', [
        'organization' => str()->random(),
    ]));

    $response->assertNotFound();
});
