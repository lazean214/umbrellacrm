<?php

use Livewire\Component;
use App\Models\Deal;
use Illuminate\Support\Str;
use App\Enums\DealStage;

new class extends Component
{
    public $deals = [];
    public $view = 'kanban'; // 'kanban' or 'table'
    public array $stages = [];

    // --- CRM Live Filter States ---
    public string $filterDealName    = '';
    public string $filterOwner       = '';
    public string $filterContact     = '';
    public string $filterCompanyName = '';
    public string $filterStage       = '';
    public $minAmount   = null;
    public $maxAmount   = null;
    public $dateFrom    = null;
    public $dateTo      = null;

    public function updatedFilterDealName()    { $this->loadDeals(); }
    public function updatedFilterOwner()       { $this->loadDeals(); }
    public function updatedFilterContact()     { $this->loadDeals(); }
    public function updatedFilterCompanyName() { $this->loadDeals(); }
    public function updatedFilterStage()       { $this->loadDeals(); }
    public function updatedMinAmount()         { $this->loadDeals(); }
    public function updatedMaxAmount()         { $this->loadDeals(); }
    public function updatedDateFrom()          { $this->loadDeals(); }
    public function updatedDateTo()            { $this->loadDeals(); }

    public function mount()
    {
        $this->stages = array_map(fn($s) => $s->value, [
            DealStage::DOC_SENT,
            DealStage::DOC_SIGNED,
            DealStage::COMPLIANT,
            DealStage::READY_FOR_PAYMENT,
            DealStage::PAID,
        ]);
        $this->loadDeals();
    }

    public function loadDeals()
    {
        $query = Deal::query()->with('contacts', 'companies', 'user');

        // Deal name
        if (!empty($this->filterDealName)) {
            $query->where('name', 'like', '%' . $this->filterDealName . '%');
        }

        // Deal owner (user name)
        if (!empty($this->filterOwner)) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', '%' . $this->filterOwner . '%')
            );
        }

        // Contact name
        if (!empty($this->filterContact)) {
            $query->whereHas('contacts', fn($q) =>
                $q->where(\DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->filterContact . '%')
            );
        }

        // Company name (related companies)
        if (!empty($this->filterCompanyName)) {
            $query->whereHas('companies', fn($q) =>
                $q->where('name', 'like', '%' . $this->filterCompanyName . '%')
            );
        }

        // Stage
        if (!empty($this->filterStage)) {
            $query->where('stage', $this->filterStage);
        }

        // Amount range
        if (!is_null($this->minAmount) && $this->minAmount !== '') {
            $query->where('amount', '>=', $this->minAmount);
        }
        if (!is_null($this->maxAmount) && $this->maxAmount !== '') {
            $query->where('amount', '<=', $this->maxAmount);
        }

        // Created date range
        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $this->deals = $query->orderBy('created_at', 'desc')->get()->map(function ($deal) {
            $arr = $deal->toArray();
            $arr['stage'] = $deal->stage instanceof \BackedEnum
                ? $deal->stage->value
                : (string) $deal->stage;
            return $arr;
        })->toArray();
    }

    public function resetFilters()
    {
        $this->reset([
            'filterDealName', 'filterOwner', 'filterContact',
            'filterCompanyName', 'filterStage',
            'minAmount', 'maxAmount', 'dateFrom', 'dateTo',
        ]);
        $this->loadDeals();
    }

    public function hasActiveFilters(): bool
    {
        return !empty($this->filterDealName)
            || !empty($this->filterOwner)
            || !empty($this->filterContact)
            || !empty($this->filterCompanyName)
            || !empty($this->filterStage)
            || ($this->minAmount !== null && $this->minAmount !== '')
            || ($this->maxAmount !== null && $this->maxAmount !== '')
            || !empty($this->dateFrom)
            || !empty($this->dateTo);
    }

    public function updateStage($dealId, $newStage)
    {
        $deal = Deal::findOrFail($dealId);
        $deal->stage = DealStage::from($newStage);
        $deal->save();
        $this->loadDeals();
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function getDealsByStage($stage)
    {
        return collect($this->deals)->where('stage', $stage)->values();
    }

    public function getStageSum($stage)
    {
        return collect($this->deals)->where('stage', $stage)->sum('amount');
    }
};

?>

