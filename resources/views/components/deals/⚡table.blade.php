<?php

use Livewire\Component;
use App\Models\Deal;
use App\Models\User;
<<<<<<< HEAD
use App\Enums\DealStage;
use Livewire\Attributes\On;
=======
use Illuminate\Support\Str;
use App\Enums\DealStage;
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c

new class extends Component {
    public $deals = [];
    public $view = 'kanban'; // 'kanban' or 'table'
    public array $stages = [];
<<<<<<< HEAD
=======
    public int $refreshInterval = 5;
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c

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

<<<<<<< HEAD
=======
    /**
     * Whether the dateFrom was auto-set to start of month (not user-defined).
     * Used to trigger auto-widening when results are sparse.
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    public bool $isDefaultDateRange = false;

    // --- BATCH OPERATIONS ---
    public array $selectedDeals = [];
    public bool $selectAll = false;
<<<<<<< HEAD
    public string $batchOperation = '';
=======
    public string $batchOperation = ''; // 'owner', 'stage', 'delete'
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    public string $batchOwnerValue = '';
    public string $batchStageValue = '';
    public bool $showBatchModal = false;
    public bool $showConfirmModal = false;
    public string $confirmMessage = '';
    public array $allUsers = [];
    public array $allCompanies = [];

    /**
<<<<<<< HEAD
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

=======
     * Shared reset-and-reload for every filter/paging change.
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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
<<<<<<< HEAD
        $this->isDefaultDateRange = false;
        $this->onFilterChanged();
    }
=======
        // Once the user touches the date, it's no longer the auto-default
        $this->isDefaultDateRange = false;
        $this->onFilterChanged();
    }

>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    public function updatedDateTo(): void
    {
        $this->onFilterChanged();
    }
<<<<<<< HEAD
=======

>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    public function updatedPerPage(): void
    {
        $this->currentPage = 1;
        $this->persistState();
        $this->loadDeals();
        $this->resetBatchState();
    }

<<<<<<< HEAD
    public function mount(): void
    {
        $this->stages = array_map(fn (DealStage $stage) => $stage->value, DealStage::cases());
=======
    public function mount()
    {
        $this->stages = array_map(fn($s) => $s->value, [DealStage::DOC_SENT, DealStage::DOC_SIGNED, DealStage::COMPLIANT, DealStage::READY_FOR_PAYMENT, DealStage::PAID]);
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        $this->allUsers = User::orderBy('name')
            ->get(['id', 'name', 'email'])
            ->toArray();
        $this->allCompanies = \App\Models\Company::orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

<<<<<<< HEAD
=======
        // ── Restore persisted view state from session ──
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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

<<<<<<< HEAD
=======
        // If a date range was explicitly persisted, restore it.
        // Otherwise default to start of current month so the initial load is fast.
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        if (array_key_exists('dateFrom', $state)) {
            $this->dateFrom = $state['dateFrom'];
            $this->dateTo = $state['dateTo'];
        } else {
            $this->dateFrom = now()->startOfMonth()->toDateString();
            $this->dateTo = null;
            $this->isDefaultDateRange = true;
        }

        $this->loadDeals();

<<<<<<< HEAD
=======
        // Auto-widen: if we loaded with the default month filter and got < 100
        // results, silently remove the date restriction so nothing is hidden.
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        if ($this->isDefaultDateRange && $this->getTotalResultCount() < 100) {
            $this->dateFrom = null;
            $this->isDefaultDateRange = false;
            $this->loadDeals();
        }
    }

<<<<<<< HEAD
=======
    /**
     * Persist the current UI state to the session.
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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

<<<<<<< HEAD
=======
    /**
     * Total results across current filters — works for both views.
     * In table view $totalDeals is set by loadDeals(); in kanban we count $deals.
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    private function getTotalResultCount(): int
    {
        return $this->view === 'table' ? $this->totalDeals : count($this->deals);
    }

<<<<<<< HEAD
=======
    /**
     * Get the currently authenticated user
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    private function getCurrentUser(): ?User
    {
        return auth()->user();
    }

<<<<<<< HEAD
=======
    /**
     * Check if current user is in Sales Team
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    public function isSalesTeam(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->isSalesTeam();
    }

<<<<<<< HEAD
=======
    /**
     * Check if current user is in Compliance Team
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    public function isComplianceTeam(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->isComplianceTeam();
    }

<<<<<<< HEAD
=======
    /**
     * Get allowed stages for current user
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    public function getAllowedStagesForUser(): array
    {
        $user = $this->getCurrentUser();
        return $user ? $user->getAllowedDealStages() : [];
    }

<<<<<<< HEAD
=======
    /**
     * Check if current user can move deals to stages
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    public function canEditDealStage(): bool
    {
        return count($this->getAllowedStagesForUser()) > 0;
    }

<<<<<<< HEAD
    public function canEditStage(string $stage, ?string $currentDealStage = null): bool
=======
    /**
     * Check if specific stage can be edited by current user
     */
    public function canEditStage($stage, $currentDealStage = null): bool
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
<<<<<<< HEAD
=======
        // If the deal is already on a restricted stage, Sales cannot move it anywhere
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        if ($currentDealStage && $user->isSalesTeam() && !$user->canMoveToStage($currentDealStage)) {
            return false;
        }
        return $user->canMoveToStage($stage);
    }

