{{-- components/deals/partials/⚡kanban.blade.php --}}

@php
    $isSalesUser = $this->isSalesTeam();
    // A cache key that changes when filters change — forces a fresh fetch when
    // the user applies new filters, while serving the old data instantly in between.
    $cacheKey =
        'kanban_deals_' .
        md5(
            json_encode([
                $this->filterDealName,
                $this->filterOwner,
                $this->filterContact,
                $this->filterCompanyName,
                $this->filterStage,
                $this->minAmount,
                $this->maxAmount,
                $this->dateFrom,
                $this->dateTo,
                auth()->id(),
            ]),
        );
@endphp

<div x-data="kanbanBoard({
    livewireDeals: {{ Js::from($this->deals) }},
    stages: {{ Js::from($this->stages) }},
    stageConfig: {{ Js::from($stageConfig) }},
    cacheKey: '{{ $cacheKey }}',
    isSalesUser: {{ $isSalesUser ? 'true' : 'false' }},
    editableStages: {{ Js::from(array_filter($this->stages, fn($s) => $this->canEditStage($s))) }},
})" x-init="init()"
    class="
        flex gap-3
        overflow-x-auto overflow-y-hidden
        pb-4 px-2
        min-h-[650px]
        snap-x snap-mandatory
        scrollbar-thin
    ">

    <template x-for="stage in stages" :key="stage">
        <div :data-stage="stage"
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
                'ring-2 ring-indigo-400 dark:ring-indigo-500 ring-offset-1 border-indigo-300 dark:border-indigo-600': dragOverStage ===
                    stage,
                'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30': dragOverStage !== stage
            }"
            x-on:dragover.prevent="canEditStage(stage) && onDragOver($event, stage)" x-on:dragleave="onDragLeave()"
            x-on:dragenter.prevent x-on:drop.prevent="canEditStage(stage) && onDrop($event, stage)">

            {{-- Header --}}
            <div class="
                    px-4 py-3 rounded-t-2xl font-semibold
                    flex items-center justify-between
                    sticky top-0 z-10 shadow-sm
                "
                :style="'background-color:' + (stageConfig[stage]?.accent ?? '#6b7280')">
                <div class="flex items-center gap-2 text-white min-w-0">
                    <span class="text-base shrink-0" x-text="stageConfig[stage]?.icon ?? '•'"></span>
                    <span class="text-sm font-semibold truncate" x-text="stageConfig[stage]?.label ?? stage"></span>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-white/80 text-xs tabular-nums font-normal bg-black/20 rounded-full px-2 py-0.5"
                        x-text="getDealsByStage(stage).length"></span>

                    <template x-if="isSalesUser && !canEditStage(stage)">
                        <span class="text-[10px] bg-white/20 text-white px-2 py-0.5 rounded-full">🔒</span>
                    </template>
                </div>
            </div>

            {{-- Total --}}
            <div
                class="
                px-4 py-2 text-xs font-semibold border-b
                border-slate-200 dark:border-slate-700
                bg-white/70 dark:bg-slate-800/40
                backdrop-blur-sm sticky top-[60px] z-10
            ">
                <span :style="'color:' + (stageConfig[stage]?.accentText ?? 'inherit')"
                    x-text="'£' + getStageSum(stage).toLocaleString('en-GB', {maximumFractionDigits:0}) + ' total TSV'"></span>
            </div>

            {{-- Cards --}}
            <div :id="'kanban-col-' + stage" class="flex-1 overflow-y-auto p-2 md:p-3 space-y-2">
                <template x-if="getDealsByStage(stage).length === 0">
                    <div
                        class="flex flex-col items-center justify-center h-full text-slate-400 dark:text-slate-600 select-none">
                        <svg class="w-8 h-8 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-xs">No deals</p>
                        <template x-if="canEditStage(stage)">
                            <p class="text-[10px] mt-1 opacity-60">Drop here to move</p>
                        </template>
                    </div>
                </template>

                <template x-for="deal in getDealsByStage(stage)" :key="deal.id">
                    <div :data-deal-id="deal.id" class="transition-transform duration-200"
                        :draggable="canEditStage(stage) ? 'true' : 'false'"
                        x-on:dragstart.stop="canEditStage(stage) && onDragStart(deal.id, stage, $event)"
                        x-on:dragend="resetDrag()">
                        {{-- Inline card (Alpine-rendered, no Livewire re-render) --}}
                        <div class="relative bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
                                   hover:border-slate-300 dark:hover:border-slate-600 rounded-xl p-3.5
                                   flex items-start gap-3 shadow-sm cursor-pointer active:cursor-grabbing
                                   group transition-all duration-150 hover:shadow-md"
                            x-on:click="handleCardClick($event, deal.id)">
                            {{-- Left accent bar --}}
                            <div class="absolute left-0 top-3 bottom-3 w-[3px] rounded-r-full"
                                :style="'background-color:' + stageConfig[deal.stage]?.accent"></div>

                            {{-- Drag handle --}}
                            <div
                                class="text-slate-300 dark:text-slate-600 group-hover:text-slate-500
                                        dark:group-hover:text-slate-400 transition text-sm pt-0.5 select-none shrink-0">
                                ⠿</div>

                            <div class="flex-1 min-w-0 pl-1">
                                {{-- Name --}}
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100
                                          group-hover:text-indigo-600 dark:group-hover:text-indigo-400
                                          transition truncate mb-2"
                                    x-text="deal.name"></p>

                                {{-- Amount + internal company --}}
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="text-sm font-bold text-slate-800 dark:text-white tabular-nums"
                                        x-text="'£' + Number(deal.amount).toLocaleString('en-GB', {maximumFractionDigits:0})"></span>
                                    <template x-if="deal.internal_company">
                                        <span
                                            class="text-[10px] font-semibold px-1.5 py-0.5 rounded border
                                                     border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                                                     text-slate-500 dark:text-slate-400 shrink-0 truncate max-w-[80px]"
                                            x-text="deal.internal_company"></span>
                                    </template>
                                </div>

                                {{-- Meta --}}
                                <div class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{-- Created --}}
                                    <div class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 opacity-70"
                                            viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M7 2h1a1 1 0 0 1 1 1v1h5V3a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a3 3 0 0 1 3 3v11a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3V3a1 1 0 0 1 1-1m8 2h1V3h-1zM8 4V3H7v1zM6 5a2 2 0 0 0-2 2v1h15V7a2 2 0 0 0-2-2zM4 18a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V9H4zm8-5h5v5h-5zm1 1v3h3v-3z" />
                                        </svg>
                                        <span x-text="timeAgo(deal.created_at)"></span>
                                    </div>

                                    {{-- Contact --}}
                                    <template x-if="deal.contacts && deal.contacts[0]">
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-3.5 h-3.5 shrink-0 opacity-70" viewBox="0 0 24 24"
                                                fill="currentColor">
                                                <path
                                                    d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
                                            </svg>
                                            <span class="truncate"
                                                x-text="deal.contacts[0].first_name + ' ' + deal.contacts[0].last_name"></span>
                                        </div>
                                    </template>

                                    {{-- Company --}}
                                    <template x-if="deal.companies && deal.companies[0]">
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                                                viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M0 0h24v24H0z" fill="none" />
                                                <path fill="currentColor"
                                                    d="M19 3v18h-6v-3.5h-2V21H5V3zm-4 4h2V5h-2zm-4 0h2V5h-2zM7 7h2V5H7zm8 4h2V9h-2zm-4 0h2V9h-2zm-4 0h2V9H7zm8 4h2v-2h-2zm-4 0h2v-2h-2zm-4 0h2v-2H7zm8 4h2v-2h-2zm-8 0h2v-2H7zM21 1H3v22h18z" />
                                            </svg>
                                            <span class="truncate" x-text="deal.companies[0].name"></span>
                                        </div>
                                    </template>

                                    {{-- Owner --}}
                                    <div class="flex items-center gap-1.5 pt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                                            viewBox="0 0 16 16">
                                            <path d="M0 0h16v16H0z" fill="none" />
                                            <path fill="currentColor"
                                                d="M11 7c0 1.66-1.34 3-3 3S5 8.66 5 7s1.34-3 3-3s3 1.34 3 3" />
                                            <path fill="currentColor" fill-rule="evenodd"
                                                d="M16 8c0 4.42-3.58 8-8 8s-8-3.58-8-8s3.58-8 8-8s8 3.58 8 8M4 13.75C4.16 13.484 5.71 11 7.99 11c2.27 0 3.83 2.49 3.99 2.75A6.98 6.98 0 0 0 14.99 8c0-3.87-3.13-7-7-7s-7 3.13-7 7c0 2.38 1.19 4.49 3.01 5.75"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="truncate font-medium"
                                            x-text="deal.user ? deal.user.name : 'Unassigned'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Compliance footer --}}
            <template x-if="isSalesUser && !canEditStage(stage)">
                <div
                    class="
                    px-3 py-2 text-[11px] text-center
                    text-amber-700 dark:text-amber-300
                    bg-amber-50 dark:bg-amber-900/20
                    rounded-b-2xl border-t
                    border-amber-100 dark:border-amber-900/30
                ">
                    Managed by Compliance</div>
            </template>
        </div>
    </template>
