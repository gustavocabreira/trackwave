<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

it('should return a list of the organization projects', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    Project::factory()->count(15)->create(['organization_id' => $organization->id, 'user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson(route('api.organizations.projects.index', [
        'organization' => $organization->id,
    ]));

    $response
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
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
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
        ]);

    expect($response->json('meta.current_page'))->toBe(1)
        ->and($response->json('meta.from'))->toBe(1)
        ->and($response->json('meta.last_page'))->toBe(2)
        ->and($response->json('meta.per_page'))->toBe(10)
        ->and($response->json('meta.to'))->toBe(10)
        ->and($response->json('meta.total'))->toBe(15);
});
