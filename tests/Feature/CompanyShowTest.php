<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('authenticated users can view a company page', function () {
    $user = User::factory()->create();

    $company = Company::query()->create([
        'name' => 'Acme Ltd',
        'email' => 'acme@example.test',
        'domain' => 'acme.test',
        'phone' => '01234 567890',
    ]);

    $deal = Deal::query()->create([
        'user_id' => $user->id,
        'name' => 'Alpha Deal',
    ]);

    $deal->companies()->attach($company->id, ['is_primary' => true]);

    $this->actingAs($user)
        ->get(route('companies.show', $company))
        ->assertOk();
});

test('associated deals include the deal owner name', function () {
    $user = User::factory()->create();

    $company = Company::query()->create([
        'name' => 'Acme Ltd',
        'email' => 'acme@example.test',
    ]);

    $deal = Deal::query()->create([
        'user_id' => $user->id,
        'name' => 'Alpha Deal',
    ]);

    $deal->companies()->attach($company->id, ['is_primary' => true]);

    $this->actingAs($user);

    Livewire::test('companies.view', ['company' => $company])
        ->assertSee('Alpha Deal')
        ->assertSee('Deal Owner: '.$user->name);
});

test('company record can be edited from the livewire component', function () {
    $user = User::factory()->create();

    $company = Company::query()->create([
        'name' => 'Acme Ltd',
        'email' => 'acme@example.test',
        'domain' => 'acme.test',
        'phone' => '01234 567890',
    ]);

    $contact = Contact::query()->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.test',
    ]);

    $company->contacts()->attach($contact->id);

    $this->actingAs($user);

    Livewire::test('companies.view', ['company' => $company])
        ->set('name', 'Globex Corp')
        ->set('email', 'globex@example.test')
        ->set('domain', 'globex.test')
        ->set('phone', '0207 000 0000')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Company updated successfully.');

    expect($company->fresh()->name)->toBe('Globex Corp');
    expect($company->fresh()->email)->toBe('globex@example.test');
    expect($company->fresh()->domain)->toBe('globex.test');
    expect($company->fresh()->phone)->toBe('0207 000 0000');
});
