<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

it('should create a new organization', function () {
    $user = User::factory()->create();

    $payload = Organization::factory()->make(['owner_id' => $user->id])->toArray();

    $response = $this->actingAs($user)->postJson(route('api.organizations.store'), $payload);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
            ],
        ]);

    $this->assertDatabaseHas('organizations', [
        'name' => $payload['name'],
        'slug' => $payload['slug'],
        'owner_id' => $user->id,
    ]);

    $this->assertDatabaseCount('organizations', 1);

    $this->assertDatabaseHas('organization_user', [
        'organization_id' => $response->json('data.id'),
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseCount('organization_user', 1);
});
