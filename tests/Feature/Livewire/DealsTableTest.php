<?php

use App\Enums\DealStage;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('deals table component renders', function () {
    $user = User::factory()->create();

    Deal::create([
        'user_id' => $user->id,
        'name' => 'Alpha Deal',
        'amount' => 1000,
        'stage' => DealStage::DOC_SENT->value,
    ]);

    $this->actingAs($user);

    Livewire::test('deals.table')
        ->assertStatus(200)
        ->assertSee('Alpha Deal');
});

test('deals can be filtered by name', function () {
    $user = User::factory()->create();

    Deal::create([
        'user_id' => $user->id,
        'name' => 'Alpha Deal',
        'amount' => 1000,
        'stage' => DealStage::DOC_SENT->value,
    ]);

    Deal::create([
        'user_id' => $user->id,
        'name' => 'Beta Deal',
        'amount' => 2000,
        'stage' => DealStage::DOC_SENT->value,
    ]);

    $this->actingAs($user);

    Livewire::test('deals.table')
        ->set('filterDealName', 'Alpha')
        ->assertSee('Alpha Deal')
        ->assertDontSee('Beta Deal');
});

test('view mode can be toggled', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('deals.table')
        ->assertSet('view', 'kanban')
        ->call('setView', 'table')
        ->assertSet('view', 'table')
        ->call('setView', 'kanban')
        ->assertSet('view', 'kanban');
});

test('deal stage can be updated', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $deal = Deal::create([
        'user_id' => $user->id,
        'name' => 'Gamma Deal',
        'amount' => 3000,
        'stage' => DealStage::DOC_SENT->value,
    ]);

    Livewire::test('deals.table')
        ->call('updateStage', $deal->id, DealStage::DOC_SIGNED->value);

    expect($deal->fresh()->stage)->toBe(DealStage::DOC_SIGNED);
});