@php
/**
 * Stage color config — keys match DealStage enum values exactly.
 * DealStage values use spaces: 'doc sent', 'doc signed', etc.
 */
$stageConfig = [
    'doc sent' => [
        'accent'      => '#4f46e5',
        'accentLight' => 'rgba(79,70,229,0.12)',
        'accentText'  => '#3730a3',
        'icon'        => '📄',
        'label'       => 'Doc Sent',
    ],
    'doc signed' => [
        'accent'      => '#0891b2',
        'accentLight' => 'rgba(8,145,178,0.12)',
        'accentText'  => '#155e75',
        'icon'        => '✍️',
        'label'       => 'Doc Signed',
    ],
    'compliant' => [
        'accent'      => '#d97706',
        'accentLight' => 'rgba(217,119,6,0.12)',
        'accentText'  => '#92400e',
        'icon'        => '✅',
        'label'       => 'Compliant',
    ],
    'ready for payment' => [
        'accent'      => '#ea580c',
        'accentLight' => 'rgba(234,88,12,0.12)',
        'accentText'  => '#9a3412',
        'icon'        => '💳',
        'label'       => 'Ready for Payment',
    ],
    'paid' => [
        'accent'      => '#16a34a',
        'accentLight' => 'rgba(22,163,74,0.12)',
        'accentText'  => '#14532d',
        'icon'        => '💰',
        'label'       => 'Paid',
    ],
];
@endphp

<div
    x-data="{
        draggingId: null,
        draggingStage: null,
        onDragStart(dealId, stage) {
            this.draggingId = dealId;
            this.draggingStage = stage;
        },
        onDrop(targetStage) {
            if (this.draggingId !== null && this.draggingStage !== targetStage) {
                $wire.updateStage(this.draggingId, targetStage);
            }
            this.draggingId = null;
            this.draggingStage = null;
        },
        onDragOver(e) { e.preventDefault(); }
    }"
    class="space-y-6 w-full mx-auto p-4 sm:p-6 lg:p-8 antialiased text-slate-900 dark:text-slate-100"
