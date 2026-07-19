<?php

use App\Models\Organization;
use App\Models\User;

use function Pest\Laravel\postJson;

it('registers a user and creates their organization as owner', function () {
    $response = postJson('/api/register', [
        'name' => 'Sutapa Sarkar',
        'email' => 'sutapa@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'organization_name' => 'Sutapa Inc',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['user' => ['id', 'name', 'email', 'organizations'], 'token']);

    expect(User::where('email', 'sutapa@example.com')->exists())->toBeTrue();

    $organization = Organization::where('name', 'Sutapa Inc')->first();
    expect($organization)->not->toBeNull();
    expect($organization->owner->email)->toBe('sutapa@example.com');
});

it('rejects registration with a duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = postJson('/api/register', [
        'name' => 'Someone Else',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'organization_name' => 'Someone Org',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});
