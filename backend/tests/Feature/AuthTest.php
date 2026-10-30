<?php

use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
});

test('a customer can register and receives a token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'New Customer',
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'pest',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['user' => ['id', 'name', 'email', 'roles'], 'token']);

    $user = User::where('email', 'new@example.com')->firstOrFail();
    expect($user->hasRole('customer'))->toBeTrue();
});

test('a customer can log in, read their profile, and log out', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => 'password123',
    ]);
    $user->assignRole('customer');

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'password123',
        'device_name' => 'pest',
    ])->assertOk();

    $token = $response->json('token');

    $this->withToken($token)
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.email', 'login@example.com');

    $this->withToken($token)
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    expect(PersonalAccessToken::count())->toBe(0);
});

test('invalid credentials are rejected', function () {
    $user = User::factory()->create(['password' => 'password123']);
    $user->assignRole('customer');

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});
