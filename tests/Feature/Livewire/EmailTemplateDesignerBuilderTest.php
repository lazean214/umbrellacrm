<?php

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('legacy mode template can be saved', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('activities.email.designer.create')
        ->set('name', 'Legacy Welcome')
        ->set('subject', 'Welcome Subject')
        ->set('body', 'Hello there from the legacy email body.')
        ->set('editorMode', 'legacy')
        ->call('save')
        ->assertHasNoErrors();

    expect(EmailTemplate::query()->where('name', 'Legacy Welcome')->exists())->toBeTrue();
});

test('builder mode template can be saved with sections', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('activities.email.designer.create')
        ->set('name', 'Builder Template')
        ->set('subject', 'Builder Subject')
        ->set('editorMode', 'builder')
        ->set('sections', [
            [
                'id' => 'sec-1',
                'type' => 'text',
                'content' => 'Builder body text',
                'image_url' => '',
                'media_id' => null,
                'alt' => '',
                'label' => '',
                'url' => '',
            ],
            [
                'id' => 'sec-2',
                'type' => 'button',
                'content' => '',
                'image_url' => '',
                'media_id' => null,
                'alt' => '',
                'label' => 'Open dashboard',
                'url' => 'https://example.com/dashboard',
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $template = EmailTemplate::query()->where('name', 'Builder Template')->first();

    expect($template)->not->toBeNull();
    expect($template->editor_mode)->toBe('builder');
    expect($template->sections)->toBeArray();
    expect($template->sections[0]['type'])->toBe('text');
});

test('builder sections can be sorted', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('activities.email.designer.create')
        ->set('editorMode', 'builder')
        ->set('sections', [
            ['id' => 'first', 'type' => 'text', 'content' => 'First', 'image_url' => '', 'media_id' => null, 'alt' => '', 'label' => '', 'url' => ''],
            ['id' => 'second', 'type' => 'text', 'content' => 'Second', 'image_url' => '', 'media_id' => null, 'alt' => '', 'label' => '', 'url' => ''],
        ])
        ->call('handleSectionSort', 'second', 0)
        ->assertSet('sections.0.id', 'second');
});
