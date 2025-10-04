<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

it('should delete the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    $response = $this->actingAs($user)->deleteJson(route('api.organizations.destroy', [
        'organization' => $organization->id,
    ]));

    $response->assertNoContent();

    $this->assertDatabaseMissing('organizations', [
        'id' => $organization->id,
    ]);

    $this->assertDatabaseCount('organizations', 0);
    $this->assertDatabaseCount('organization_user', 0);
});

it('should return forbidden when trying to delete another user organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    $response = $this->actingAs($user)->deleteJson(route('api.organizations.destroy', [
        'organization' => $organization->id,
    ]));

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

    $response = $this->actingAs($anotherUser)->deleteJson(route('api.organizations.destroy', [
        'organization' => $organization->id,
    ]));

    $response
        ->assertForbidden()
        ->assertJsonFragment([
            'message' => 'This action is unauthorized.',
        ]);
});

it('should remove the organization from all the users', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);
    $anotherUser->organizations()->attach($organization);

    $response = $this->actingAs($user)->deleteJson(route('api.organizations.destroy', [
        'organization' => $organization->id,
    ]));

    $response->assertNoContent();

    $this->assertDatabaseMissing('organizations', [
        'id' => $organization->id,
    ]);

    $this->assertDatabaseCount('organizations', 0);
    $this->assertDatabaseCount('organization_user', 0);
});
