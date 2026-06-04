<?php

use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('authenticated users can view a contact page', function () {
    $user = User::factory()->create();

    $contact = Contact::query()->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.test',
        'phone' => '01234 567890',
    ]);

    $deal = Deal::query()->create([
        'user_id' => $user->id,
        'name' => 'Alpha Deal',
    ]);

    $deal->contacts()->attach($contact->id, ['is_primary' => true]);

    $this->actingAs($user)
        ->get(route('contacts.show', $contact))
        ->assertOk();
});

test('associated deals include the deal owner name', function () {
    $user = User::factory()->create();

    $contact = Contact::query()->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.test',
    ]);

    $deal = Deal::query()->create([
        'user_id' => $user->id,
        'name' => 'Alpha Deal',
    ]);

    $deal->contacts()->attach($contact->id, ['is_primary' => true]);

    $this->actingAs($user);

    Livewire::test('contacts.view', ['contact' => $contact])
        ->assertSee('Alpha Deal')
        ->assertSee('Deal Owner: '.$user->name);
});

test('contact record can be edited from the livewire component', function () {
    $user = User::factory()->create();

    $contact = Contact::query()->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.test',
        'phone' => '01234 567890',
        'city' => 'London',
    ]);

    $this->actingAs($user);

    Livewire::test('contacts.view', ['contact' => $contact])
        ->set('first_name', 'Grace')
        ->set('last_name', 'Hopper')
        ->set('email', 'grace@example.test')
        ->set('phone', '0207 000 0000')
        ->set('city', 'Arlington')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Contact updated successfully.');

    expect($contact->fresh()->first_name)->toBe('Grace');
    expect($contact->fresh()->last_name)->toBe('Hopper');
    expect($contact->fresh()->email)->toBe('grace@example.test');
    expect($contact->fresh()->phone)->toBe('0207 000 0000');
    expect($contact->fresh()->city)->toBe('Arlington');
});
