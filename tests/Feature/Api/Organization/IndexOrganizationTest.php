<?php

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
