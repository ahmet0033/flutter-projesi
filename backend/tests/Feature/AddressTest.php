<?php

use App\Models\Address;
use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('customer');
    Sanctum::actingAs($this->user);
});

test('customers can manage addresses and defaults', function () {
    $first = $this->postJson('/api/v1/addresses', [
        'label' => 'Home',
        'name' => 'Test Customer',
        'phone' => '+905551112233',
        'line1' => 'Test Street 1',
        'city' => 'Mersin',
        'postal_code' => '33000',
        'country' => 'TR',
    ])->assertCreated()
        ->assertJsonPath('data.is_default_shipping', true)
        ->assertJsonPath('data.is_default_billing', true);

    $second = $this->postJson('/api/v1/addresses', [
        'label' => 'Work',
        'name' => 'Test Customer',
        'phone' => '+905551112233',
        'line1' => 'Work Street 2',
        'city' => 'Mersin',
        'postal_code' => '33000',
        'country' => 'TR',
        'is_default_shipping' => true,
    ])->assertCreated();

    expect($second->json('data.is_default_shipping'))->toBeTrue();
    expect(Address::find($first->json('data.id'))->is_default_shipping)->toBeFalse();

    $this->deleteJson('/api/v1/addresses/'.$second->json('data.id'))
        ->assertOk();

    expect($this->user->addresses()->count())->toBe(1);
});