<<<<<<< HEAD
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
=======
    public function loadDeals(): void
    {
        $query = Deal::query()->with(['contacts:id,first_name,last_name', 'companies:id,name,email,phone,domain', 'user:id,name,email']);
        $user = $this->getCurrentUser();

        // ──────────────────────────────────────
        // APPLY TEAM RESTRICTIONS
        // ──────────────────────────────────────

        if ($user) {
            // Sales Team: Only see their own deals
            if ($user->isSalesTeam()) {
                $query->where('user_id', $user->id);
            }
            // Compliance Team & no-team users: see all deals
        }

        // ──────────────────────────────────────
        // APPLY FILTERS
        // ──────────────────────────────────────

        // Deal name
        if (!empty($this->filterDealName)) {
            $query->where('name', 'like', '%' . $this->filterDealName . '%');
        }

        // Deal owner (user name)
        if (!empty($this->filterOwner)) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $this->filterOwner . '%'));
        }

        // Contact name
        if (!empty($this->filterContact)) {
            $query->whereHas('contacts', fn($q) => $q->where(\DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->filterContact . '%'));
        }

        // Company name (related companies)
        if (!empty($this->filterCompanyName)) {
            $query->whereHas('companies', fn($q) => $q->where('name', 'like', '%' . $this->filterCompanyName . '%'));
        }

        // Stage
        if (!empty($this->filterStage)) {
            $query->where('stage', $this->filterStage);
        }

        // Amount range
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        if (!is_null($this->minAmount) && $this->minAmount !== '') {
            $query->where('amount', '>=', $this->minAmount);
        }
        if (!is_null($this->maxAmount) && $this->maxAmount !== '') {
            $query->where('amount', '<=', $this->maxAmount);
        }
<<<<<<< HEAD
=======

        // Created date range
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

