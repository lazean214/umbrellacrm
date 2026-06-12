<?php

use Livewire\Component;
use App\Models\Deal;
use App\Models\User;
use App\Enums\DealStage;
use Livewire\Attributes\On;

new class extends Component {
    public $deals = [];
    public $view = 'kanban'; // 'kanban' or 'table'
    public array $stages = [];

    // --- Pagination (table view) ---
    public int $perPage = 25;
    public int $currentPage = 1;
    public int $totalDeals = 0;
    public int $totalPages = 1;
    public int $paginationFrom = 0;
    public int $paginationTo = 0;

    // --- Lazy load (kanban view) ---
    public int $kanbanLoadedCount = 50;
    public bool $kanbanHasMore = false;

    // --- Column Visibility ---
    /** @var array<string> */
    public array $visibleColumns = ['name', 'owner', 'contact', 'company', 'amount', 'stage', 'created_at'];

    /**
     * All available columns with labels grouped by source.
     *
     * @var array<string, array{label: string, group: string}>
     */
    public const AVAILABLE_COLUMNS = [
        // Deal fields
        'name' => ['label' => 'Deal Name', 'group' => 'Deal'],
        'amount' => ['label' => 'Amount', 'group' => 'Deal'],
        'stage' => ['label' => 'Stage', 'group' => 'Deal'],
        'recruitment_agency' => ['label' => 'Recruitment Agency', 'group' => 'Deal'],
        'consultant_name' => ['label' => 'Consultant Name', 'group' => 'Deal'],
        'agency_deal_value' => ['label' => 'Agency Deal Value', 'group' => 'Deal'],
        'margin_agreed' => ['label' => 'Margin Agreed', 'group' => 'Deal'],
        'date_sent' => ['label' => 'Date Sent', 'group' => 'Deal'],
        'date_signed' => ['label' => 'Date Signed', 'group' => 'Deal'],
        'who_signed' => ['label' => 'Who Signed', 'group' => 'Deal'],
        'right_to_work' => ['label' => 'Right to Work', 'group' => 'Deal'],
        'mda_reference_number' => ['label' => 'MDA Reference', 'group' => 'Deal'],
        'date_set_up' => ['label' => 'Date Set Up', 'group' => 'Deal'],
        'tax_code' => ['label' => 'Tax Code', 'group' => 'Deal'],
        'created_at' => ['label' => 'Created', 'group' => 'Deal'],
        // Owner (user) fields
        'owner' => ['label' => 'Owner', 'group' => 'Owner'],
        'owner_email' => ['label' => 'Owner Email', 'group' => 'Owner'],
        // Contact fields
        'contact' => ['label' => 'Contact', 'group' => 'Contact'],
        // Company fields
        'company' => ['label' => 'Company', 'group' => 'Company'],
        'company_email' => ['label' => 'Company Email', 'group' => 'Company'],
        'company_phone' => ['label' => 'Company Phone', 'group' => 'Company'],
        'company_domain' => ['label' => 'Company Domain', 'group' => 'Company'],
    ];

    // --- CRM Live Filter States ---
    public string $filterDealName = '';
    public string $filterOwner = '';
    public string $filterContact = '';
    public string $filterCompanyName = '';
    public string $filterStage = '';
    public $minAmount = null;
    public $maxAmount = null;
    public $dateFrom = null;
    public $dateTo = null;

    public bool $isDefaultDateRange = false;

    // --- BATCH OPERATIONS ---
    public array $selectedDeals = [];
    public bool $selectAll = false;
    public string $batchOperation = '';
    public string $batchOwnerValue = '';
    public string $batchStageValue = '';
    public bool $showBatchModal = false;
    public bool $showConfirmModal = false;
    public string $confirmMessage = '';
    public array $allUsers = [];
    public array $allCompanies = [];

    /**
     * Push an incoming background deal straight into the tracking array on-the-fly.
     */
    #[On('echo:deals,DealCreated')]
    public function appendIncomingDeal(array $rawDeal, int $targetUserId): void
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser->id !== $targetUserId) {
            return;
        }

        if (collect($this->deals)->contains('id', $rawDeal['id'])) {
            return;
        }

        array_unshift($this->deals, $rawDeal);

        $this->totalDeals++;
        if ($this->view === 'table' && count($this->deals) > $this->perPage) {
            array_pop($this->deals);
        }
    }

    /**
     * Handle deal updates from WebSocket
     */
    #[On('echo:deals,DealUpdated')]
    public function updateIncomingDeal(array $rawDeal, int $targetUserId): void
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser->id !== $targetUserId) {
            return;
        }

        foreach ($this->deals as $index => $deal) {
            if ($deal['id'] === $rawDeal['id']) {
                $this->deals[$index] = $rawDeal;
                break;
            }
        }
    }

    /**
     * Handle deal deletion from WebSocket
     */
    #[On('echo:deals,DealDeleted')]
    public function removeIncomingDeal(int $dealId, int $targetUserId): void
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser->id !== $targetUserId) {
            return;
        }

        $this->deals = array_filter($this->deals, fn($deal) => $deal['id'] !== $dealId);
        $this->totalDeals--;
    }

    private function onFilterChanged(): void
    {
        $this->currentPage = 1;
        $this->kanbanLoadedCount = 50;
        $this->persistState();
        $this->loadDeals();
        $this->resetBatchState();
    }

    public function updatedFilterDealName(): void
    {
        $this->onFilterChanged();
    }
    public function updatedFilterOwner(): void
    {
        $this->onFilterChanged();
    }
    public function updatedFilterContact(): void
    {
        $this->onFilterChanged();
    }
    public function updatedFilterCompanyName(): void
    {
        $this->onFilterChanged();
    }
    public function updatedFilterStage(): void
    {
        $this->onFilterChanged();
    }
    public function updatedMinAmount(): void
    {
        $this->onFilterChanged();
    }
    public function updatedMaxAmount(): void
    {
        $this->onFilterChanged();
    }
    public function updatedDateFrom(): void
    {
        $this->isDefaultDateRange = false;
        $this->onFilterChanged();
    }
    public function updatedDateTo(): void
    {
        $this->onFilterChanged();
    }
    public function updatedPerPage(): void
    {
        $this->currentPage = 1;
        $this->persistState();
        $this->loadDeals();
        $this->resetBatchState();
    }

    public function mount(): void
    {
        $this->stages = array_map(fn (DealStage $stage) => $stage->value, DealStage::cases());
        $this->allUsers = User::orderBy('name')
            ->get(['id', 'name', 'email'])
            ->toArray();
        $this->allCompanies = \App\Models\Company::orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $state = session('deals_view_state', []);

        $this->view = $state['view'] ?? 'kanban';
        $this->perPage = $state['perPage'] ?? 25;
        $this->visibleColumns = $state['visibleColumns'] ?? ['name', 'owner', 'contact', 'company', 'amount', 'stage', 'created_at'];
        $this->filterDealName = $state['filterDealName'] ?? '';
        $this->filterOwner = $state['filterOwner'] ?? '';
        $this->filterContact = $state['filterContact'] ?? '';
        $this->filterCompanyName = $state['filterCompanyName'] ?? '';
        $this->filterStage = $state['filterStage'] ?? '';
        $this->minAmount = $state['minAmount'] ?? null;
        $this->maxAmount = $state['maxAmount'] ?? null;
        $this->isDefaultDateRange = $state['isDefaultDateRange'] ?? false;

        if (array_key_exists('dateFrom', $state)) {
            $this->dateFrom = $state['dateFrom'];
            $this->dateTo = $state['dateTo'];
        } else {
            $this->dateFrom = now()->startOfMonth()->toDateString();
            $this->dateTo = null;
            $this->isDefaultDateRange = true;
        }

        $this->loadDeals();

        if ($this->isDefaultDateRange && $this->getTotalResultCount() < 100) {
            $this->dateFrom = null;
            $this->isDefaultDateRange = false;
            $this->loadDeals();
        }
    }

    private function persistState(): void
    {
        session([
            'deals_view_state' => [
                'view' => $this->view,
                'perPage' => $this->perPage,
                'visibleColumns' => $this->visibleColumns,
                'filterDealName' => $this->filterDealName,
                'filterOwner' => $this->filterOwner,
                'filterContact' => $this->filterContact,
                'filterCompanyName' => $this->filterCompanyName,
                'filterStage' => $this->filterStage,
                'minAmount' => $this->minAmount,
                'maxAmount' => $this->maxAmount,
                'dateFrom' => $this->dateFrom,
                'dateTo' => $this->dateTo,
                'isDefaultDateRange' => $this->isDefaultDateRange,
            ],
        ]);
    }

    private function getTotalResultCount(): int
    {
        return $this->view === 'table' ? $this->totalDeals : count($this->deals);
    }

    private function getCurrentUser(): ?User
    {
        return auth()->user();
    }

    public function isSalesTeam(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->isSalesTeam();
    }

    public function isComplianceTeam(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->isComplianceTeam();
    }

    public function getAllowedStagesForUser(): array
    {
        $user = $this->getCurrentUser();
        return $user ? $user->getAllowedDealStages() : [];
    }

    public function canEditDealStage(): bool
    {
        return count($this->getAllowedStagesForUser()) > 0;
    }

    public function canEditStage(string $stage, ?string $currentDealStage = null): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        if ($currentDealStage && $user->isSalesTeam() && !$user->canMoveToStage($currentDealStage)) {
            return false;
        }
        return $user->canMoveToStage($stage);
    }

    #[On('triggerLoadDeal')]
    public function loadDeals(): void
    {
        $query = Deal::query()
            ->with(['contacts:id,first_name,last_name', 'companies:id,name,email,phone,domain', 'user:id,name,email'])
            ->visibleTo($this->getCurrentUser());

        // Apply filters
        if (!empty($this->filterDealName)) {
            $query->where('name', 'like', '%' . $this->filterDealName . '%');
        }
        if (!empty($this->filterOwner)) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $this->filterOwner . '%'));
        }
        if (!empty($this->filterContact)) {
            $query->whereHas('contacts', fn($q) => $q->where(\DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->filterContact . '%'));
        }
        if (!empty($this->filterCompanyName)) {
            $query->whereHas('companies', fn($q) => $q->where('name', 'like', '%' . $this->filterCompanyName . '%'));
        }
        if (!empty($this->filterStage)) {
            $query->where('stage', $this->filterStage);
        }
        if (!is_null($this->minAmount) && $this->minAmount !== '') {
            $query->where('amount', '>=', $this->minAmount);
        }
        if (!is_null($this->maxAmount) && $this->maxAmount !== '') {
            $query->where('amount', '<=', $this->maxAmount);
        }
        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        if ($this->view === 'table') {
            // Use cursor pagination for better performance
            $countQuery = clone $query;
            $this->totalDeals = $countQuery->count();
            $this->totalPages = max(1, (int) ceil($this->totalDeals / $this->perPage));
            $this->currentPage = min($this->currentPage, $this->totalPages);

            $this->paginationFrom = $this->totalDeals === 0 ? 0 : ($this->currentPage - 1) * $this->perPage + 1;
            $this->paginationTo = min($this->currentPage * $this->perPage, $this->totalDeals);

            $this->deals = $query
                ->latest('updated_at')
                ->skip(($this->currentPage - 1) * $this->perPage)
                ->take($this->perPage)
                ->get()
                ->map(fn (Deal $deal) => $this->dealToArray($deal))
                ->toArray();
        } else {
            $total = $query->count();
            $this->kanbanHasMore = $total > $this->kanbanLoadedCount;
            $this->deals = $query->latest('updated_at')->take($this->kanbanLoadedCount)->get()->map(fn (Deal $deal) => $this->dealToArray($deal))->toArray();
        }
    }

    private function dealToArray(Deal $deal): array
    {
        $data = $deal->toArray();
        $data['stage'] = $deal->stage instanceof \BackedEnum ? $deal->stage->value : (string) $deal->stage;

        return $data;
    }

    // Manual refresh - call this when user wants to refresh (e.g., pull-to-refresh or button)
    public function refreshDeals(): void
    {
        $this->loadDeals();
        $this->dispatch('deals-refreshed');
    }

    public function loadMoreKanban(): void
    {
        $this->kanbanLoadedCount += 50;
        $this->loadDeals();
    }

    public function resetFilters(): void
    {
        $this->reset(['filterDealName', 'filterOwner', 'filterContact', 'filterCompanyName', 'filterStage', 'minAmount', 'maxAmount', 'dateTo']);
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->isDefaultDateRange = true;
        $this->currentPage = 1;
        $this->kanbanLoadedCount = 50;
        $this->persistState();
        $this->loadDeals();
        $this->resetBatchState();

        if ($this->isDefaultDateRange && $this->getTotalResultCount() < 100) {
            $this->dateFrom = null;
            $this->isDefaultDateRange = false;
            $this->loadDeals();
        }
    }

    public function goToPage(int $page): void
    {
        $this->currentPage = max(1, min($page, $this->totalPages));
        $this->loadDeals();
        $this->resetBatchState();
    }

    public function showAllTime(): void
    {
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->isDefaultDateRange = false;
        $this->currentPage = 1;
        $this->kanbanLoadedCount = 50;
        $this->persistState();
        $this->loadDeals();
        $this->resetBatchState();
    }

    public function hasActiveFilters(): bool
    {
        return !empty($this->filterDealName) || !empty($this->filterOwner) || !empty($this->filterContact) || !empty($this->filterCompanyName) || !empty($this->filterStage) || ($this->minAmount !== null && $this->minAmount !== '') || ($this->maxAmount !== null && $this->maxAmount !== '') || (!$this->isDefaultDateRange && !empty($this->dateFrom)) || !empty($this->dateTo);
    }

    public function updateStage(int $dealId, string $newStage): void
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            $this->dispatch('error', message: 'Unauthorized');
            return;
        }

        $deal = Deal::findOrFail($dealId);
        $oldStage = $deal->stage->value;

        if ($user->isSalesTeam()) {
            if ($deal->user_id !== $user->id) {
                $this->dispatch('error', message: 'You can only edit your own deals');
                return;
            }
            if (!$user->canMoveToStage($deal->stage->value)) {
                $this->dispatch('error', message: 'This deal is managed by the Compliance Team.');
                return;
            }
            if (!$user->canMoveToStage($newStage)) {
                $allowedStages = implode(', ', $user->getAllowedDealStages());
                $this->dispatch('error', message: "You can only move to: {$allowedStages}");
                return;
            }
        }

        $deal->stage = DealStage::from($newStage);
        $deal->save();

        $reason = $user->isSalesTeam() ? 'Sales Team action' : 'Compliance Team action';
        $deal->logStageChange($oldStage, $newStage, $reason);

        foreach ($this->deals as &$existingDeal) {
            if ($existingDeal['id'] == $dealId) {
                $existingDeal['stage'] = $newStage;
                break;
            }
        }
        unset($existingDeal);

        $this->dispatch('success', message: 'Deal moved successfully');
        $this->dispatch('deals-updated');
    }

    // Batch operations (keep existing implementation)
    public function resetBatchState(): void
    {
        $this->selectedDeals = [];
        $this->selectAll = false;
        $this->batchOperation = '';
        $this->batchOwnerValue = '';
        $this->batchStageValue = '';
        $this->showBatchModal = false;
        $this->showConfirmModal = false;
    }

    public function toggleSelectAll(): void
    {
        $this->selectAll = !$this->selectAll;
        if ($this->selectAll) {
            if ($this->hasActiveFilters()) {
                $this->showConfirmModal = true;
                $dealCount = count($this->deals);
                $this->confirmMessage = "Select all {$dealCount} deals from the filtered results?";
            } else {
                $this->selectedDeals = array_map(fn($deal) => $deal['id'], $this->deals);
            }
        } else {
            $this->selectedDeals = [];
        }
    }

    public function confirmSelectAll(): void
    {
        $this->selectedDeals = array_map(fn($deal) => $deal['id'], $this->deals);
        $this->showConfirmModal = false;
    }

    public function cancelSelectAll(): void
    {
        $this->selectAll = false;
        $this->showConfirmModal = false;
    }

    public function toggleDealSelection(int $dealId): void
    {
        if (in_array($dealId, $this->selectedDeals)) {
            $this->selectedDeals = array_filter($this->selectedDeals, fn($id) => $id !== $dealId);
            $this->selectAll = false;
        } else {
            $this->selectedDeals[] = $dealId;
            $visibleIds = array_map(fn($deal) => $deal['id'], $this->deals);
            if (count($this->selectedDeals) === count($visibleIds) && empty(array_diff($visibleIds, $this->selectedDeals))) {
                $this->selectAll = true;
            }
        }
    }

    public function getSelectedCount(): int
    {
        return count($this->selectedDeals);
    }

    public function openBatchModal(string $operation): void
    {
        if (empty($this->selectedDeals)) {
            $this->dispatch('error', message: 'Please select at least one deal');
            return;
        }
        $this->batchOperation = $operation;
        $this->batchOwnerValue = '';
        $this->batchStageValue = '';
        $this->showBatchModal = true;
    }

    public function confirmBatchUpdateOwner(): void
    {
        if (empty($this->batchOwnerValue)) {
            $this->dispatch('error', message: 'Please select an owner');
            return;
        }
        $selectedCount = count($this->selectedDeals);
        $this->confirmMessage = "Update owner for {$selectedCount} deal(s)?";
        $this->showBatchModal = false;
        $this->showConfirmModal = true;
    }

    public function confirmBatchUpdateStage(): void
    {
        if (empty($this->batchStageValue)) {
            $this->dispatch('error', message: 'Please select a stage');
            return;
        }
        $selectedCount = count($this->selectedDeals);
        $this->confirmMessage = "Update stage for {$selectedCount} deal(s)?";
        $this->showBatchModal = false;
        $this->showConfirmModal = true;
    }

    public function confirmBatchDelete(): void
    {
        $selectedCount = count($this->selectedDeals);
        $this->confirmMessage = "Delete {$selectedCount} deal(s)? This action cannot be undone.";
        $this->showBatchModal = false;
        $this->showConfirmModal = true;
    }

    public function executeBatchUpdateOwner(): void
    {
        $user = $this->getCurrentUser();
        if (!$user || !$this->isComplianceTeam()) {
            $this->dispatch('error', message: 'Unauthorized');
            return;
        }
        $count = count($this->selectedDeals);
        Deal::whereIn('id', $this->selectedDeals)->update(['user_id' => $this->batchOwnerValue]);
        $this->loadDeals();
        $this->resetBatchState();
        $this->dispatch('success', message: 'Owner updated for ' . $count . ' deal(s)');
    }

    public function executeBatchUpdateStage(): void
    {
        $user = $this->getCurrentUser();
        if (!$user || !$this->isComplianceTeam()) {
            $this->dispatch('error', message: 'Unauthorized');
            return;
        }
        $count = count($this->selectedDeals);
        Deal::whereIn('id', $this->selectedDeals)->update(['stage' => $this->batchStageValue]);
        $this->loadDeals();
        $this->resetBatchState();
        $this->dispatch('success', message: 'Stage updated for ' . $count . ' deal(s)');
    }

    public function executeBatchDelete(): void
    {
        $user = $this->getCurrentUser();
        if (!$user || !$this->isComplianceTeam()) {
            $this->dispatch('error', message: 'Unauthorized');
            return;
        }
        $count = count($this->selectedDeals);
        Deal::whereIn('id', $this->selectedDeals)->delete();
        $this->loadDeals();
        $this->resetBatchState();
        $this->dispatch('success', message: "{$count} deal(s) deleted successfully");
    }

    public function closeBatchModal(): void
    {
        $this->showBatchModal = false;
        $this->showConfirmModal = false;
    }

    public function confirmBatchAction(): void
    {
        match ($this->batchOperation) {
            'owner' => $this->executeBatchUpdateOwner(),
            'stage' => $this->executeBatchUpdateStage(),
            'delete' => $this->executeBatchDelete(),
        };
        $this->showConfirmModal = false;
    }

    public function setView(string $view): void
    {
        $this->view = $view;
        $this->currentPage = 1;
        $this->kanbanLoadedCount = 50;
        $this->persistState();
        $this->loadDeals();
    }

    public function toggleColumn(string $column): void
    {
        if (in_array($column, $this->visibleColumns)) {
            if (count($this->visibleColumns) > 1) {
                $this->visibleColumns = array_values(array_filter($this->visibleColumns, fn($c) => $c !== $column));
            }
        } else {
            $this->visibleColumns[] = $column;
        }
        $this->persistState();
    }

    public function exportUrl(): string
    {
        return route(
            'deals.export',
            array_filter([
                'filterDealName' => $this->filterDealName ?: null,
                'filterOwner' => $this->filterOwner ?: null,
                'filterContact' => $this->filterContact ?: null,
                'filterCompanyName' => $this->filterCompanyName ?: null,
                'filterStage' => $this->filterStage ?: null,
                'minAmount' => $this->minAmount ?: null,
                'maxAmount' => $this->maxAmount ?: null,
                'dateFrom' => $this->dateFrom ?: null,
                'dateTo' => $this->dateTo ?: null,
            ]),
        );
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
    $stageConfig = [
        'doc sent' => [
            'accent' => '#4f46e5',
            'accentLight' => 'rgba(79,70,229,0.12)',
            'accentText' => '#3730a3',
            'icon' => '📄',
            'label' => 'Doc Sent',
        ],
        'doc signed' => [
            'accent' => '#0891b2',
            'accentLight' => 'rgba(8,145,178,0.12)',
            'accentText' => '#155e75',
            'icon' => '✍️',
            'label' => 'Doc Signed',
        ],
        'compliant' => [
            'accent' => '#4ed386',
            'accentLight' => 'rgba(217,119,6,0.12)',
            'accentText' => '#1b8b41',
            'icon' => '✅',
            'label' => 'Compliant',
        ],
        'ready for payment' => [
            'accent' => '#ea580c',
            'accentLight' => 'rgba(234,88,12,0.12)',
            'accentText' => '#9a3412',
            'icon' => '💳',
            'label' => 'Ready for Payment',
        ],
        'paid' => [
            'accent' => '#16a34a',
            'accentLight' => 'rgba(22,163,74,0.12)',
            'accentText' => '#14532d',
            'icon' => '💰',
            'label' => 'Paid',
        ],
    ];
@endphp

<div x-data="{
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
    onDragOver(e) { e.preventDefault(); },
    isRefreshing: false,
    async refresh() {
        this.isRefreshing = true;
        await $wire.refreshDeals();
        setTimeout(() => { this.isRefreshing = false; }, 500);
    }
}"
    class="space-y-6 w-full mx-auto p-4 sm:p-6 lg:p-8 antialiased text-slate-900 dark:text-slate-100">

    {{-- Loading indicator (only shows on actual loads, not on every poll) --}}
    <div wire:loading.delay.class="opacity-100" wire:loading.delay.class.remove="opacity-0" wire:loading.delay
        class="fixed top-0 left-0 right-0 h-0.5 bg-indigo-600 dark:bg-indigo-400 z-50 animate-pulse opacity-0 transition-opacity duration-200">
    </div>

    {{-- Header with manual refresh button --}}
    <div class="w-full border-b border-slate-200 dark:border-slate-800 pb-5 mb-4">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white tracking-tight">
                    {{ __('Deals Pipeline') }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Manage your pipeline tracking, stage workflows, and incoming financial volumes.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                {{-- Manual refresh button --}}
                <button @click="refresh()" :disabled="isRefreshing"
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium rounded-md transition-all duration-150 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-50">
                    <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isRefreshing }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>

                <div class="inline-flex rounded-lg shadow-sm bg-slate-100 dark:bg-slate-800 p-1 gap-0.5">
                    <button wire:click="setView('kanban')"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium rounded-md transition-all duration-150
                            {{ $view === 'kanban'
                                ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm ring-1 ring-slate-200/60 dark:ring-slate-600/40'
                                : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor">
                            <rect x="1" y="1" width="4" height="14" rx="1.5" />
                            <rect x="6" y="1" width="4" height="14" rx="1.5" />
                            <rect x="11" y="1" width="4" height="14" rx="1.5" />
                        </svg>
                        Kanban
                    </button>
                    <button wire:click="setView('table')"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium rounded-md transition-all duration-150
                            {{ $view === 'table'
                                ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm ring-1 ring-slate-200/60 dark:ring-slate-600/40'
                                : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor">
                            <rect x="1" y="1" width="14" height="3" rx="1.5" />
                            <rect x="1" y="6" width="14" height="3" rx="1.5" />
                            <rect x="1" y="11" width="14" height="3" rx="1.5" />
                        </svg>
                        Table
                    </button>
                </div>
                @include('components.deals.partials.⚡export', ['exportUrl' => $this->exportUrl()])
            </div>
        </div>
    </div>

    @include('components.deals.partials.⚡filters')

    @if ($view === 'kanban')
        <div wire:key="kanban-board-{{ $kanbanLoadedCount }}-{{ $totalDeals }}">
            @include('components.deals.partials.⚡kanban', [
                'stageConfig' => $stageConfig,
            ])

            @if ($kanbanHasMore)
                <div x-data x-intersect.threshold.10="$wire.loadMoreKanban()"
                    class="h-10 flex items-center justify-center">
                    <svg class="w-5 h-5 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                </div>
            @endif
        </div>
    @endif

    @if ($view === 'table')
        <div wire:key="table-view-{{ $currentPage }}-{{ $totalDeals }}">
            @include('components.deals.partials.⚡table-view', ['stageConfig' => $stageConfig])
        </div>
    @endif

    {{-- Batch Actions Floating Toolbar (unchanged) --}}
    @if ($this->getSelectedCount() > 0)
        <div
            class="fixed bottom-0 left-0 right-0 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 shadow-xl z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="text-sm font-medium text-slate-900 dark:text-white">
                    {{ $this->getSelectedCount() }} deal(s) selected
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors">
                            Batch Operations
                            <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" x-cloak
                            class="absolute right-0 bottom-full mb-2 w-48 rounded-lg shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 divide-y divide-slate-100 dark:divide-slate-700 z-50">
                            <div class="py-1">
                                <button wire:click="openBatchModal('owner')" @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">👤
                                    Update Owner</button>
                                <button wire:click="openBatchModal('stage')" @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">📊
                                    Update Stage</button>
                            </div>
                            <div class="py-1">
                                <button wire:click="openBatchModal('delete')" @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">🗑️
                                    Delete Records</button>
                            </div>
                        </div>
                    </div>

                    <button wire:click="resetBatchState"
                        class="px-4 py-2.5 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Clear
                        Selection</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODALS (unchanged) --}}
    @if ($showBatchModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            wire:click.self="closeBatchModal">
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white">
                    @if ($batchOperation === 'owner')
                        Update Deal Owner
                    @endif
                    @if ($batchOperation === 'stage')
                        Update Deal Stage
                    @endif
                    @if ($batchOperation === 'delete')
                        Delete Deals
                    @endif
                </h3>

                @if ($batchOperation === 'owner')
                    <select wire:model="batchOwnerValue"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-white">
                        <option value="">Choose an owner...</option>
                        @foreach ($allUsers as $u)
                            <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                        @endforeach
                    </select>
                @elseif($batchOperation === 'stage')
                    <select wire:model="batchStageValue"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-white">
                        <option value="">Choose a stage...</option>
                        @foreach ($stages as $s)
                            <option value="{{ $s }}">{{ $stageConfig[$s]['label'] ?? ucwords($s) }}
                            </option>
                        @endforeach
                    </select>
                @elseif($batchOperation === 'delete')
                    <p class="text-sm text-red-600 dark:text-red-400">⚠️ This action cannot be undone.</p>
                @endif

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="closeBatchModal"
                        class="px-4 py-2 rounded-lg border border-slate-300 text-sm">Cancel</button>
                    <button
                        wire:click="@if ($batchOperation === 'owner') confirmBatchUpdateOwner @elseif($batchOperation === 'stage') confirmBatchUpdateStage @else confirmBatchDelete @endif"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm">Continue</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showConfirmModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            wire:click.self="closeBatchModal">
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold mb-2">Confirm Action</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">{{ $confirmMessage }}</p>
                <div class="flex justify-end gap-3">
                    @if ($selectAll && !$batchOperation)
                        <button wire:click="cancelSelectAll" class="px-4 py-2 text-sm border rounded-lg">Page
                            Only</button>
                        <button wire:click="confirmSelectAll"
                            class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Select System-wide</button>
                    @else
                        <button wire:click="closeBatchModal"
                            class="px-4 py-2 text-sm border rounded-lg">Cancel</button>
                        <button wire:click="confirmBatchAction"
                            class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Confirm</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
