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

it('should return forbidden if the user is not a member of the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization);
    $anotherUser = User::factory()->create();

    $response = $this->actingAs($anotherUser)->getJson(route('api.organizations.projects.index', [
        'organization' => $organization->id,
    ]));

    $response->assertForbidden();
});

it('should return only the organization projects', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    $anotherUser = User::factory()->create();
    $anotherOrganization = Organization::factory()->create(['owner_id' => $anotherUser->id]);
    $anotherUser->organizations()->attach($anotherOrganization);

    Project::factory()->count(15)->create(['organization_id' => $organization->id, 'user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson(route('api.organizations.projects.index', [
        'organization' => $organization->id,
    ]));

    foreach ($response->json('data') as $project) {
        expect($project['organization_id'])->not->toBe($anotherOrganization->id);
        expect($project['user_id'])->toBe($user->id);
    }

    expect($response->json('meta.current_page'))->toBe(1)
        ->and($response->json('meta.from'))->toBe(1)
        ->and($response->json('meta.last_page'))->toBe(2)
        ->and($response->json('meta.per_page'))->toBe(10)
        ->and($response->json('meta.to'))->toBe(10)
        ->and($response->json('meta.total'))->toBe(15);
});

it('should be able to paginate the project list', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    Project::factory()->count(15)->create(['organization_id' => $organization->id, 'user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson(route('api.organizations.projects.index', [
        'organization' => $organization->id,
        'page' => 2,
    ]));

    $response->assertOk()->assertJsonCount(5, 'data');

    expect($response->json('meta.current_page'))->toBe(2)
        ->and($response->json('meta.from'))->toBe(11)
        ->and($response->json('meta.last_page'))->toBe(2)
        ->and($response->json('meta.per_page'))->toBe(10)
        ->and($response->json('meta.to'))->toBe(15)
        ->and($response->json('meta.total'))->toBe(15);
});

it('should be able to filter the project list by name', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    Project::factory()->count(15)->create(['organization_id' => $organization->id, 'user_id' => $user->id]);
    Project::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id, 'name' => 'project']);

    $response = $this->actingAs($user)->getJson(route('api.organizations.projects.index', [
        'organization' => $organization->id,
        'name' => 'project',
    ]));

    $response->assertOk()->assertJsonCount(1, 'data');

    expect($response->json('data.0.name'))->toBe('project');
});

it('should be able to select how many projects per page', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    Project::factory()->count(15)->create(['organization_id' => $organization->id, 'user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson(route('api.organizations.projects.index', [
        'organization' => $organization->id,
        'page' => 2,
        'per_page' => 5,
    ]));

    $response->assertOk()->assertJsonCount(5, 'data');

    expect($response->json('meta.current_page'))->toBe(2)
        ->and($response->json('meta.from'))->toBe(6)
        ->and($response->json('meta.last_page'))->toBe(3)
        ->and($response->json('meta.per_page'))->toBe(5)
        ->and($response->json('meta.to'))->toBe(10)
        ->and($response->json('meta.total'))->toBe(15);
});

it('should be able to order the project list by name in ascending order', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    Project::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id, 'name' => 'First Project']);
    Project::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id, 'name' => 'Second Project']);

    $response = $this->actingAs($user)->getJson(route('api.organizations.projects.index', [
        'organization' => $organization->id,
        'order_by' => 'name',
        'direction' => 'asc',
    ]));

    $response->assertOk()->assertJsonCount(2, 'data');

    expect($response->json('data.0.name'))->toBe('First Project')
        ->and($response->json('data.1.name'))->toBe('Second Project');
});

it('should be able to order the project list by name in descending order', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    Project::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id, 'name' => 'First Project']);
    Project::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id, 'name' => 'Second Project']);

    $response = $this->actingAs($user)->getJson(route('api.organizations.projects.index', [
        'organization' => $organization->id,
        'order_by' => 'name',
        'direction' => 'desc',
    ]));

    $response->assertOk()->assertJsonCount(2, 'data');

    expect($response->json('data.0.name'))->toBe('Second Project')
        ->and($response->json('data.1.name'))->toBe('First Project');
});