</div>

<script>
    /**
     * Kanban board Alpine component.
     *
     * Strategy:
     *  1. On init — serve deals from localStorage immediately (zero wait).
     *     Livewire's server-rendered $deals are merged in as the authoritative set.
     *  2. On drag-drop — move the card in local state instantly (optimistic).
     *     Fire $wire.updateStage() silently; roll back + shake on failure.
     *  3. After every server update — persist the mutated deals array back to
     *     localStorage so the next page load is instant again.
     */
    function kanbanBoard({
        livewireDeals,
        stages,
        stageConfig,
        cacheKey,
        isSalesUser,
        editableStages
    }) {

        // ─── localStorage helpers ──────────────────────────────────────────────────
        const CACHE_VERSION = 1;
        const MAX_CACHE_AGE_MS = 10 * 60 * 1000; // 10 minutes

        function readCache() {
            try {
                const raw = localStorage.getItem(cacheKey);
                if (!raw) return null;
                const {
                    v,
                    ts,
                    deals
                } = JSON.parse(raw);
                if (v !== CACHE_VERSION) return null;
                if (Date.now() - ts > MAX_CACHE_AGE_MS) return null;
                return deals;
            } catch {
                return null;
            }
        }

        function writeCache(deals) {
            try {
                localStorage.setItem(cacheKey, JSON.stringify({
                    v: CACHE_VERSION,
                    ts: Date.now(),
                    deals,
                }));
            } catch {
                /* quota exceeded — silently skip */
            }
        }

        /**
         * Merge server deals into the local set.
         * - Any deal from the server overwrites the local copy (server is authoritative).
         * - Deals present locally but NOT on the server are kept (they may be
         *   loaded from a previous "load more" batch not re-fetched this cycle).
         * - Deals whose id is not in the server set at all will be pruned after the
         *   next full reload — we don't remove them here to avoid visual flicker.
         */
        function mergeDeals(local, incoming) {
            const map = new Map(local.map(d => [d.id, d]));
            for (const d of incoming) map.set(d.id, d); // server wins
            return Array.from(map.values());
        }

        // ─── Component ────────────────────────────────────────────────────────────
        return {
            deals: [],
            stages,
            stageConfig,
            isSalesUser,
            editableStages: new Set(editableStages),

            // drag state
            draggingId: null,
            draggingStage: null,
            dragOverStage: null,

            // track if this is the first load after view switch
            isFirstLoad: true,

            // ── Lifecycle ──────────────────────────────────────────────────────────
            init() {
                // Listen for view change events from Livewire
                this.$wire.$on('view-changed', (event) => {
                    if (event.view === 'kanban') {
                        // Clear cache for this filter set to force fresh data
                        try {
                            localStorage.removeItem(cacheKey);
                        } catch (e) {}

                        // Force reload from Livewire data
                        this.deals = livewireDeals;
                        if (this.deals && this.deals.length > 0) {
                            writeCache(this.deals);
                        }
                    }
                });

                // 1. Serve from cache immediately — board appears with no wait.
                const cached = readCache();

                // Only use cache if we have deals AND it's not the first load after view switch
                if (cached && cached.length > 0 && !this.isFirstLoad) {
                    this.deals = cached;
                } else {
                    this.deals = livewireDeals;
                    if (livewireDeals && livewireDeals.length > 0) {
                        writeCache(this.deals);
                    }
                }

                this.isFirstLoad = false;

                // 2. Merge in the authoritative server data (may be identical).
                //    Use $nextTick so the cached board paints first.
                this.$nextTick(() => {
                    if (livewireDeals && livewireDeals.length > 0) {
                        this.deals = mergeDeals(this.deals, livewireDeals);
                        writeCache(this.deals);
                    }
                });

                // 3. Watch for Livewire updates (e.g. load-more, filter changes).
                //    When Livewire re-renders it calls $wire.on; we watch the deals property.
                this.$watch('$wire.deals', (fresh) => {
                    if (fresh && fresh.length > 0) {
                        this.deals = mergeDeals(this.deals, fresh);
                        writeCache(this.deals);
                    } else if (fresh && fresh.length === 0 && this.deals.length > 0) {
                        // If server returns empty but we have deals, keep what we have
                        console.log('Server returned empty deals, keeping cached data');
                    } else if (fresh) {
                        this.deals = fresh;
                        writeCache(this.deals);
                    }
                });

                // Also watch for the specific deals-updated event
                this.$wire.$on('deals-updated', () => {
                    // Just refresh cache with current deals
                    if (this.deals && this.deals.length > 0) {
                        writeCache(this.deals);
                    }
                });
            },

            // ── Helpers ────────────────────────────────────────────────────────────
            canEditStage(stage) {
                return this.editableStages.has(stage);
            },

            getDealsByStage(stage) {
                return this.deals.filter(d => d.stage === stage);
            },

            getStageSum(stage) {
                return this.getDealsByStage(stage)
                    .reduce((sum, d) => sum + (parseFloat(d.amount) || 0), 0);
            },

            timeAgo(dateStr) {
                if (!dateStr) return '';
                const diff = Date.now() - new Date(dateStr).getTime();
                const mins = Math.floor(diff / 60000);
                const hours = Math.floor(diff / 3600000);
                const days = Math.floor(diff / 86400000);
                if (mins < 1) return 'just now';
                if (mins < 60) return mins + ' minutes ago';
                if (hours < 24) return hours + ' hours ago';
                if (days < 30) return days + ' days ago';
                return new Date(dateStr).toLocaleDateString('en-GB');
            },

            handleCardClick(event, dealId) {
                if (event.target.closest('a')) return;
                if (this.draggingId) return;
                window.location.href = `/deals/${dealId}`;
            },

            // ── Drag & Drop ────────────────────────────────────────────────────────
            onDragStart(dealId, stage, event) {
                this.draggingId = dealId;
                this.draggingStage = stage;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', String(dealId));
            },

            onDragOver(event, targetStage) {
                event.dataTransfer.dropEffect = 'move';
                this.dragOverStage = targetStage;
            },

            onDragLeave() {
                this.dragOverStage = null;
            },

            async onDrop(event, targetStage) {
                this.dragOverStage = null;

                if (!this.draggingId || this.draggingStage === targetStage) {
                    this.resetDrag();
                    return;
                }

                const dealId = this.draggingId;
                const fromStage = this.draggingStage;
                this.resetDrag();

                // ── Optimistic update — move the card in local state immediately ──
                const deal = this.deals.find(d => d.id === dealId);
                if (!deal) return;
                deal.stage = targetStage;

                // Persist the optimistic state so a refresh doesn't revert it
                writeCache(this.deals);

                // ── Fire server update in the background ──────────────────────────
                try {
                    await this.$wire.updateStage(dealId, targetStage);
                    // updateStage already patches $this->deals on the server side;
                    // Livewire will trigger a re-render and sync back.
                } catch (err) {
                    // ── Rollback on failure ───────────────────────────────────────
                    const revert = this.deals.find(d => d.id === dealId);
                    if (revert) {
                        revert.stage = fromStage;
                        writeCache(this.deals);
                    }

                    // Shake the card to signal failure
                    const card = document.querySelector(`[data-deal-id='${dealId}']`);
                    if (card) {
                        card.style.transition = 'transform .1s ease';
                        const shake = [0, -6, 6, -4, 4, 0]
                            .map((x, i) => setTimeout(() => card.style.transform = `translateX(${x}px)`, i * 60));
                        setTimeout(() => card.style.transform = '', 6 * 60 + 50);
                    }
                }
            },

            resetDrag() {
                this.draggingId = null;
                this.draggingStage = null;
            },
        };
    }
</script>
