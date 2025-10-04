<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

it('should return a list of the user organizations', function () {
    $user = User::factory()->create();
    $organizations = Organization::factory()->count(15)->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organizations);

    $response = $this->actingAs($user)->getJson(route('api.organizations.index'));

    $response
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'slug',
                    'owner' => [
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

it('should return only the user organizations', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organization);

    $anotherUser = User::factory()->create();
    $anotherOrganization = Organization::factory()->create(['owner_id' => $anotherUser->id]);
    $anotherUser->organizations()->attach($anotherOrganization);

    $response = $this->actingAs($user)->getJson(route('api.organizations.index'));

    foreach ($response->json('data') as $organization) {
        expect($organization['id'])->not->toBe($anotherOrganization->id);
    }

    expect($response->json('meta.current_page'))->toBe(1)
        ->and($response->json('meta.from'))->toBe(1)
        ->and($response->json('meta.last_page'))->toBe(1)
        ->and($response->json('meta.per_page'))->toBe(10)
        ->and($response->json('meta.to'))->toBe(1)
        ->and($response->json('meta.total'))->toBe(1);
});

it('should be able to paginate', function () {
    $user = User::factory()->create();
    $organizations = Organization::factory()->count(15)->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organizations);

    $response = $this->actingAs($user)->getJson(route('api.organizations.index', [
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

it('should be able to filter by name', function () {
    $user = User::factory()->create();
    $organizations = Organization::factory()->count(15)->create(['owner_id' => $user->id]);
    $user->organizations()->attach($organizations);

    $anotherOrganization = Organization::factory()->create(['owner_id' => $user->id, 'name' => 'organization']);
    $user->organizations()->attach($anotherOrganization);

    $response = $this->actingAs($user)->getJson(route('api.organizations.index', [
        'name' => 'organization',
    ]));

    $response->assertOk()->assertJsonCount(1, 'data');

    expect($response->json('data.0.name'))->toBe('organization');
});
