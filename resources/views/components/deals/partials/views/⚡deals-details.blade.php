<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Deal;
use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
use App\Enums\DealStage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new class extends Component
{
    use WithPagination;

    // Filters
    public ?int $filterUserId = null;
    public ?int $filterCompanyId = null;
    public ?int $filterContactId = null;
    public string $filterStage = '';
    public string $filterDateFrom = '';
    public string $filterDateTo = '';
    public string $reportView = 'master'; // master, weekly, tsv

    // Pagination
    public int $perPage = 15;
    public int $weeklyPerPage = 12;
    public int $tsvPerPage = 15;

    // Pagination pages for different tables
    public int $weeklyPage = 1;
    public int $tsvCompanyPage = 1;
    public int $tsvContactPage = 1;
    public int $tsvUserPage = 1;
    public int $tsvDealPage = 1;

    public function updatedFilterUserId(): void { $this->resetPage(); }
    public function updatedFilterCompanyId(): void { $this->resetPage(); }
    public function updatedFilterContactId(): void { $this->resetPage(); }
    public function updatedFilterStage(): void { $this->resetPage(); }
    public function updatedFilterDateFrom(): void { $this->resetPage(); }
    public function updatedFilterDateTo(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }
    public function updatedReportView(): void { $this->resetAllPages(); }
    public function updatedWeeklyPerPage(): void { $this->weeklyPage = 1; }
    public function updatedTsvPerPage(): void { $this->tsvCompanyPage = $this->tsvContactPage = $this->tsvUserPage = $this->tsvDealPage = 1; }

    public function resetAllPages(): void
    {
        $this->resetPage();
        $this->weeklyPage = 1;
        $this->tsvCompanyPage = 1;
        $this->tsvContactPage = 1;
        $this->tsvUserPage = 1;
        $this->tsvDealPage = 1;
    }

    #[Computed]
    public function deals()
    {
        return Deal::query()
            ->with(['user:id,name', 'companies', 'contacts'])
            ->when($this->filterUserId, fn ($q) => $q->where('user_id', $this->filterUserId))
            ->when($this->filterStage !== '', fn ($q) => $q->where('stage', $this->filterStage))
            ->when($this->filterCompanyId, fn ($q) => $q->whereHas('companies', fn ($q2) => $q2->where('companies.id', $this->filterCompanyId)))
            ->when($this->filterContactId, fn ($q) => $q->whereHas('contacts', fn ($q2) => $q2->where('contacts.id', $this->filterContactId)))
            ->when($this->filterDateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function totalPipelineValue(): string
    {
        $total = $this->getBaseQuery()->sum('amount') ?? 0;
        return number_format((float) $total, 2);
    }

    #[Computed]
    public function totalActiveDeals(): int
    {
        return $this->getBaseQuery()->count();
    }

    #[Computed]
    public function averageMargin(): string
    {
        $avg = $this->getBaseQuery()->avg('margin_agreed') ?? 0;
        return number_format((float) $avg, 2);
    }

    #[Computed]
    public function weeklySummary()
    {
        $startDate = $this->filterDateFrom ? Carbon::parse($this->filterDateFrom) : Carbon::now()->subMonths(3);
        $endDate = $this->filterDateTo ? Carbon::parse($this->filterDateTo) : Carbon::now();

        $query = Deal::query()
            ->when($this->filterUserId, fn ($q) => $q->where('user_id', $this->filterUserId))
            ->when($this->filterCompanyId, fn ($q) => $q->whereHas('companies', fn ($q2) => $q2->where('companies.id', $this->filterCompanyId)))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('YEARWEEK(created_at) as week_key'),
                DB::raw('MIN(DATE(created_at)) as week_start'),
                DB::raw('COUNT(CASE WHEN stage = "paid" THEN 1 END) as paid_deals'),
                DB::raw('SUM(CASE WHEN stage = "paid" THEN amount ELSE 0 END) as paid_amount'),
                DB::raw('COUNT(CASE WHEN stage = "doc_sent" AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as created_this_week'),
                DB::raw('COUNT(*) as total_deals')
            )
            ->groupBy('week_key')
            ->orderBy('week_key', 'desc');

        $totalCount = $query->count();
        
        $data = $query
            ->skip(($this->weeklyPage - 1) * $this->weeklyPerPage)
            ->limit($this->weeklyPerPage)
            ->get()
            ->map(function ($item) {
                $item->week_range = Carbon::parse($item->week_start)->format('d M') . ' - ' . 
                                    Carbon::parse($item->week_start)->addDays(6)->format('d M Y');
                return $item;
            });

        return (object) [
            'data' => $data,
            'total' => $totalCount,
            'perPage' => $this->weeklyPerPage,
            'currentPage' => $this->weeklyPage,
            'lastPage' => ceil($totalCount / $this->weeklyPerPage),
        ];
    }

    #[Computed]
    public function tsvReport()
    {
        $query = $this->getBaseQuery()
            ->with(['user:id,name', 'companies', 'contacts']);

        return [
            'by_company' => $this->getTsvByCompanyPaginated($query),
            'by_contact' => $this->getTsvByContactPaginated($query),
            'by_user' => $this->getTsvByUserPaginated($query),
            'by_deal' => $this->getTsvByDealPaginated($query),
        ];
    }

    private function getTsvByCompanyPaginated($query)
    {
        $deals = $query->get();
        $summary = [];

        foreach ($deals as $deal) {
            foreach ($deal->companies as $company) {
                if (!isset($summary[$company->id])) {
                    $summary[$company->id] = [
                        'name' => $company->name,
                        'total_value' => 0,
                        'deal_count' => 0,
                        'avg_margin' => [],
                    ];
                }
                $summary[$company->id]['total_value'] += (float) ($deal->amount ?? 0);
                $summary[$company->id]['deal_count']++;
                if ($deal->margin_agreed !== null) {
                    $summary[$company->id]['avg_margin'][] = (float) $deal->margin_agreed;
                }
            }
        }

        $collection = collect($summary)->map(fn ($item) => [
            'name' => $item['name'],
            'total_value' => (float) $item['total_value'],
            'deal_count' => (int) $item['deal_count'],
            'avg_margin' => !empty($item['avg_margin']) ? number_format(array_sum($item['avg_margin']) / count($item['avg_margin']), 2) : '0.00',
        ])->sortByDesc('total_value')->values();

        $total = $collection->count();
        $data = $collection->slice(($this->tsvCompanyPage - 1) * $this->tsvPerPage, $this->tsvPerPage);

        return (object) [
            'data' => $data,
            'total' => $total,
            'perPage' => $this->tsvPerPage,
            'currentPage' => $this->tsvCompanyPage,
            'lastPage' => ceil($total / $this->tsvPerPage),
        ];
    }

    private function getTsvByContactPaginated($query)
    {
        $deals = $query->get();
        $summary = [];

        foreach ($deals as $deal) {
            foreach ($deal->contacts as $contact) {
                if (!isset($summary[$contact->id])) {
                    $summary[$contact->id] = [
                        'name' => $contact->first_name . ' ' . $contact->last_name,
                        'email' => $contact->email ?? '—',
                        'company' => $contact->company?->name ?? '—',
                        'total_value' => 0,
                        'deal_count' => 0,
                    ];
                }
                $summary[$contact->id]['total_value'] += (float) ($deal->amount ?? 0);
                $summary[$contact->id]['deal_count']++;
            }
        }

        $collection = collect($summary)->map(fn ($item) => [
            'name' => $item['name'],
            'email' => $item['email'],
            'company' => $item['company'],
            'total_value' => (float) $item['total_value'],
            'deal_count' => (int) $item['deal_count'],
        ])->sortByDesc('total_value')->values();

        $total = $collection->count();
        $data = $collection->slice(($this->tsvContactPage - 1) * $this->tsvPerPage, $this->tsvPerPage);

        return (object) [
            'data' => $data,
            'total' => $total,
            'perPage' => $this->tsvPerPage,
            'currentPage' => $this->tsvContactPage,
            'lastPage' => ceil($total / $this->tsvPerPage),
        ];
    }

    private function getTsvByUserPaginated($query)
    {
        $deals = $query->get()
            ->groupBy('user_id')
            ->map(fn ($deals, $userId) => [
                'name' => $deals->first()->user?->name ?? 'Unassigned',
                'total_value' => (float) $deals->sum('amount'),
                'deal_count' => $deals->count(),
                'avg_margin' => number_format((float) ($deals->avg('margin_agreed') ?? 0), 2),
                'avg_deal_size' => number_format((float) ($deals->avg('amount') ?? 0), 2),
            ])
            ->sortByDesc('total_value')
            ->values();

        $total = $deals->count();
        $data = $deals->slice(($this->tsvUserPage - 1) * $this->tsvPerPage, $this->tsvPerPage);

        return (object) [
            'data' => $data,
            'total' => $total,
            'perPage' => $this->tsvPerPage,
            'currentPage' => $this->tsvUserPage,
            'lastPage' => ceil($total / $this->tsvPerPage),
        ];
    }

    private function getTsvByDealPaginated($query)
    {
        $deals = $query->get()
            ->map(fn ($deal) => [
                'name' => $deal->name ?? '—',
                'owner' => $deal->user?->name ?? '—',
                'company' => $deal->companies->first()?->name ?? '—',
                'contact' => $deal->contacts->first()?->name ?? '—',
                'stage' => ucwords($deal->stage->value ?? 'unknown'),
                'amount' => (float) ($deal->amount ?? 0),
                'margin' => $deal->margin_agreed !== null ? (float) $deal->margin_agreed : null,
                'created_date' => $deal->created_at?->format('Y-m-d') ?? '—',
            ])
            ->sortByDesc('amount')
            ->values();

        $total = $deals->count();
        $data = $deals->slice(($this->tsvDealPage - 1) * $this->tsvPerPage, $this->tsvPerPage);

        return (object) [
            'data' => $data,
            'total' => $total,
            'perPage' => $this->tsvPerPage,
            'currentPage' => $this->tsvDealPage,
            'lastPage' => ceil($total / $this->tsvPerPage),
        ];
    }

    #[Computed]
    public function dealHistory()
    {
        return Deal::query()
            ->with(['user:id,name', 'companies'])
            ->whereIn('stage', [DealStage::PAID, DealStage::COMPLIANT])
            ->when($this->filterUserId, fn ($q) => $q->where('user_id', $this->filterUserId))
            ->when($this->filterCompanyId, fn ($q) => $q->whereHas('companies', fn ($q2) => $q2->where('companies.id', $this->filterCompanyId)))
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->map(function ($deal) {
                $deal->history_type = $deal->stage === DealStage::PAID ? 'paid' : 'completed';
                return $deal;
            });
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function companies()
    {
        return Company::orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function contacts()
    {
        return Contact::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']);
    }

    #[Computed]
    public function stages(): array
    {
        return DealStage::cases();
    }

    private function getBaseQuery()
    {
        return Deal::query()
            ->when($this->filterUserId, fn ($q) => $q->where('user_id', $this->filterUserId))
            ->when($this->filterStage !== '', fn ($q) => $q->where('stage', $this->filterStage))
            ->when($this->filterCompanyId, fn ($q) => $q->whereHas('companies', fn ($q2) => $q2->where('companies.id', $this->filterCompanyId)))
            ->when($this->filterContactId, fn ($q) => $q->whereHas('contacts', fn ($q2) => $q2->where('contacts.id', $this->filterContactId)))
            ->when($this->filterDateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->filterDateTo));
    }

    public function resetFilters(): void
    {
        $this->filterUserId = null;
        $this->filterCompanyId = null;
        $this->filterContactId = null;
        $this->filterStage = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->reportView = 'master';
        $this->resetAllPages();
    }

    public function exportTsv($type)
    {
        // Get the full unfiltered data for export
        $query = $this->getBaseQuery()->with(['user:id,name', 'companies', 'contacts']);
        
        $data = match($type) {
            'by_company' => $this->getTsvByCompanyPaginated($query)->data->toArray(),
            'by_contact' => $this->getTsvByContactPaginated($query)->data->toArray(),
            'by_user' => $this->getTsvByUserPaginated($query)->data->toArray(),
            'by_deal' => $this->getTsvByDealPaginated($query)->data->toArray(),
            default => []
        };

        if (empty($data)) return;

        $filename = "tsv_report_{$type}_" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($data, $type) {
            $file = fopen('php://output', 'w');
            
            switch($type) {
                case 'by_company':
                    fputcsv($file, ['Company', 'Total Value (£)', 'Deal Count', 'Avg Margin (%)']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row['name'],
                            number_format((float) $row['total_value'], 2),
                            $row['deal_count'],
                            $row['avg_margin']
                        ]);
                    }
                    break;
                case 'by_contact':
                    fputcsv($file, ['Contact', 'Email', 'Company', 'Total Value (£)', 'Deal Count']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row['name'],
                            $row['email'],
                            $row['company'],
                            number_format((float) $row['total_value'], 2),
                            $row['deal_count']
                        ]);
                    }
                    break;
                case 'by_user':
                    fputcsv($file, ['User', 'Total Value (£)', 'Deal Count', 'Avg Margin (%)', 'Avg Deal Size (£)']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row['name'],
                            number_format((float) $row['total_value'], 2),
                            $row['deal_count'],
                            $row['avg_margin'],
                            $row['avg_deal_size']
                        ]);
                    }
                    break;
                case 'by_deal':
                    fputcsv($file, ['Deal Name', 'Owner', 'Company', 'Contact', 'Stage', 'Amount (£)', 'Margin (%)', 'Created Date']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row['name'],
                            $row['owner'],
                            $row['company'],
                            $row['contact'],
                            $row['stage'],
                            number_format((float) $row['amount'], 2),
                            $row['margin'] !== null ? (string) $row['margin'] : '—',
                            $row['created_date']
                        ]);
                    }
                    break;
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
};
?>