<<<<<<< HEAD
        if ($this->view === 'table') {
            // Use cursor pagination for better performance
            $countQuery = clone $query;
            $this->totalDeals = $countQuery->count();
=======
        // ──────────────────────────────────────
        // FETCH — strategy depends on active view
        // ──────────────────────────────────────

        $mapper = function ($deal) {
            $arr = $deal->toArray();
            $arr['stage'] = $deal->stage instanceof \BackedEnum ? $deal->stage->value : (string) $deal->stage;
            return $arr;
        };

        if ($this->view === 'table') {
            // Paginated
            $countQuery = clone $query;

            $this->totalDeals = cache()->remember('deals_count_' . md5($query->toSql() . serialize($query->getBindings())), now()->addSeconds(15), fn() => $countQuery->count());
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
            $this->totalPages = max(1, (int) ceil($this->totalDeals / $this->perPage));
            $this->currentPage = min($this->currentPage, $this->totalPages);

            $this->paginationFrom = $this->totalDeals === 0 ? 0 : ($this->currentPage - 1) * $this->perPage + 1;
            $this->paginationTo = min($this->currentPage * $this->perPage, $this->totalDeals);

            $this->deals = $query
                ->latest('updated_at')
                ->skip(($this->currentPage - 1) * $this->perPage)
                ->take($this->perPage)
                ->get()
<<<<<<< HEAD
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

=======
                ->map($mapper)
                ->toArray();
        } else {
            // Kanban — lazy load: fetch up to kanbanLoadedCount
            $total = $query->count();

            $this->kanbanHasMore = $total > $this->kanbanLoadedCount;

            $this->deals = $query->latest('updated_at')->take($this->kanbanLoadedCount)->get()->map($mapper)->toArray();
        }
    }

    public function refreshDeals(): void
    {
        $this->loadDeals();
    }

    /**
     * Load the next batch of 50 deals in kanban view.
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    public function loadMoreKanban(): void
    {
        $this->kanbanLoadedCount += 50;
        $this->loadDeals();
    }

    public function resetFilters(): void
    {
        $this->reset(['filterDealName', 'filterOwner', 'filterContact', 'filterCompanyName', 'filterStage', 'minAmount', 'maxAmount', 'dateTo']);
<<<<<<< HEAD
=======

        // Reset to the default month window, not all-time
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->isDefaultDateRange = true;
        $this->currentPage = 1;
        $this->kanbanLoadedCount = 50;
        $this->persistState();
        $this->loadDeals();
        $this->resetBatchState();

<<<<<<< HEAD
=======
        // Auto-widen if sparse
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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

<<<<<<< HEAD
=======
    /**
     * Explicitly load all-time data (user clicked "View all time →").
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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
<<<<<<< HEAD
        return !empty($this->filterDealName) || !empty($this->filterOwner) || !empty($this->filterContact) || !empty($this->filterCompanyName) || !empty($this->filterStage) || ($this->minAmount !== null && $this->minAmount !== '') || ($this->maxAmount !== null && $this->maxAmount !== '') || (!$this->isDefaultDateRange && !empty($this->dateFrom)) || !empty($this->dateTo);
    }

    public function updateStage(int $dealId, string $newStage): void
=======
        return !empty($this->filterDealName) ||
            !empty($this->filterOwner) ||
            !empty($this->filterContact) ||
            !empty($this->filterCompanyName) ||
            !empty($this->filterStage) ||
            ($this->minAmount !== null && $this->minAmount !== '') ||
            ($this->maxAmount !== null && $this->maxAmount !== '') ||
            // Only count dateFrom as active if the user explicitly set it
            (!$this->isDefaultDateRange && !empty($this->dateFrom)) ||
            !empty($this->dateTo);
    }

    /**
     * Update deal stage with authorization checks
     *
     * - Sales Team: Can only move to Doc Sent, Doc Signed, Compliant
     * - Compliance Team: Can move to any stage
     * - Must own the deal (Sales Team) or be in Compliance Team
     */
    public function updateStage($dealId, $newStage)
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            $this->dispatch('error', message: 'Unauthorized');
            return;
        }

        $deal = Deal::findOrFail($dealId);
        $oldStage = $deal->stage->value;

