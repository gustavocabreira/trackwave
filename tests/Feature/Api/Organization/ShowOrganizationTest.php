<?php

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