<div class="flex flex-col gap-6">
    {{-- View Toggle --}}
    <div class="flex gap-2 rounded-xl border border-neutral-200 bg-white p-2 dark:border-neutral-700 dark:bg-neutral-900">
        <button wire:click="$set('reportView', 'master')" 
            class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-all {{ $reportView === 'master' ? 'bg-indigo-600 text-white shadow-sm' : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800' }}">
            📊 Master Report
        </button>
        <button wire:click="$set('reportView', 'weekly')" 
            class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-all {{ $reportView === 'weekly' ? 'bg-indigo-600 text-white shadow-sm' : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800' }}">
            📈 Weekly Summary
        </button>
        <button wire:click="$set('reportView', 'tsv')" 
            class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-all {{ $reportView === 'tsv' ? 'bg-indigo-600 text-white shadow-sm' : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800' }}">
            💰 TSV Report
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="flex flex-wrap items-start gap-3 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
        <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400 mr-1">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Filters
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Owner</label>
            <select wire:model.live="filterUserId" class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">
                <option value="">All Owners</option>
                @foreach ($this->users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Company</label>
            <select wire:model.live="filterCompanyId" class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">
                <option value="">All Companies</option>
                @foreach ($this->companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Contact</label>
            <select wire:model.live="filterContactId" class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">
                <option value="">All Contacts</option>
                @foreach ($this->contacts as $contact)
                    <option value="{{ $contact->id }}">{{ $contact->first_name }} {{ $contact->last_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Stage</label>
            <select wire:model.live="filterStage" class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">
                <option value="">All Stages</option>
                @foreach ($this->stages as $stage)
                    <option value="{{ $stage->value }}">{{ ucwords($stage->value) }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Date From</label>
            <input type="date" wire:model.live="filterDateFrom" class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Date To</label>
            <input type="date" wire:model.live="filterDateTo" class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">
        </div>

        @if ($filterUserId || $filterCompanyId || $filterContactId || $filterStage !== '' || $filterDateFrom !== '' || $filterDateTo !== '')
            <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 self-end rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Clear
            </button>
        @endif
    </div>

    {{-- KPI Cards --}}
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-neutral-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Total Pipeline Value</p>
            <p class="mt-2 text-2xl font-bold text-neutral-900">£{{ $this->totalPipelineValue }}</p>
            <p class="mt-1 text-xs text-neutral-400">Sum of all deal amounts</p>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Active Deals</p>
            <p class="mt-2 text-2xl font-bold text-neutral-900">{{ $this->totalActiveDeals }}</p>
            <p class="mt-1 text-xs text-neutral-400">Deals matching current filters</p>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Avg. Margin Agreed</p>
            <p class="mt-2 text-2xl font-bold text-neutral-900">{{ $this->averageMargin }}%</p>
            <p class="mt-1 text-xs text-neutral-400">Average across filtered deals</p>
        </div>
    </div>

    {{-- Master Report View --}}
    @if($reportView === 'master')
    <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
        <div class="flex items-center justify-between border-b border-neutral-100 px-5 py-3">
            <h2 class="text-sm font-semibold text-neutral-700">Deal Master Report</h2>
            <div class="flex items-center gap-3">
                <select wire:model.live="perPage" class="rounded-lg border border-neutral-200 px-2 py-1 text-xs">
                    <option value="10">10 per page</option>
                    <option value="15">15 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
                <span class="text-xs text-neutral-400">{{ $this->deals->total() }} total deals</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 bg-neutral-50 text-left">
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Deal Name</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Owner</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Company</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Stage</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Value</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Date Logged</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse ($this->deals as $deal)
                        <tr class="transition-colors hover:bg-neutral-50">
                            <td class="px-5 py-3 font-medium">
                                <a href="{{ route('deals.show', $deal) }}" class="hover:text-indigo-600">
                                    {{ $deal->name }}
                                </a>
                            </td>
                            <td class="px-5 py-3 text-neutral-600">{{ $deal->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-neutral-600">{{ $deal->companies->first()?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-neutral-100">
                                    {{ ucwords($deal->stage->value) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-medium tabular-nums">£{{ number_format((float) $deal->amount, 2) }}</td>
                            <td class="px-5 py-3 text-neutral-500">{{ $deal->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-neutral-400">
                                No deals found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-neutral-100 px-5 py-3">
            {{ $this->deals->links() }}
        </div>
    </div>

    {{-- Recent Deal History --}}
    <div class="rounded-xl border border-neutral-200 bg-white">
        <div class="border-b border-neutral-100 px-5 py-3">
            <h2 class="text-sm font-semibold text-neutral-700">🔄 Recent Deal History (Paid/Completed)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 bg-neutral-50 text-left">
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Deal Name</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Owner</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Company</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Value</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse ($this->dealHistory as $deal)
                        <tr class="transition-colors hover:bg-neutral-50">
                            <td class="px-5 py-3 font-medium">{{ $deal->name }}</td>
                            <td class="px-5 py-3 text-neutral-600">{{ $deal->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-neutral-600">{{ $deal->companies->first()?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $deal->history_type === 'paid' ? 'bg-green-100 text-green-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ ucfirst($deal->history_type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-medium">£{{ number_format((float) $deal->amount, 2) }}</td>
                            <td class="px-5 py-3 text-neutral-500">{{ $deal->updated_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-neutral-400">
                                No recent deal activity.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Weekly Summary View --}}
    @if($reportView === 'weekly')
    <div class="rounded-xl border border-neutral-200 bg-white">
        <div class="flex items-center justify-between border-b border-neutral-100 px-5 py-3">
            <h2 class="text-sm font-semibold text-neutral-700">📅 Weekly Deal Summary</h2>
            <select wire:model.live="weeklyPerPage" class="rounded-lg border border-neutral-200 px-2 py-1 text-xs">
                <option value="6">6 per page</option>
                <option value="12">12 per page</option>
                <option value="24">24 per page</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 bg-neutral-50 text-left">
                        <th class="px-5 py-3 text-xs font-semibold uppercase">Week</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Total Deals</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Created This Week</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Paid Deals</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Paid Amount (£)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse ($this->weeklySummary->data as $week)
                        <tr class="transition-colors hover:bg-neutral-50">
                            <td class="px-5 py-3 font-medium">{{ $week->week_range }}</td>
                            <td class="px-5 py-3 text-right">{{ $week->total_deals }}</td>
                            <td class="px-5 py-3 text-right">
                                <span class="text-emerald-600">{{ $week->created_this_week }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">{{ $week->paid_deals }}</td>
                            <td class="px-5 py-3 text-right font-medium">£{{ number_format((float) $week->paid_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-neutral-400">
                                No weekly data available for selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-neutral-100 px-5 py-3">
            <div class="flex items-center justify-between">
                <span class="text-xs text-neutral-500">
                    Showing {{ ($this->weeklySummary->currentPage - 1) * $this->weeklySummary->perPage + 1 }} to {{ min($this->weeklySummary->currentPage * $this->weeklySummary->perPage, $this->weeklySummary->total) }} of {{ $this->weeklySummary->total }} weeks
                </span>
                <div class="flex gap-1">
                    @if($this->weeklySummary->currentPage > 1)
                        <button wire:click="$set('weeklyPage', 1)" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">«</button>
                        <button wire:click="$set('weeklyPage', {{ $this->weeklySummary->currentPage - 1 }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">‹</button>
                    @endif
                    <span class="flex items-center px-2 py-1 text-xs font-medium">{{ $this->weeklySummary->currentPage }}/{{ $this->weeklySummary->lastPage }}</span>
                    @if($this->weeklySummary->currentPage < $this->weeklySummary->lastPage)
                        <button wire:click="$set('weeklyPage', {{ $this->weeklySummary->currentPage + 1 }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">›</button>
                        <button wire:click="$set('weeklyPage', {{ $this->weeklySummary->lastPage }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">»</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TSV Report View --}}
    @if($reportView === 'tsv')
    <div class="space-y-6">
        {{-- By Company --}}
        <div class="rounded-xl border border-neutral-200 bg-white">
            <div class="flex items-center justify-between border-b border-neutral-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-neutral-700">🏢 TSV by Company</h2>
                <button wire:click="exportTsv('by_company')" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                    📥 Export CSV
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-100 bg-neutral-50 text-left">
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Company</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Total Value (£)</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Deal Count</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Avg Margin (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($this->tsvReport['by_company']->data as $company)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-5 py-3 font-medium">{{ $company['name'] }}</td>
                                <td class="px-5 py-3 text-right">£{{ number_format($company['total_value'], 2) }}</td>
                                <td class="px-5 py-3 text-right">{{ $company['deal_count'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $company['avg_margin'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-neutral-400">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-neutral-100 px-5 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-neutral-500">Showing {{ ($this->tsvReport['by_company']->currentPage - 1) * $this->tsvReport['by_company']->perPage + 1 }} to {{ min($this->tsvReport['by_company']->currentPage * $this->tsvReport['by_company']->perPage, $this->tsvReport['by_company']->total) }} of {{ $this->tsvReport['by_company']->total }}</span>
                    <div class="flex gap-1">
                        @if($this->tsvReport['by_company']->currentPage > 1)
                            <button wire:click="$set('tsvCompanyPage', 1)" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">«</button>
                            <button wire:click="$set('tsvCompanyPage', {{ $this->tsvReport['by_company']->currentPage - 1 }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">‹</button>
                        @endif
                        <span class="flex items-center px-2 py-1 text-xs font-medium">{{ $this->tsvReport['by_company']->currentPage }}/{{ $this->tsvReport['by_company']->lastPage }}</span>
                        @if($this->tsvReport['by_company']->currentPage < $this->tsvReport['by_company']->lastPage)
                            <button wire:click="$set('tsvCompanyPage', {{ $this->tsvReport['by_company']->currentPage + 1 }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">›</button>
                            <button wire:click="$set('tsvCompanyPage', {{ $this->tsvReport['by_company']->lastPage }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">»</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- By Contact --}}
        <div class="rounded-xl border border-neutral-200 bg-white">
            <div class="flex items-center justify-between border-b border-neutral-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-neutral-700">👤 TSV by Contact</h2>
                <button wire:click="exportTsv('by_contact')" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                    📥 Export CSV
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-100 bg-neutral-50 text-left">
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Contact</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Email</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Company</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Total Value (£)</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Deal Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($this->tsvReport['by_contact']->data as $contact)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-5 py-3 font-medium">{{ $contact['name'] }}</td>
                                <td class="px-5 py-3 text-neutral-600">{{ $contact['email'] }}</td>
                                <td class="px-5 py-3 text-neutral-600">{{ $contact['company'] }}</td>
                                <td class="px-5 py-3 text-right">£{{ number_format($contact['total_value'], 2) }}</td>
                                <td class="px-5 py-3 text-right">{{ $contact['deal_count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-neutral-400">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-neutral-100 px-5 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-neutral-500">Showing {{ ($this->tsvReport['by_contact']->currentPage - 1) * $this->tsvReport['by_contact']->perPage + 1 }} to {{ min($this->tsvReport['by_contact']->currentPage * $this->tsvReport['by_contact']->perPage, $this->tsvReport['by_contact']->total) }} of {{ $this->tsvReport['by_contact']->total }}</span>
                    <div class="flex gap-1">
                        @if($this->tsvReport['by_contact']->currentPage > 1)
                            <button wire:click="$set('tsvContactPage', 1)" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">«</button>
                            <button wire:click="$set('tsvContactPage', {{ $this->tsvReport['by_contact']->currentPage - 1 }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">‹</button>
                        @endif
                        <span class="flex items-center px-2 py-1 text-xs font-medium">{{ $this->tsvReport['by_contact']->currentPage }}/{{ $this->tsvReport['by_contact']->lastPage }}</span>
                        @if($this->tsvReport['by_contact']->currentPage < $this->tsvReport['by_contact']->lastPage)
                            <button wire:click="$set('tsvContactPage', {{ $this->tsvReport['by_contact']->currentPage + 1 }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">›</button>
                            <button wire:click="$set('tsvContactPage', {{ $this->tsvReport['by_contact']->lastPage }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">»</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- By User --}}
        <div class="rounded-xl border border-neutral-200 bg-white">
            <div class="flex items-center justify-between border-b border-neutral-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-neutral-700">👥 TSV by User</h2>
                <button wire:click="exportTsv('by_user')" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                    📥 Export CSV
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-100 bg-neutral-50 text-left">
                            <th class="px-5 py-3 text-xs font-semibold uppercase">User</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Total Value (£)</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Deal Count</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Avg Margin (%)</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Avg Deal Size (£)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($this->tsvReport['by_user']->data as $user)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-5 py-3 font-medium">{{ $user['name'] }}</td>
                                <td class="px-5 py-3 text-right">£{{ $user['total_value'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $user['deal_count'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $user['avg_margin'] }}%</td>
                                <td class="px-5 py-3 text-right">£{{ $user['avg_deal_size'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-neutral-400">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-neutral-100 px-5 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-neutral-500">Showing {{ ($this->tsvReport['by_user']->currentPage - 1) * $this->tsvReport['by_user']->perPage + 1 }} to {{ min($this->tsvReport['by_user']->currentPage * $this->tsvReport['by_user']->perPage, $this->tsvReport['by_user']->total) }} of {{ $this->tsvReport['by_user']->total }}</span>
                    <div class="flex gap-1">
                        @if($this->tsvReport['by_user']->currentPage > 1)
                            <button wire:click="$set('tsvUserPage', 1)" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">«</button>
                            <button wire:click="$set('tsvUserPage', {{ $this->tsvReport['by_user']->currentPage - 1 }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">‹</button>
                        @endif
                        <span class="flex items-center px-2 py-1 text-xs font-medium">{{ $this->tsvReport['by_user']->currentPage }}/{{ $this->tsvReport['by_user']->lastPage }}</span>
                        @if($this->tsvReport['by_user']->currentPage < $this->tsvReport['by_user']->lastPage)
                            <button wire:click="$set('tsvUserPage', {{ $this->tsvReport['by_user']->currentPage + 1 }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">›</button>
                            <button wire:click="$set('tsvUserPage', {{ $this->tsvReport['by_user']->lastPage }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">»</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Deal List --}}
        <div class="rounded-xl border border-neutral-200 bg-white">
            <div class="flex items-center justify-between border-b border-neutral-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-neutral-700">📋 Detailed Deal List</h2>
                <button wire:click="exportTsv('by_deal')" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                    📥 Export CSV
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-100 bg-neutral-50 text-left">
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Deal Name</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Owner</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Company</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Contact</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Stage</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Amount (£)</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase">Margin (%)</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($this->tsvReport['by_deal']->data as $deal)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-5 py-3 font-medium">{{ $deal['name'] }}</td>
                                <td class="px-5 py-3 text-neutral-600">{{ $deal['owner'] }}</td>
                                <td class="px-5 py-3 text-neutral-600">{{ $deal['company'] }}</td>
                                <td class="px-5 py-3 text-neutral-600">{{ $deal['contact'] }}</td>
                                <td class="px-5 py-3">{{ $deal['stage'] }}</td>
                                <td class="px-5 py-3 text-right">£{{ number_format($deal['amount'], 2) }}</td>
                                <td class="px-5 py-3 text-right">{{ $deal['margin'] !== null ? $deal['margin'] . '%' : '—' }}</td>
                                <td class="px-5 py-3 text-neutral-500">{{ $deal['created_date'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-neutral-400">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-neutral-100 px-5 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-neutral-500">Showing {{ ($this->tsvReport['by_deal']->currentPage - 1) * $this->tsvReport['by_deal']->perPage + 1 }} to {{ min($this->tsvReport['by_deal']->currentPage * $this->tsvReport['by_deal']->perPage, $this->tsvReport['by_deal']->total) }} of {{ $this->tsvReport['by_deal']->total }}</span>
                    <div class="flex gap-1">
                        @if($this->tsvReport['by_deal']->currentPage > 1)
                            <button wire:click="$set('tsvDealPage', 1)" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">«</button>
                            <button wire:click="$set('tsvDealPage', {{ $this->tsvReport['by_deal']->currentPage - 1 }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">‹</button>
                        @endif
                        <span class="flex items-center px-2 py-1 text-xs font-medium">{{ $this->tsvReport['by_deal']->currentPage }}/{{ $this->tsvReport['by_deal']->lastPage }}</span>
                        @if($this->tsvReport['by_deal']->currentPage < $this->tsvReport['by_deal']->lastPage)
                            <button wire:click="$set('tsvDealPage', {{ $this->tsvReport['by_deal']->currentPage + 1 }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">›</button>
                            <button wire:click="$set('tsvDealPage', {{ $this->tsvReport['by_deal']->lastPage }})" class="rounded border border-neutral-300 px-2 py-1 text-xs font-medium hover:bg-neutral-100">»</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>