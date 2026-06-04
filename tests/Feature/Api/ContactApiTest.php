<?php

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

test('can list contacts', function (): void {
    Contact::factory()->count(3)->create();

    $this->getJson('/api/contacts')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can create a contact', function (): void {
    $payload = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
    ];

    $this->postJson('/api/contacts', $payload)
        ->assertCreated()
        ->assertJsonPath('data.email', 'jane@example.com');

    $this->assertDatabaseHas('contacts', ['email' => 'jane@example.com']);
});

test('can show a contact', function (): void {
    $contact = Contact::factory()->create();

    $this->getJson("/api/contacts/{$contact->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $contact->id);
});

test('can update a contact', function (): void {
    $contact = Contact::factory()->create();

    $this->patchJson("/api/contacts/{$contact->id}", ['first_name' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.first_name', 'Updated');
});

test('can delete a contact', function (): void {
    $contact = Contact::factory()->create();

    $this->deleteJson("/api/contacts/{$contact->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
});

test('store contact validates required fields', function (): void {
    $this->postJson('/api/contacts', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'last_name']);
});
