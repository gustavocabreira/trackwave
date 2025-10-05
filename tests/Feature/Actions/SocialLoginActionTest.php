<?php

declare(strict_types=1);

use App\Models\OauthProvider;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

it('should return a 404 for an invalid provider', function () {
    $providerName = 'aws';
    $response = $this->get(route('auth.redirect', ['provider' => $providerName]));

    $response->assertNotFound();
});

it('should redirect to Google OAuth authorization page', function () {
    $response = $this->get(route('auth.redirect', ['provider' => 'google']));
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
    $provider = OauthProvider::where('name', 'google')->first();

    $googleUser = (object) [
        'id' => '123456789',
        'name' => 'Google User',
        'email' => 'googleuser@example.com',
        'token' => 'fake-token',
        'refreshToken' => 'fake-refresh-token',
        'expiresIn' => 3600,
    ];

    Socialite::shouldReceive('driver->stateless->user')
        ->once()
        ->andReturn($googleUser);

    $response = $this->get(route('auth.callback', ['provider' => 'google']));
    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'name' => 'Google User',
        'email' => 'googleuser@example.com',
    ]);

    $provider = OauthProvider::where('name', 'google')->first();
    $user = User::where('email', 'googleuser@example.com')->first();

    $this->assertDatabaseHas('social_accounts', [
        'user_id' => $user->id,
        'provider_id' => $provider->id,
        'provider_user_id' => '123456789',
    ]);

    $this->assertAuthenticatedAs($user);
});
