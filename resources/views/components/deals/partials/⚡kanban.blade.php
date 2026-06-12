{{-- components/deals/partials/⚡kanban.blade.php --}}

@php
    $isSalesUser = $this->isSalesTeam();
    $editableStages = array_values(array_filter($this->stages, fn (string $stage) => $this->canEditStage($stage)));
@endphp

<div
    x-data="kanbanBoard({
        deals: {{ Js::from($deals) }},
        stages: {{ Js::from($stages) }},
        stageConfig: {{ Js::from($stageConfig) }},
        editableStages: {{ Js::from($editableStages) }},
        isSalesUser: {{ $isSalesUser ? 'true' : 'false' }},
        showUrlTemplate: {{ Js::from(route('deals.show', ['deal' => '__deal__'])) }},
    })"
    x-init="init()"
    class="flex min-h-[650px] snap-x snap-mandatory gap-3 overflow-x-auto overflow-y-hidden px-2 pb-4 scrollbar-thin"
>
    <template x-for="stage in stages" :key="stage">
        <section
            :data-stage="stage"
            class="flex h-[calc(100vh-210px)] max-h-[900px] min-h-[620px] w-[280px] shrink-0 snap-start flex-col rounded-2xl border-2 transition-all duration-200 md:w-[320px] lg:w-[340px] xl:w-[360px]"
            :class="dragOverStage === stage
                ? 'border-indigo-300 ring-2 ring-indigo-400 ring-offset-1 dark:border-indigo-600 dark:ring-indigo-500'
                : 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/30'"
            x-on:dragenter.prevent
            x-on:dragover.prevent="canEditStage(stage) && onDragOver($event, stage)"
            x-on:dragleave="onDragLeave()"
            x-on:drop.prevent="canEditStage(stage) && onDrop(stage)"
        >
            <header
                class="sticky top-0 z-10 flex items-center justify-between rounded-t-2xl px-4 py-3 font-semibold shadow-sm"
                :style="'background-color:' + (stageConfig[stage]?.accent ?? '#6b7280')"
            >
                <div class="flex min-w-0 items-center gap-2 text-white">
                    <span class="shrink-0 text-base" x-text="stageConfig[stage]?.icon ?? ''"></span>
                    <span class="truncate text-sm font-semibold" x-text="stageConfig[stage]?.label ?? stage"></span>
                </div>

                <div class="flex shrink-0 items-center gap-1.5">
                    <span
                        class="rounded-full bg-black/20 px-2 py-0.5 text-xs font-normal tabular-nums text-white/80"
                        x-text="getDealsByStage(stage).length"
                    ></span>
                    <template x-if="isSalesUser && !canEditStage(stage)">
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px] text-white">Locked</span>
                    </template>
                </div>
            </header>

            <div class="sticky top-[60px] z-10 border-b border-slate-200 bg-white/70 px-4 py-2 text-xs font-semibold backdrop-blur-sm dark:border-slate-700 dark:bg-slate-800/40">
                <span
                    :style="'color:' + (stageConfig[stage]?.accentText ?? 'inherit')"
                    x-text="'\u00a3' + getStageSum(stage).toLocaleString('en-GB', { maximumFractionDigits: 0 }) + ' total TSV'"
                ></span>
            </div>

            <div :id="'kanban-col-' + stage" class="flex-1 space-y-2 overflow-y-auto p-2 md:p-3">
                <template x-if="getDealsByStage(stage).length === 0">
                    <div class="flex h-full select-none flex-col items-center justify-center text-slate-400 dark:text-slate-600">
                        <svg class="mb-2 h-8 w-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-xs">No deals</p>
                        <template x-if="canEditStage(stage)">
                            <p class="mt-1 text-[10px] opacity-60">Drop here to move</p>
                        </template>
                    </div>
                </template>

                <template x-for="deal in getDealsByStage(stage)" :key="deal.id">
                    <article
                        :data-deal-id="deal.id"
                        class="relative flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition-all duration-150 hover:border-slate-300 hover:shadow-md active:cursor-grabbing dark:border-slate-700 dark:bg-slate-800 dark:hover:border-slate-600"
                        :class="{ 'opacity-60': draggingId === deal.id }"
                        :draggable="canEditStage(stage)"
                        x-on:dragstart.stop="onDragStart(deal.id, stage, $event)"
                        x-on:dragend="resetDrag()"
                        x-on:click="openDeal($event, deal.id)"
                    >
                        <div class="absolute bottom-3 left-0 top-3 w-[3px] rounded-r-full" :style="'background-color:' + (stageConfig[deal.stage]?.accent ?? '#94a3b8')"></div>

                        <div class="shrink-0 select-none pt-0.5 text-sm text-slate-300 transition dark:text-slate-600">
                            <span aria-hidden="true">::</span>
                        </div>

                        <div class="min-w-0 flex-1 pl-1">
                            <p class="mb-2 truncate text-sm font-semibold text-slate-900 transition hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400" x-text="deal.name"></p>

                            <div class="mb-3 flex items-center justify-between gap-2">
                                <span class="text-sm font-bold tabular-nums text-slate-800 dark:text-white" x-text="'\u00a3' + Number(deal.amount || 0).toLocaleString('en-GB', { maximumFractionDigits: 0 })"></span>
                                <template x-if="deal.internal_company">
                                    <span class="max-w-[80px] shrink-0 truncate rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400" x-text="deal.internal_company"></span>
                                </template>
                            </div>

                            <div class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                                <div class="flex items-center gap-1.5">
                                    <flux:icon.calendar-days class="h-3.5 w-3.5 shrink-0 opacity-70" />
                                    <span x-text="timeAgo(deal.created_at)"></span>
                                </div>

                                <template x-if="deal.contacts?.[0]">
                                    <div class="flex items-center gap-1.5">
                                        <flux:icon.user class="h-3.5 w-3.5 shrink-0 opacity-70" />
                                        <span class="truncate" x-text="contactName(deal.contacts[0])"></span>
                                    </div>
                                </template>

                                <template x-if="deal.companies?.[0]">
                                    <div class="flex items-center gap-1.5">
                                        <flux:icon.building-office class="h-3.5 w-3.5 shrink-0 opacity-70" />
                                        <span class="truncate" x-text="deal.companies[0].name"></span>
                                    </div>
                                </template>

                                <div class="flex items-center gap-1.5 pt-0.5">
                                    <flux:icon.identification class="h-3.5 w-3.5 shrink-0 opacity-70" />
                                    <span class="truncate font-medium" x-text="deal.user?.name ?? 'Unassigned'"></span>
                                </div>
                            </div>
                        </div>
                    </article>
                </template>
            </div>

            <template x-if="isSalesUser && !canEditStage(stage)">
                <div class="rounded-b-2xl border-t border-amber-100 bg-amber-50 px-3 py-2 text-center text-[11px] text-amber-700 dark:border-amber-900/30 dark:bg-amber-900/20 dark:text-amber-300">
                    Managed by Compliance
                </div>
            </template>
        </section>
    </template>
