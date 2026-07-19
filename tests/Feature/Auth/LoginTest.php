<?php
use function Pest\Laravel\postJson;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('logs in with correct credentials and returns a token', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
});

it('rejects login with incorrect password', function () {
    User::factory()->create([
        'email' => 'wrongpass@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = postJson('/api/login', [
        'email' => 'wrongpass@example.com',
        'password' => 'not-the-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});