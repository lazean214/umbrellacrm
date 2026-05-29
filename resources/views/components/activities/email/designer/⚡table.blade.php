<?php

use Livewire\WithPagination;
use Livewire\Component;
use App\Models\EmailTemplate;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleStatus(int $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $template->update([
            'is_active' => ! $template->is_active,
        ]);
    }

    public function delete(int $id)
    {
        EmailTemplate::findOrFail($id)
            ->delete();

        session()->flash(
            'success',
            'Template deleted successfully.'
        );
    }

    public function getTemplatesProperty()
    {
        return EmailTemplate::query()
            ->when(
                $this->search,
                fn ($query) =>
                $query->where(function ($q) {
                    $q->where(
                        'name',
                        'like',
                        "%{$this->search}%"
                    )
                    ->orWhere(
                        'subject',
                        'like',
                        "%{$this->search}%"
                    );
                })
            )
            ->latest()
            ->paginate(10);
    }


};
?>

<div class="space-y-6">

    @if (session('success'))
        <div
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

        <div
            class="mb-5 flex items-center justify-between">

            <div>
                <h2
                    class="text-lg font-semibold text-slate-800">
                    Email Templates
                </h2>

                <p class="text-sm text-slate-500">
                    Manage CRM email templates
                </p>
            </div>

            <a
                href="{{ route('designer.create') }}"
                class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700">

                + Create Template

            </a>
        </div>

        {{-- Search --}}
        <div class="mb-5">

            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search template..."
                class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:border-emerald-500 focus:outline-none">

        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-xl border border-slate-200">

            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">

                    <tr>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Template
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Subject
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Status
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Created
                        </th>

                        <th
                            class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">

                    @forelse($this->templates as $template)

                        <tr
                            class="hover:bg-slate-50">

                            <td class="px-5 py-4">

                                <div
                                    class="font-medium text-slate-800">
                                    {{ $template->name }}
                                </div>

                                <div
                                    class="text-sm text-slate-500 line-clamp-1">
                                    {{ $template->description }}
                                </div>

                            </td>

                            <td class="px-5 py-4 text-sm text-slate-700">
                                {{ Str::limit(
                                    $template->subject,
                                    60
                                ) }}
                            </td>

                            <td class="px-5 py-4">

                                <button
                                    wire:click="toggleStatus({{ $template->id }})"
                                    class="rounded-full px-3 py-1 text-xs font-medium
                                    {{ $template->is_active
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-red-100 text-red-700' }}">

                                    {{ $template->is_active
                                        ? 'Active'
                                        : 'Inactive' }}

                                </button>

                            </td>

                            <td
                                class="px-5 py-4 text-sm text-slate-500">

                                {{ $template->created_at
                                    ?->format('M d, Y') }}

                            </td>

                            <td
                                class="px-5 py-4">

                                <div
                                    class="flex justify-end gap-2">

                                    <a
                                        href="{{ route(
                                            'designer.edit',
                                            $template->id
                                        ) }}"
                                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">

                                        Edit

                                    </a>

                                    <button
                                        wire:click="delete({{ $template->id }})"
                                        wire:confirm="Delete this template?"
                                        class="rounded-lg border border-red-200 px-3 py-2 text-sm text-red-600 hover:bg-red-50">

                                        Delete

                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="5"
                                class="px-5 py-10 text-center text-sm text-slate-500">

                                No templates found.

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-5">
            {{ $this->templates->links() }}
        </div>

    </div>

</div>