>
    {{-- Loading bar --}}
    <div wire:loading.delay class="fixed top-0 left-0 right-0 h-0.5 bg-indigo-600 dark:bg-indigo-400 z-50 animate-pulse"></div>

    {{-- ── Header ── --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 dark:border-slate-800 pb-5">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white tracking-tight">
                {{ __('Deals Pipeline') }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Manage your pipeline tracking, stage workflows, and incoming financial volumes.
            </p>
        </div>

        {{-- View toggle --}}
        <div class="inline-flex rounded-lg shadow-sm bg-slate-100 dark:bg-slate-800 p-1 self-start sm:self-center gap-0.5">
            <button
                wire:click="setView('kanban')"
                class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium rounded-md transition-all duration-150
                    {{ $view === 'kanban'
                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm ring-1 ring-slate-200/60 dark:ring-slate-600/40'
                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}"
            >
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor">
                    <rect x="1" y="1" width="4" height="14" rx="1.5"/>
                    <rect x="6" y="1" width="4" height="14" rx="1.5"/>
                    <rect x="11" y="1" width="4" height="14" rx="1.5"/>
                </svg>
                Kanban
            </button>
            <button
                wire:click="setView('table')"
                class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium rounded-md transition-all duration-150
                    {{ $view === 'table'
                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm ring-1 ring-slate-200/60 dark:ring-slate-600/40'
                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}"
            >
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor">
                    <rect x="1" y="1" width="14" height="3" rx="1.5"/>
                    <rect x="1" y="6" width="14" height="3" rx="1.5"/>
                    <rect x="1" y="11" width="14" height="3" rx="1.5"/>
                </svg>
                Table
            </button>
        </div>
    </div>

    {{-- ── Filters Panel ── --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">

        {{-- Filter header bar --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filters
                @if($this->hasActiveFilters())
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-indigo-600 text-white text-[10px] font-bold">
                        {{ collect([
                            $filterDealName, $filterOwner, $filterContact, $filterCompanyName,
                            $filterStage,
                            ($minAmount !== null && $minAmount !== '') ? '1' : '',
                            ($maxAmount !== null && $maxAmount !== '') ? '1' : '',
                            $dateFrom, $dateTo,
                        ])->filter()->count() }}
                    </span>
                @endif
            </div>
            @if($this->hasActiveFilters())
                <button
                    wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 transition"
                >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Clear all
                </button>
            @endif
        </div>

        {{-- Filter grid body --}}
        <div class="p-5 space-y-4">

            {{-- Row 1: Deal Name · Owner · Contact --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Deal Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="filterDealName"
                            placeholder="Search deal name…"
                            class="block w-full pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 dark:focus:border-indigo-400 transition"
                        >
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Deal Owner</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="filterOwner"
                            placeholder="Owner name…"
                            class="block w-full pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 dark:focus:border-indigo-400 transition"
                        >
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Contact Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="filterContact"
                            placeholder="Contact name…"
                            class="block w-full pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 dark:focus:border-indigo-400 transition"
                        >
                    </div>
                </div>

            </div>

            {{-- Row 2: Company · Internal Entity · Stage --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Company Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="filterCompanyName"
                            placeholder="Company name…"
                            class="block w-full pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 dark:focus:border-indigo-400 transition"
                        >
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Stage</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <select
                            wire:model.live="filterStage"
                            class="block w-full pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition appearance-none"
                        >
                            <option value="">All stages</option>
                            @foreach($stages as $s)
                                <option value="{{ $s }}">{{ ucwords($s) }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Row 3: Amount range · Date range --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Amount Range (£)</label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs font-medium">£</div>
                            <input
                                type="number"
                                wire:model.live.debounce.300ms="minAmount"
                                placeholder="Min"
                                min="0"
                                class="block w-full pl-6 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                            >
                        </div>
                        <span class="text-slate-300 dark:text-slate-600 text-sm shrink-0">→</span>
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs font-medium">£</div>
                            <input
                                type="number"
                                wire:model.live.debounce.300ms="maxAmount"
                                placeholder="Max"
                                min="0"
                                class="block w-full pl-6 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                            >
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Created Date Range</label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input
                                type="date"
                                wire:model.live="dateFrom"
                                class="block w-full pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                            >
                        </div>
                        <span class="text-slate-300 dark:text-slate-600 text-sm shrink-0">→</span>
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input
                                type="date"
                                wire:model.live="dateTo"
                                class="block w-full pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                            >
                        </div>
                    </div>
                </div>

            </div>

            {{-- Active filter chips --}}
            @if($this->hasActiveFilters())
            <div class="flex flex-wrap gap-2 pt-1">
                @if($filterDealName)
                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                        Deal: <strong>{{ $filterDealName }}</strong>
                        <button wire:click="$set('filterDealName', '')" class="hover:text-indigo-900 dark:hover:text-indigo-100 transition">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                @endif
                @if($filterOwner)
                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-violet-50 dark:bg-violet-950/40 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-800/60">
                        Owner: <strong>{{ $filterOwner }}</strong>
                        <button wire:click="$set('filterOwner', '')" class="hover:text-violet-900 dark:hover:text-violet-100 transition">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                @endif
                @if($filterContact)
                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60">
                        Contact: <strong>{{ $filterContact }}</strong>
                        <button wire:click="$set('filterContact', '')" class="hover:text-sky-900 dark:hover:text-sky-100 transition">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                @endif
                @if($filterCompanyName)
                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60">
                        Company: <strong>{{ $filterCompanyName }}</strong>
                        <button wire:click="$set('filterCompanyName', '')" class="hover:text-teal-900 dark:hover:text-teal-100 transition">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                @endif
              
                @if($filterStage)
                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-800/60">
                        Stage: <strong>{{ ucwords($filterStage) }}</strong>
                        <button wire:click="$set('filterStage', '')" class="hover:text-orange-900 dark:hover:text-orange-100 transition">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                @endif
                @if($minAmount !== null && $minAmount !== '' || $maxAmount !== null && $maxAmount !== '')
                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                        Amount: <strong>£{{ $minAmount ?? '0' }} – {{ $maxAmount ? '£'.$maxAmount : '∞' }}</strong>
                        <button wire:click="$set('minAmount', null); $wire.set('maxAmount', null)" class="hover:text-emerald-900 dark:hover:text-emerald-100 transition">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                @endif
                @if($dateFrom || $dateTo)
                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60">
                        Date: <strong>{{ $dateFrom ?? '…' }} – {{ $dateTo ?? '…' }}</strong>
                        <button wire:click="$set('dateFrom', null); $wire.set('dateTo', null)" class="hover:text-rose-900 dark:hover:text-rose-100 transition">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                @endif
            </div>
            @endif

        </div>
    </div>

    {{-- ══════════════════════════════════════
         KANBAN BOARD VIEW
    ══════════════════════════════════════ --}}
    @if($view === 'kanban')
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-start">
        @foreach($stages as $stage)
        @php
            $stageDeals = $this->getDealsByStage($stage);
            $stageSum   = $this->getStageSum($stage);
            $cfg        = $stageConfig[$stage] ?? [
                'accent' => '#64748b', 'accentLight' => 'rgba(100,116,139,0.10)',
                'accentText' => '#475569', 'icon' => '🔹',
                'label' => ucwords($stage),
            ];
        @endphp
        <div
            class="flex flex-col bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl min-h-[480px] transition-all duration-200 overflow-hidden"
            x-on:dragover="onDragOver($event)"
            x-on:drop="onDrop('{{ $stage }}')"
            :class="{ 'ring-2 ring-indigo-500/60 dark:ring-indigo-400/60 bg-indigo-50/40 dark:bg-indigo-950/20 scale-[1.01]': draggingId !== null && draggingStage !== '{{ $stage }}' }"
        >
            {{-- Column header with color top bar --}}
            <div class="relative pt-1">
                {{-- Top accent bar — rendered via inline style, guaranteed to display --}}
                <div class="h-1 w-full" style="background-color: {{ $cfg['accent'] }};"></div>

                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-base leading-none">{{ $cfg['icon'] }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider" style="color: {{ $cfg['accent'] }};">
                                {{ $cfg['label'] }}
                            </span>
                        </div>
                        {{-- Count badge --}}
                        <span
                            class="text-xs font-bold px-2 py-0.5 rounded-full"
                            style="background-color: {{ $cfg['accentLight'] }}; color: {{ $cfg['accent'] }};"
                        >
                            {{ $stageDeals->count() }}
                        </span>
                    </div>
                    {{-- Stage sum --}}
                    <div class="text-base font-bold text-slate-800 dark:text-slate-100 tabular-nums">
                        £{{ number_format($stageSum, 0) }}
                    </div>
                </div>
            </div>

            {{-- Cards --}}
            <div class="flex-1 flex flex-col gap-3 p-3">
                @forelse($stageDeals as $deal)
                <div
                    class="relative bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 rounded-xl p-3.5 flex items-start gap-3 shadow-sm cursor-grab active:cursor-grabbing group transition-all duration-150 hover:shadow-md"
                    draggable="true"
                    x-on:dragstart="onDragStart({{ $deal['id'] }}, '{{ $deal['stage'] }}')"
                    x-on:dragend="draggingId = null"
                    wire:key="card-{{ $deal['id'] }}"
                >
                    {{-- Left accent bar — inline style so color always renders --}}
                    <div
                        class="absolute left-0 top-3 bottom-3 w-[3px] rounded-r-full"
                        style="background-color: {{ $cfg['accent'] }};"
                    ></div>

                    {{-- Drag handle --}}
                    <div class="text-slate-300 dark:text-slate-600 group-hover:text-slate-500 dark:group-hover:text-slate-400 transition text-sm pt-0.5 select-none shrink-0">
                        ⠿
                    </div>

                    <div class="flex-1 min-w-0 pl-1">
                        {{-- Deal name --}}
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition truncate mb-2">
                            <a href="{{ route('deals.show', $deal['id']) }}" class="focus:underline outline-none">
                                {{ $deal['name'] }}
                            </a>
                        </p>

                        {{-- Amount + company --}}
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-sm font-bold text-slate-800 dark:text-white tabular-nums">
                                £{{ number_format($deal['amount'], 0) }}
                            </span>
                            @if(!empty($deal['internal_company']))
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-500 dark:text-slate-400 shrink-0 truncate max-w-[80px]">
                                    {{ $deal['internal_company'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Meta rows --}}
                        <div class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                            {{-- Created date --}}
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 opacity-70" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7 2h1a1 1 0 0 1 1 1v1h5V3a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a3 3 0 0 1 3 3v11a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3V3a1 1 0 0 1 1-1m8 2h1V3h-1zM8 4V3H7v1zM6 5a2 2 0 0 0-2 2v1h15V7a2 2 0 0 0-2-2zM4 18a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V9H4zm8-5h5v5h-5zm1 1v3h3v-3z"/>
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($deal['created_at'])->diffForHumans() }}</span>
                            </div>

                            {{-- Contact --}}
                            @if(!empty($deal['contacts'][0]))
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 opacity-70" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                </svg>
                                <span class="truncate">{{ $deal['contacts'][0]['first_name'] }} {{ $deal['contacts'][0]['last_name'] }}</span>
                            </div>
                            @endif

                            {{-- Company --}}
                            @if(!empty($deal['companies'][0]))
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 opacity-70" viewBox="0 0 512 512" fill="currentColor">
                                    <path d="M440 464V16H72v448H16v32h480v-32Zm-32 0H272v-64h-32v64H104V48h304Z"/>
                                    <path d="M160 304h32v32h-32zm80 0h32v32h-32zm80 0h32v32h-32zm-160-96h32v32h-32zm80 0h32v32h-32zm80 0h32v32h-32zm-160-96h32v32h-32zm80 0h32v32h-32zm80 0h32v32h-32z"/>
                                </svg>
                                <span class="truncate">{{ $deal['companies'][0]['name'] }}</span>
                            </div>
                            @endif

                            {{-- Owner --}}
                            <div class="flex items-center gap-1.5 pt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 opacity-70" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2zm0 12c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z"/>
                                </svg>
                                <span class="truncate font-medium">
                                    {{ $deal['user'] ? $deal['user']['name'] : 'Unassigned' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 text-slate-400 dark:text-slate-600 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl mt-1">
                    <svg class="w-6 h-6 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-xs">No deals</span>
                </div>
                @endforelse
            </div>

            {{-- Drop zone indicator --}}
            <div
                class="text-center text-xs font-semibold px-3 py-3 border-t border-dashed border-indigo-200 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 bg-indigo-50/80 dark:bg-indigo-950/30"
                x-show="draggingId !== null && draggingStage !== '{{ $stage }}'"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                ↓ Drop here to update stage
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ══════════════════════════════════════
         TABLE LIST VIEW
    ══════════════════════════════════════ --}}
    @if($view === 'table')
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Internal Entity</th>
                        <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Amount</th>
                        <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stage</th>
                        <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Move Stage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($deals as $deal)
                    @php
                        $cfg = $stageConfig[$deal['stage']] ?? [
                            'accent' => '#64748b', 'accentLight' => 'rgba(100,116,139,0.10)',
                            'accentText' => '#475569', 'icon' => '🔹',
                            'label' => ucwords($deal['stage']),
                        ];
                    @endphp
                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/40 transition duration-100">
                        <td class="px-5 py-3.5 font-medium text-slate-900 dark:text-white">
                            {{-- Left accent bar via box-shadow on td --}}
                            <div class="flex items-center gap-3">
                                <div class="w-1 h-5 rounded-full shrink-0" style="background-color: {{ $cfg['accent'] }};"></div>
                                <a
                                    href="{{ route('deals.show', $deal['id']) }}"
                                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate max-w-[200px] block"
                                >{{ $deal['name'] }}</a>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 text-sm">
                            {{ $deal['internal_company'] ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white tabular-nums text-sm">
                            £{{ number_format($deal['amount'], 0) }}
                        </td>
                        <td class="px-5 py-3.5">
                            {{-- Badge with inline styles so colors always render --}}
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                style="background-color: {{ $cfg['accentLight'] }}; color: {{ $cfg['accentText'] }};"
                            >
                                <span>{{ $cfg['icon'] }}</span>
                                {{ $cfg['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <select
                                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 text-xs py-1.5 pl-2.5 pr-7 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition cursor-pointer"
                                wire:change="updateStage({{ $deal['id'] }}, $event.target.value)"
                            >
                                @foreach($stages as $s)
                                    @php $sCfg = $stageConfig[$s] ?? ['label' => ucwords($s), 'icon' => '']; @endphp
                                    <option value="{{ $s }}" {{ $deal['stage'] === $s ? 'selected' : '' }}>
                                        {{ $sCfg['icon'] }} {{ $sCfg['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500">
                                <svg class="w-8 h-8 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm italic">No deals match the current filters.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>