<<<<<<< HEAD
        if ($user->isSalesTeam()) {
=======
        // ─────────────────────────────
        // AUTHORIZATION
        // ─────────────────────────────

        if ($user->isSalesTeam()) {
            // Must own deal
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
            if ($deal->user_id !== $user->id) {
                $this->dispatch('error', message: 'You can only edit your own deals');
                return;
            }
<<<<<<< HEAD
=======

            // Locked stages
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
            if (!$user->canMoveToStage($deal->stage->value)) {
                $this->dispatch('error', message: 'This deal is managed by the Compliance Team.');
                return;
            }
<<<<<<< HEAD
            if (!$user->canMoveToStage($newStage)) {
                $allowedStages = implode(', ', $user->getAllowedDealStages());
                $this->dispatch('error', message: "You can only move to: {$allowedStages}");
=======

            // Target stage restriction
            if (!$user->canMoveToStage($newStage)) {
                $allowedStages = implode(', ', $user->getAllowedDealStages());

                $this->dispatch('error', message: "You can only move to: {$allowedStages}");

>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
                return;
            }
        }

<<<<<<< HEAD
        $deal->stage = DealStage::from($newStage);
        $deal->save();

        $reason = $user->isSalesTeam() ? 'Sales Team action' : 'Compliance Team action';
        $deal->logStageChange($oldStage, $newStage, $reason);

=======
        // ─────────────────────────────
        // SAVE TO DATABASE
        // ─────────────────────────────

        $deal->stage = DealStage::from($newStage);
        $deal->save();

        $this->dispatch('deals-updated');

        // Log the stage change with reason
        $reason = $user->isSalesTeam() ? 'Sales Team action' : 'Compliance Team action';
        $deal->logStageChange($oldStage, $newStage, $reason);

        // ─────────────────────────────
        // UPDATE LOCAL ARRAY ONLY
        // NO FULL loadDeals()
        // ─────────────────────────────

>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        foreach ($this->deals as &$existingDeal) {
            if ($existingDeal['id'] == $dealId) {
                $existingDeal['stage'] = $newStage;
                break;
            }
        }
<<<<<<< HEAD
        unset($existingDeal);

        $this->dispatch('success', message: 'Deal moved successfully');
        $this->dispatch('deals-updated');
    }

    // Batch operations (keep existing implementation)
    public function resetBatchState(): void
=======

        unset($existingDeal);

        $this->dispatch('success', message: 'Deal moved successfully');
    }

    // ─────────────────────────────────────────────────
    // BATCH OPERATIONS
    // ─────────────────────────────────────────────────

    /**
     * Reset batch operation state
     */
    public function resetBatchState()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        $this->selectedDeals = [];
        $this->selectAll = false;
        $this->batchOperation = '';
        $this->batchOwnerValue = '';
        $this->batchStageValue = '';
        $this->showBatchModal = false;
        $this->showConfirmModal = false;
<<<<<<< HEAD
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
=======

        $this->loadDeals();

        $this->dispatch('deals-updated');
    }

    /**
     * Toggle select all checkbox
     * Shows confirmation modal if filters are active
     */
    public function toggleSelectAll()
    {
        $this->selectAll = !$this->selectAll;

        if ($this->selectAll) {
            // If filters are active, show confirmation modal
            if ($this->hasActiveFilters()) {
                $this->showConfirmModal = true;
                $dealCount = count($this->deals);
                $this->confirmMessage = "Select all {$dealCount} deals from the filtered results? " . 'This will apply the batch operation to all matching records.';
            } else {
                // No filters, just select all visible deals
                $this->selectedDeals = array_map(fn($deal) => $deal['id'], $this->deals);
            }
        } else {
            // Deselect all
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
            $this->selectedDeals = [];
        }
    }

<<<<<<< HEAD
    public function confirmSelectAll(): void
=======
    /**
     * Confirm select all from modal
     */
    public function confirmSelectAll()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        $this->selectedDeals = array_map(fn($deal) => $deal['id'], $this->deals);
        $this->showConfirmModal = false;
    }

<<<<<<< HEAD
    public function cancelSelectAll(): void
=======
    /**
     * Cancel select all and close modal
     */
    public function cancelSelectAll()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        $this->selectAll = false;
        $this->showConfirmModal = false;
    }

<<<<<<< HEAD
    public function toggleDealSelection(int $dealId): void