</div>

<script>
    function kanbanBoard({ deals, stages, stageConfig, editableStages, isSalesUser, showUrlTemplate }) {
        return {
            deals,
            stages,
            stageConfig,
            editableStages: new Set(editableStages),
            isSalesUser,
            showUrlTemplate,
            draggingId: null,
            draggingStage: null,
            dragOverStage: null,

            init() {
                this.$watch('$wire.deals', (freshDeals) => {
                    this.deals = Array.isArray(freshDeals) ? freshDeals : [];
                });
            },

            canEditStage(stage) {
                return this.editableStages.has(stage);
            },

            getDealsByStage(stage) {
                return this.deals.filter((deal) => deal.stage === stage);
            },

            getStageSum(stage) {
                return this.getDealsByStage(stage).reduce((sum, deal) => sum + (Number(deal.amount) || 0), 0);
            },

            contactName(contact) {
                return [contact.first_name, contact.last_name].filter(Boolean).join(' ') || 'Unknown contact';
            },

            timeAgo(date) {
                if (!date) {
                    return '';
                }

                const diff = Date.now() - new Date(date).getTime();
                const minutes = Math.floor(diff / 60000);
                const hours = Math.floor(diff / 3600000);
                const days = Math.floor(diff / 86400000);

                if (minutes < 1) {
                    return 'just now';
                }

                if (minutes < 60) {
                    return `${minutes} minutes ago`;
                }

                if (hours < 24) {
                    return `${hours} hours ago`;
                }

                if (days < 30) {
                    return `${days} days ago`;
                }

                return new Date(date).toLocaleDateString('en-GB');
            },

            openDeal(event, dealId) {
                if (this.draggingId || event.target.closest('a, button, input, select')) {
                    return;
                }

                window.location.href = this.showUrlTemplate.replace('__deal__', dealId);
            },

            onDragStart(dealId, stage, event) {
                if (!this.canEditStage(stage)) {
                    event.preventDefault();
                    return;
                }

                this.draggingId = dealId;
                this.draggingStage = stage;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', String(dealId));
            },

            onDragOver(event, stage) {
                event.dataTransfer.dropEffect = 'move';
                this.dragOverStage = stage;
            },

            onDragLeave() {
                this.dragOverStage = null;
            },

            async onDrop(targetStage) {
                this.dragOverStage = null;

                if (!this.draggingId || this.draggingStage === targetStage) {
                    this.resetDrag();
                    return;
                }

                const dealId = this.draggingId;
                const fromStage = this.draggingStage;
                const deal = this.deals.find((item) => item.id === dealId);

                this.resetDrag();

                if (!deal) {
                    return;
                }

                deal.stage = targetStage;

                try {
                    await this.$wire.updateStage(dealId, targetStage);
                } catch (error) {
                    deal.stage = fromStage;
                }
            },

            resetDrag() {
                this.draggingId = null;
                this.draggingStage = null;
            },
        };
    }
</script>
