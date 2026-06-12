<?php

use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

test('can list deals', function (): void {
    Deal::factory()->count(3)->create();

    $this->getJson('/api/deals')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can create a deal', function (): void {
    $payload = ['name' => 'Acme Deal'];

    $this->postJson('/api/deals', $payload)
        ->assertCreated()
        ->assertJsonPath('data.name', 'Acme Deal');

    $this->assertDatabaseHas('deals', ['name' => 'Acme Deal']);
});

test('can show a deal', function (): void {
    $deal = Deal::factory()->create();

    $this->getJson("/api/deals/{$deal->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $deal->id);
});

test('can update a deal', function (): void {
    $deal = Deal::factory()->create();

    $this->patchJson("/api/deals/{$deal->id}", ['name' => 'Updated Deal'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Deal');
});

test('can delete a deal', function (): void {
    $deal = Deal::factory()->create();

    $this->deleteJson("/api/deals/{$deal->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('deals', ['id' => $deal->id]);
});

test('store deal validates required fields', function (): void {
    $this->postJson('/api/deals', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});
