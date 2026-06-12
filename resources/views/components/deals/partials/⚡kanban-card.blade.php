@php
    $dealUrl = route('deals.show', ['deal' => $deal['id']]);
@endphp

<div class="relative bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 rounded-xl p-3.5 flex items-start gap-3 shadow-sm cursor-pointer active:cursor-grabbing group transition-all duration-150 hover:shadow-md"
    draggable="true" x-data="{
        isDragging: false,
        handleDragStart(e) {
            this.isDragging = true;
            this.$dispatch('drag-start', { dealId: {{ $deal['id'] }}, stage: '{{ $deal['stage'] }}' });
        },
        handleDragEnd(e) {
            setTimeout(() => { this.isDragging = false; }, 100);
        },
        handleClick(e) {
            // Don't navigate if dragging
            if (this.isDragging) {
                e.preventDefault();
                return false;
            }
            // Don't navigate if clicking on a link (let the link handle it)
            if (e.target.closest('a')) {
                return;
            }
            window.location.assign({{ Js::from($dealUrl) }});
        }
    }" @dragstart="handleDragStart($event)" @dragend="handleDragEnd($event)"
    @click="handleClick($event)" wire:key="card-{{ $deal['id'] }}">
    {{-- Left accent bar — inline style so color always renders --}}
    <div class="absolute left-0 top-3 bottom-3 w-[3px] rounded-r-full" style="background-color: {{ $cfg['accent'] }};">
    </div>

    {{-- Drag handle --}}
    <div
        class="text-slate-300 dark:text-slate-600 group-hover:text-slate-500 dark:group-hover:text-slate-400 transition text-sm pt-0.5 select-none shrink-0">
        ⠿
    </div>

    <div class="flex-1 min-w-0 pl-1">
        {{-- Deal name --}}
        <p
            class="text-sm font-semibold text-slate-900 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition truncate mb-2">
            <a href="{{ $dealUrl }}" class="focus:underline outline-none">
                {{ $deal['name'] }}
            </a>
        </p>

        {{-- Amount + company --}}
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-sm font-bold text-slate-800 dark:text-white tabular-nums">
                £{{ number_format($deal['amount'], 0) }}
            </span>
            @if (!empty($deal['internal_company']))
                <span
                    class="text-[10px] font-semibold px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-500 dark:text-slate-400 shrink-0 truncate max-w-[80px]">
                    {{ $deal['internal_company'] }}
                </span>
            @endif
        </div>

        {{-- Meta rows --}}
        <div class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
            {{-- Created date --}}
            <div class="flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 opacity-70" viewBox="0 0 24 24"
                    fill="currentColor">
                    <path
                        d="M7 2h1a1 1 0 0 1 1 1v1h5V3a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a3 3 0 0 1 3 3v11a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3V3a1 1 0 0 1 1-1m8 2h1V3h-1zM8 4V3H7v1zM6 5a2 2 0 0 0-2 2v1h15V7a2 2 0 0 0-2-2zM4 18a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V9H4zm8-5h5v5h-5zm1 1v3h3v-3z" />
                </svg>
                <span>{{ \Carbon\Carbon::parse($deal['created_at'])->diffForHumans() }}</span>
            </div>

            {{-- Contact --}}
            @if (!empty($deal['contacts'][0]))
                <div class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 opacity-70" viewBox="0 0 24 24"
                        fill="currentColor">
                        <path
                            d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
                    </svg>
                    <span class="truncate">{{ $deal['contacts'][0]['first_name'] }}
                        {{ $deal['contacts'][0]['last_name'] }}</span>
                </div>
            @endif

            {{-- Company --}}
            @if (!empty($deal['companies'][0]))
                <div class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"
                        title="Company" fill="currentColor">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor"
                            d="M19 3v18h-6v-3.5h-2V21H5V3zm-4 4h2V5h-2zm-4 0h2V5h-2zM7 7h2V5H7zm8 4h2V9h-2zm-4 0h2V9h-2zm-4 0h2V9H7zm8 4h2v-2h-2zm-4 0h2v-2h-2zm-4 0h2v-2H7zm8 4h2v-2h-2zm-8 0h2v-2H7zM21 1H3v22h18z" />
                    </svg>

                    <span class="truncate">{{ $deal['companies'][0]['name'] }}</span>
                </div>
            @endif

            {{-- Owner --}}
            <div class="flex items-center gap-1.5 pt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16">
                    <path d="M0 0h16v16H0z" fill="none" />
                    <path fill="currentColor" d="M11 7c0 1.66-1.34 3-3 3S5 8.66 5 7s1.34-3 3-3s3 1.34 3 3" />
                    <path fill="currentColor" fill-rule="evenodd"
                        d="M16 8c0 4.42-3.58 8-8 8s-8-3.58-8-8s3.58-8 8-8s8 3.58 8 8M4 13.75C4.16 13.484 5.71 11 7.99 11c2.27 0 3.83 2.49 3.99 2.75A6.98 6.98 0 0 0 14.99 8c0-3.87-3.13-7-7-7s-7 3.13-7 7c0 2.38 1.19 4.49 3.01 5.75"
                        clip-rule="evenodd" />
                </svg>

                <span class="truncate font-medium">
                    {{ $deal['user'] ? $deal['user']['name'] : 'Unassigned' }}
                </span>
            </div>
        </div>
    </div>
</div>
