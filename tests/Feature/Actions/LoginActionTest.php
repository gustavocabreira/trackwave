<?php

declare(strict_types=1);

use Laravel\Socialite\Facades\Socialite;

it('should redirect to Google OAuth authorization page', function () {
    $response = $this->get(route('api.auth.redirect', ['provider' => 'google']));
    $response->assertRedirect();

    $redirectUrl = $response->headers->get('Location');

    parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $queryParams);

    expect($queryParams)
        ->toHaveKey('redirect_uri')
        ->toHaveKey('scope')
        ->toHaveKey('response_type')
        ->and($queryParams['redirect_uri'])
        ->toBe(config('services.google.redirect'))
        ->and($queryParams['scope'])
        ->toBe('openid profile email')
        ->and($queryParams['response_type'])
        ->toBe('code');
});

it('logs in a user with Google OAuth', function () {
    $googleUser = (object) [
        'id' => '12345678',
        'name' => 'Google User',
        'email' => 'googleuser@example.com',
    ];

    Socialite::shouldReceive('driver->stateless->user')
        ->once()
        ->andReturn($googleUser);

    $response = $this->get(route('api.auth.callback', ['provider' => 'google']));
    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'google_id' => '12345678',
        'email' => 'googleuser@example.com',
    ]);
});
