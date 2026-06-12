<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

test('can list users', function (): void {
    User::factory()->count(2)->create();

    $this->getJson('/api/users')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('can create a user', function (): void {
    $payload = [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $this->postJson('/api/users', $payload)
        ->assertCreated()
        ->assertJsonPath('data.email', 'newuser@example.com');

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

test('can show a user', function (): void {
    $user = User::factory()->create();

    $this->getJson("/api/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

test('can update a user', function (): void {
    $user = User::factory()->create();

    $this->patchJson("/api/users/{$user->id}", ['name' => 'Updated Name'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');
});

test('can delete a user', function (): void {
    $user = User::factory()->create();

    $this->deleteJson("/api/users/{$user->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('store user validates required fields', function (): void {
    $this->postJson('/api/users', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});
