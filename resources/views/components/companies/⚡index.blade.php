<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Company;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $paginationTheme = 'tailwind';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    #[\Livewire\Attributes\On('company-created')]
    #[\Livewire\Attributes\On('import-completed')]
    #[\Livewire\Attributes\On('refresh-companies')]
    public function refreshCompanies()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection =
                $this->sortDirection === 'asc'
                    ? 'desc'
                    : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function delete($id)
    {
        Company::find($id)?->delete();

        session()->flash(
            'success',
            'Company deleted successfully!'
        );

        $this->resetPage();
    }

    public function getCompaniesProperty()
    {
        return Company::with([
                'contacts',
                'deals'
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where(
                        'name',
                        'like',
                        '%' . $this->search . '%'
                    )
                    ->orWhere(
                        'email',
                        'like',
                        '%' . $this->search . '%'
                    )
                    ->orWhere(
                        'domain',
                        'like',
                        '%' . $this->search . '%'
                    )
                    ->orWhere(
                        'phone',
                        'like',
                        '%' . $this->search . '%'
                    );
                });
            })
            ->orderBy(
                $this->sortBy,
                $this->sortDirection
            )
            ->paginate($this->perPage);
    }
};

?>

<div class="p-6 space-y-6">

    <!-- Header -->
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">
                Companies
            </h1>

            <p class="text-slate-600 dark:text-slate-400 mt-2">
                Manage your companies and their associated contacts and deals.
            </p>
        </div>

        <div class="flex gap-2">
           
            <livewire:companies.import />
            <livewire:companies.create />
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
            <p class="text-sm font-medium text-green-700 dark:text-green-300">
                {{ session('success') }}
            </p>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <p class="text-sm font-medium text-red-700 dark:text-red-300">
                {{ session('error') }}
            </p>
        </div>
    @endif

    <!-- Search + Per Page -->
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <!-- Search -->
            <div class="flex items-center gap-2 flex-1">
                <svg class="w-5 h-5 text-slate-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
                    </path>
                </svg>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name, email, domain, or phone..."
                    class="flex-1 bg-transparent text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none">
            </div>

            <!-- Per Page -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-slate-600 dark:text-slate-400">
                    Show
                </span>

                <select
                    wire:model.live="perPage"
                    class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">

                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

                <span class="text-sm text-slate-600 dark:text-slate-400">
                    entries
                </span>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">

        @if($this->companies->count())

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">

                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                        <tr>

                            @php
                                $columns = [
                                    'id' => 'ID',
                                    'name' => 'Name',
                                    'email' => 'Email',
                                    'phone' => 'Phone',
                                ];
                            @endphp

                            @foreach($columns as $field => $label)
                                <th
                                    wire:click="sort('{{ $field }}')"
                                    class="cursor-pointer px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition">

                                    <div class="flex items-center gap-2">
                                        {{ $label }}

                                        @if($sortBy === $field)
                                            <span>
                                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach

                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Contacts
                            </th>

                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Deals
                            </th>

                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Actions
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">

                        @foreach($this->companies as $company)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">

                                <td class="px-6 py-4">
                                    {{ $company->id }}
                                </td>

                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                    {{ $company->name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $company->email ?: '—' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $company->phone ?: '—' }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ $company->contacts->count() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-purple-100 px-2.5 py-1 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                        {{ $company->deals->count() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 flex items-center gap-3">

                                    <a
                                        href="{{ route('companies.show', $company) }}"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-200">

                                        View
                                    </a>

                                    <button
                                        wire:click="delete({{ $company->id }})"
                                        wire:confirm="Are you sure?"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200">

                                        Delete
                                    </button>

                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-6 py-4">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Showing
                        <span class="font-semibold">
                            {{ $this->companies->firstItem() ?? 0 }}
                        </span>
                        to
                        <span class="font-semibold">
                            {{ $this->companies->lastItem() ?? 0 }}
                        </span>
                        of
                        <span class="font-semibold">
                            {{ $this->companies->total() }}
                        </span>
                        companies
                    </p>

                    {{ $this->companies->links() }}

                </div>
            </div>

        @else

            <div class="p-12 text-center">

                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                    No companies found
                </h3>

                <p class="mt-2 text-slate-600 dark:text-slate-400">
                    @if($search)
                        Try adjusting your search criteria.
                    @else
                        Get started by creating your first company.
                    @endif
                </p>

            </div>

        @endif
    </div>
</div>