=======
    /**
     * Toggle individual deal selection
     */
    public function toggleDealSelection($dealId)
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        if (in_array($dealId, $this->selectedDeals)) {
            $this->selectedDeals = array_filter($this->selectedDeals, fn($id) => $id !== $dealId);
            $this->selectAll = false;
        } else {
            $this->selectedDeals[] = $dealId;
<<<<<<< HEAD
=======
            // Check if all visible deals are now selected
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
            $visibleIds = array_map(fn($deal) => $deal['id'], $this->deals);
            if (count($this->selectedDeals) === count($visibleIds) && empty(array_diff($visibleIds, $this->selectedDeals))) {
                $this->selectAll = true;
            }
        }
    }

<<<<<<< HEAD
=======
    /**
     * Get count of selected deals
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    public function getSelectedCount(): int
    {
        return count($this->selectedDeals);
    }

<<<<<<< HEAD
    public function openBatchModal(string $operation): void
=======
    /**
     * Open batch operation modal
     */
    public function openBatchModal($operation)
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        if (empty($this->selectedDeals)) {
            $this->dispatch('error', message: 'Please select at least one deal');
            return;
        }
<<<<<<< HEAD
=======

>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        $this->batchOperation = $operation;
        $this->batchOwnerValue = '';
        $this->batchStageValue = '';
        $this->showBatchModal = true;
    }

<<<<<<< HEAD
    public function confirmBatchUpdateOwner(): void
=======
    /**
     * Confirm batch owner update
     */
    public function confirmBatchUpdateOwner()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        if (empty($this->batchOwnerValue)) {
            $this->dispatch('error', message: 'Please select an owner');
            return;
        }
<<<<<<< HEAD
=======

>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        $selectedCount = count($this->selectedDeals);
        $this->confirmMessage = "Update owner for {$selectedCount} deal(s)?";
        $this->showBatchModal = false;
        $this->showConfirmModal = true;
    }

<<<<<<< HEAD
    public function confirmBatchUpdateStage(): void
=======
    /**
     * Confirm batch stage update
     */
    public function confirmBatchUpdateStage()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        if (empty($this->batchStageValue)) {
            $this->dispatch('error', message: 'Please select a stage');
            return;
        }
<<<<<<< HEAD
=======

>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
        $selectedCount = count($this->selectedDeals);
        $this->confirmMessage = "Update stage for {$selectedCount} deal(s)?";
        $this->showBatchModal = false;
        $this->showConfirmModal = true;
    }

<<<<<<< HEAD
    public function confirmBatchDelete(): void
=======
    /**
     * Confirm batch delete
     */
    public function confirmBatchDelete()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        $selectedCount = count($this->selectedDeals);
        $this->confirmMessage = "Delete {$selectedCount} deal(s)? This action cannot be undone.";
        $this->showBatchModal = false;
        $this->showConfirmModal = true;
    }

<<<<<<< HEAD
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
=======
    /**
     * Execute batch owner update
     */
    public function executeBatchUpdateOwner()
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            $this->dispatch('error', message: 'Unauthorized');
            return;
        }

        // Authorization: Only compliance can batch update owner
        if (!$this->isComplianceTeam()) {
            $this->dispatch('error', message: 'Only Compliance Team can perform batch updates');
            return;
        }

        Deal::whereIn('id', $this->selectedDeals)->update([
            'user_id' => $this->batchOwnerValue,
        ]);

        $this->loadDeals();
        $this->resetBatchState();

        $this->dispatch('success', message: 'Owner updated for ' . count($this->selectedDeals) . ' deal(s)');
    }

    /**
     * Execute batch stage update
     */
    public function executeBatchUpdateStage()
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            $this->dispatch('error', message: 'Unauthorized');
            return;
        }

        // Authorization: Only compliance can batch update stage
        if (!$this->isComplianceTeam()) {
            $this->dispatch('error', message: 'Only Compliance Team can perform batch updates');
            return;
        }

        Deal::whereIn('id', $this->selectedDeals)->update([
            'stage' => $this->batchStageValue,
        ]);

        $this->loadDeals();
        $this->resetBatchState();

        $this->dispatch('success', message: 'Stage updated for ' . count($this->selectedDeals) . ' deal(s)');
    }

    /**
     * Execute batch delete
     */
    public function executeBatchDelete()
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            $this->dispatch('error', message: 'Unauthorized');
            return;
        }

        // Authorization: Only compliance can batch delete
        if (!$this->isComplianceTeam()) {
            $this->dispatch('error', message: 'Only Compliance Team can delete deals');
            return;
        }

        $count = count($this->selectedDeals);
        Deal::whereIn('id', $this->selectedDeals)->delete();

        $this->loadDeals();
        $this->resetBatchState();

        $this->dispatch('success', message: "{$count} deal(s) deleted successfully");
    }

    /**
     * Close modals and reset
     */
    public function closeBatchModal()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        $this->showBatchModal = false;
        $this->showConfirmModal = false;
    }

