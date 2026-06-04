{{-- components/deals/partials/⚡kanban.blade.php --}}

@php
    $isSalesUser = $this->isSalesTeam();
@endphp

<div
    x-data="{
        draggingId: null,
        draggingStage: null,
        dragOverStage: null,

        onDragStart(dealId, stage, event) {
            this.draggingId = dealId;
            this.draggingStage = stage;

            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', String(dealId));
        },

        onDragOver(event, targetStage) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            this.dragOverStage = targetStage;
        },

        onDragLeave() {
            this.dragOverStage = null;
        },

        async onDrop(event, targetStage) {
            event.preventDefault();
            this.dragOverStage = null;

            if (!this.draggingId || this.draggingStage === targetStage) {
                this.resetDrag();
                return;
            }

            const dealId = this.draggingId;
            const card = document.querySelector(`[data-deal-id='${dealId}']`);

            if (card) {
                card.style.opacity = '.5';
                card.style.transform = 'scale(.97)';
                card.style.transition = 'all .2s ease';
            }

            this.resetDrag();

            await $wire.updateStage(dealId, targetStage);
        },

        resetDrag() {
            this.draggingId = null;
            this.draggingStage = null;
        }
    }"
    class="
        flex gap-3
        overflow-x-auto overflow-y-hidden
        pb-4 px-2
        min-h-[650px]
        snap-x snap-mandatory
        scrollbar-thin
    "
>

    @foreach($this->stages as $stage)
        @php
            $canEdit      = $this->canEditStage($stage);
            $stageInfo    = $stageConfig[$stage] ?? [];
            $dealsInStage = $this->getDealsByStage($stage);
            $stageTotal   = $this->getStageSum($stage);
        @endphp

        <div
            data-stage="{{ $stage }}"
            class="
                flex flex-col shrink-0
                rounded-2xl border-2
                transition-all duration-200
                snap-start

                w-[280px]
                md:w-[320px]
                lg:w-[340px]
                xl:w-[360px]

                h-[calc(100vh-210px)]
                min-h-[620px]
                max-h-[900px]
            "
            :class="{
                'ring-2 ring-indigo-400 dark:ring-indigo-500 ring-offset-1 border-indigo-300 dark:border-indigo-600':
                    dragOverStage === '{{ $stage }}',

                'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30':
                    dragOverStage !== '{{ $stage }}'
            }"
            @if($canEdit)
                @dragover.prevent="onDragOver($event, '{{ $stage }}')"
                @dragleave="onDragLeave()"
                @dragenter.prevent
                @drop.prevent="onDrop($event, '{{ $stage }}')"
            @endif
        >

            {{-- Header --}}
            <div
                class="
                    px-4 py-3
                    rounded-t-2xl
                    font-semibold
                    flex items-center justify-between
                    sticky top-0 z-1
                    shadow-sm
                "
                style="background-color: {{ $stageInfo['accent'] ?? '#6b7280' }}"
            >
                <div class="flex items-center gap-2 text-white min-w-0">
                    <span class="text-base shrink-0">
                        {{ $stageInfo['icon'] ?? '•' }}
                    </span>

                    <span class="text-sm font-semibold truncate">
                        {{ $stageInfo['label'] ?? $stage }}
                    </span>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-white/80 text-xs tabular-nums font-normal bg-black/20 rounded-full px-2 py-0.5">
                        {{ $dealsInStage->count() }}
                    </span>

                    @if($isSalesUser && !$canEdit)
                        <span class="text-[10px] bg-white/20 text-white px-2 py-0.5 rounded-full">
                            🔒
                        </span>
                    @endif
                </div>
            </div>

            {{-- Total --}}
            <div class="
                px-4 py-2
                text-xs font-semibold
                border-b
                border-slate-200 dark:border-slate-700
                bg-white/70 dark:bg-slate-800/40
                backdrop-blur-sm
                sticky top-[60px]
                z-1
            ">
                <span style="color: {{ $stageInfo['accentText'] ?? 'inherit' }}">
                    £{{ number_format($stageTotal, 0) }} total TSV
                </span>
            </div>

            {{-- Cards --}}
            <div
                class="
                    flex-1 overflow-y-auto
                    p-2 md:p-3
                    space-y-2
                "
                wire:loading.class="opacity-60 pointer-events-none"
                wire:target="updateStage,loadMoreKanban"
            >
                @forelse($dealsInStage as $deal)
                    <div
                        data-deal-id="{{ $deal['id'] }}"
                        wire:key="kanban-card-{{ $deal['id'] }}"
                        class="transition-transform duration-200"

                        @if($canEdit)
                            draggable="true"
                            @dragstart.stop="onDragStart({{ $deal['id'] }}, '{{ $stage }}', $event)"
                            @dragend="resetDrag()"
                        @endif
                    >
                        @include('components.deals.partials.⚡kanban-card', [
                            'deal' => $deal,
                            'cfg'  => $stageInfo,
                        ])
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-slate-400 dark:text-slate-600 select-none">
                        <svg
                            class="w-8 h-8 mb-2 opacity-40"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>

                        <p class="text-xs">No deals</p>

                        @if($canEdit)
                            <p class="text-[10px] mt-1 opacity-60">
                                Drop here to move
                            </p>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- Compliance footer --}}
            @if($isSalesUser && !$canEdit)
                <div class="
                    px-3 py-2
                    text-[11px]
                    text-center
                    text-amber-700 dark:text-amber-300
                    bg-amber-50 dark:bg-amber-900/20
                    rounded-b-2xl
                    border-t
                    border-amber-100 dark:border-amber-900/30
                ">
                    Managed by Compliance
                </div>
            @endif
        </div>
    @endforeach
</div>