<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('text sections keep independent content when multiple sections exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('activities.email.designer.create')
        ->set('editorMode', 'builder')
        ->set('sections', [
            [
                'id' => 'text-a',
                'type' => 'text',
                'content' => 'First block',
                'image_url' => '',
                'media_id' => null,
                'alt' => '',
                'label' => '',
                'url' => '',
            ],
            [
                'id' => 'text-b',
                'type' => 'text',
                'content' => 'Second block',
                'image_url' => '',
                'media_id' => null,
                'alt' => '',
                'label' => '',
                'url' => '',
            ],
        ])
        ->set('activeSectionId', 'text-b')
        ->set('sections.1.content', 'Second block changed')
        ->assertSet('sections.0.content', 'First block')
        ->assertSet('sections.1.content', 'Second block changed');
});