<<<<<<< HEAD
    public function confirmBatchAction(): void
=======
    /**
     * Confirm from confirmation modal
     */
    public function confirmBatchAction()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        match ($this->batchOperation) {
            'owner' => $this->executeBatchUpdateOwner(),
            'stage' => $this->executeBatchUpdateStage(),
            'delete' => $this->executeBatchDelete(),
        };
<<<<<<< HEAD
        $this->showConfirmModal = false;
    }

    public function setView(string $view): void
=======

        $this->showConfirmModal = false;
    }

    public function setView($view)
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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
<<<<<<< HEAD
=======
            // Always keep at least one column visible
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
            if (count($this->visibleColumns) > 1) {
                $this->visibleColumns = array_values(array_filter($this->visibleColumns, fn($c) => $c !== $column));
            }
        } else {
            $this->visibleColumns[] = $column;
        }
<<<<<<< HEAD
=======

>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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
<<<<<<< HEAD
=======
    /**
     * Stage color config — keys match DealStage enum values exactly.
     * DealStage values use spaces: 'doc sent', 'doc signed', etc.
     */
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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

<<<<<<< HEAD
<div x-data="{
=======
<div wire:poll.3s="refreshDeals" x-data="{
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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
<<<<<<< HEAD
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
=======
    onDragOver(e) { e.preventDefault(); }
}"
    class="space-y-6 w-full mx-auto p-4 sm:p-6 lg:p-8 antialiased text-slate-900 dark:text-slate-100">
    {{-- Loading bar --}}
    <div wire:loading.delay class="fixed top-0 left-0 right-0 h-0.5 bg-indigo-600 dark:bg-indigo-400 z-50 animate-pulse">
    </div>

    {{-- ── Header ── --}}
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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

<<<<<<< HEAD
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

=======
            {{-- View toggle + Export --}}
            <div class="flex items-center gap-2 shrink-0">
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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

<<<<<<< HEAD
=======
    {{-- ══════════════════════════════════════
         KANBAN BOARD VIEW
    ══════════════════════════════════════ --}}
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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

<<<<<<< HEAD
=======
    {{-- ══════════════════════════════════════
         TABLE LIST VIEW WITH BATCH OPERATIONS
    ══════════════════════════════════════ --}}
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    @if ($view === 'table')
        <div wire:key="table-view-{{ $currentPage }}-{{ $totalDeals }}">
            @include('components.deals.partials.⚡table-view', ['stageConfig' => $stageConfig])
        </div>
    @endif

<<<<<<< HEAD
    {{-- Batch Actions Floating Toolbar (unchanged) --}}
=======
    {{-- BATCH ACTIONS FLOATING TOOLBAR --}}
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    @if ($this->getSelectedCount() > 0)
        <div
            class="fixed bottom-0 left-0 right-0 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 shadow-xl z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="text-sm font-medium text-slate-900 dark:text-white">
                    {{ $this->getSelectedCount() }} deal(s) selected
                </div>

                <div class="flex items-center gap-3">
<<<<<<< HEAD
=======
                    {{-- Dropdown Container with Alpine Management --}}
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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

<<<<<<< HEAD
    {{-- MODALS (unchanged) --}}
=======
    {{-- MODALS --}}
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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
<<<<<<< HEAD
=======

</div>
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
