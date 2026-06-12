<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('notifications dropdown can mark all notifications as read', function () {
    $user = User::factory()->create();

    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test',
        'data' => [
            'title' => 'Deal updated',
            'message' => 'A deal moved stages.',
            'url' => '#',
        ],
    ]);

    $this->actingAs($user);

    Livewire::test('notifications-dropdown')
        ->assertSet('unreadCount', 1)
        ->assertSee('Deal updated')
        ->call('markAsReadAll')
        ->assertSet('unreadCount', 0);

    expect($user->notifications()->whereNull('read_at')->count())->toBe(0);
});
