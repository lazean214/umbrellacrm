<?php

use Livewire\Component;
use Livewire\Attributes\On; // 👈 Add this import

new class extends Component
{
    // ✅ This attribute forces Livewire to re-render the bell whenever 'notification-updated' is dispatched
    #[On('notification-updated')]
    public function refreshNotifications(): void
    {
        // Simply triggering this method clears the Livewire cache and re-fetches computed properties
    }

    public function getNotificationsProperty()
    {
        return auth()
            ->user()
            ->notifications()
            ->latest()
            ->take(20)
            ->get();
    }

    public function getUnreadCountProperty()
    {
        return auth()
            ->user()
            ->unreadNotifications()
            ->count();
    }

    public function markAsRead($id)
    {
        auth()
            ->user()
            ->notifications()
            ->find($id)
            ?->markAsRead();
    }

    public function markAsReadAll(): void
    {
        auth()
            ->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);
    }
};
?>

<div
    x-data="{ open: false }"
    class="relative"
    wire:poll.60s
>
    {{-- Bell --}}
    <button
        @click="open = ! open"
        class="relative"
    >
        <flux:icon.bell class="w-5 h-5" />

        @if($this->unreadCount > 0)
            <span
                class="absolute -top-1 -right-1
                min-w-[18px] h-[18px]
                rounded-full bg-red-500
                text-white text-[10px]
                flex items-center justify-center px-1"
            >
                {{ $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        x-cloak
        class="fixed top-4 right-4 z-50 
               w-96 rounded-xl border
               border-zinc-200 dark:border-zinc-700
               bg-white dark:bg-zinc-900
               shadow-2xl overflow-hidden
               z-[99999]"
    >
        <div class="p-4 border-b dark:border-zinc-700 flex justify-between items-center">
            <h3 class="font-semibold">
                Notifications
            </h3>

            <div class="flex items-center gap-3">
                @if($this->unreadCount)
                    <button
                        wire:click="markAsReadAll"
                        class="text-xs font-medium text-blue-600 dark:text-blue-400
                               hover:text-blue-700 dark:hover:text-blue-300
                               transition-colors"
                    >
                        Mark all as read
                    </button>
                @endif

                @if($this->unreadCount)
                    <span class="text-xs text-zinc-500">
                        {{ $this->unreadCount }} unread
                    </span>
                @endif
            </div>
        </div>

        <div class="max-h-[500px] overflow-y-auto">
            @forelse($this->notifications as $notification)
                <a
                    href="{{ $notification->data['url'] ?? '#' }}"
                    wire:click="markAsRead('{{ $notification->id }}')"
                    class="block p-4 border-b
                           hover:bg-zinc-50
                           dark:hover:bg-zinc-800
                           {{ is_null($notification->read_at) ? 'bg-blue-50 dark:bg-zinc-800/50' : '' }}"
                >
                    <div class="font-medium">
                        {{ $notification->data['title'] ?? 'Notification' }}
                    </div>

                    <div class="text-sm text-zinc-500">
                        {{ $notification->data['message'] ?? '' }}
                    </div>

                    <div class="text-xs text-zinc-400 mt-1">
                        {{ $notification->created_at->diffForHumans() }}
                    </div>
                </a>
            @empty
                <div class="p-6 text-center text-zinc-500">
                    No notifications
                </div>
            @endforelse
        </div>
    </div>
</div>