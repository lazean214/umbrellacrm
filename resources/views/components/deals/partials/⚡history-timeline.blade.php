<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

{{-- components/deals/partials/⚡history-timeline.blade.php --}}

<div class="flow-root">
    <ul role="list" class="-mb-8">
        @forelse($deal->histories as $history)
            <li>
                <div class="relative pb-8">
                    @if(!$loop->last)
                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-slate-200 dark:bg-slate-700" aria-hidden="true"></span>
                    @endif
                    <div class="relative flex items-start space-x-3">
                        <div class="relative">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-slate-900"
                                @php
                                    $icon = match($history->action) {
                                        'created' => '🎉',
                                        'stage_moved' => '🔄',
                                        'details_updated' => '✏️',
                                        'association_updated' => '🔗',
                                        'owner_changed' => '👤',
                                        default => '📝'
                                    };
                                @endphp
                            >
                                <span class="text-lg">{{ $icon }}</span>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div>
                                <div class="text-sm font-medium text-slate-900 dark:text-white">
                                    {{ match($history->action) {
                                        'created' => 'Deal Created',
                                        'stage_moved' => 'Stage Changed',
                                        'details_updated' => 'Details Updated',
                                        'association_updated' => 'Association Updated',
                                        'owner_changed' => 'Owner Changed',
                                        default => ucfirst($history->action)
                                    } }}
                                </div>
                                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                                    {{ $history->details }}
                                </p>
                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                    {{ $history->created_at->format('d M Y, H:i') }}
                                    @if($history->user)
                                        by {{ $history->user->name }}
                                    @else
                                        by System
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li class="text-center py-8 text-slate-400">
                No activity recorded yet
            </li>
        @endforelse
    </ul>
</div>