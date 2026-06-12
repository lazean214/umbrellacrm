<?php

use Livewire\Component;
use App\Models\Company;

new class extends Component
{
    public $showModal = false;
    public $isSaving = false;

    public $name = '';
    public $email = '';
    public $domain = '';
    public $phone = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:companies,name',
        'email' => 'nullable|email|max:255|unique:companies,email',
        'domain' => 'nullable|string|max:255|unique:companies,domain',
        'phone' => 'nullable|string|max:20',
    ];

    protected $messages = [
        'name.required' => 'Company name is required.',
        'name.unique' => 'A company with this name already exists.',
        'email.email' => 'Please provide a valid email address.',
        'email.unique' => 'This email is already registered.',
        'domain.unique' => 'This domain already exists.',
    ];

    public function openModal()
    {
        $this->resetForm();

        $this->resetValidation();
        $this->resetErrorBag();

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;

        $this->resetForm();

        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();

        try {
            $this->isSaving = true;

            Company::create([
                'name' => trim($this->name),
                'email' => $this->email
                    ? trim($this->email)
                    : null,
                'domain' => $this->domain
                    ? trim($this->domain)
                    : null,
                'phone' => $this->phone
                    ? trim($this->phone)
                    : null,
            ]);

            $this->dispatch('company-created');
            $this->dispatch('refresh-companies');

            session()->flash(
                'success',
                'Company created successfully!'
            );

            $this->closeModal();

        } catch (\Throwable $e) {

            session()->flash(
                'error',
                'Failed to create company.'
            );

            report($e);

        } finally {

            $this->isSaving = false;
        }
    }

    private function resetForm()
    {
        $this->reset([
            'name',
            'email',
            'domain',
            'phone',
            'isSaving',
        ]);
    }

    #[\Livewire\Attributes\On('company-created')]
    public function refreshCompanies()
    {
        $this->dispatch('refresh-companies');
    }
};

?>

<div>

    <!-- Trigger Button -->
    <button
        wire:click="openModal"
        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 font-medium text-white shadow-md transition hover:bg-blue-700 hover:shadow-lg">

        <svg class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24">

            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4">
            </path>
        </svg>

        New Company
    </button>

    @if($showModal)

        <!-- Overlay -->
        <div
            wire:click="closeModal"
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm">
        </div>

        <!-- Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">

            <div
                class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-slate-900">

                <!-- Header -->
                <div class="sticky top-0 flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 dark:border-slate-700 dark:from-slate-800 dark:to-slate-700">

                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                            Create New Company
                        </h2>

                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Add a new company to your CRM
                        </p>
                    </div>

                    <button
                        wire:click="closeModal"
                        class="text-slate-500 transition hover:text-slate-700 dark:text-slate-400 dark:hover:text-white">

                        <svg class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit="save" class="space-y-6 p-6">

                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">
                            Company Information
                        </h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                            <!-- Name -->
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Company Name *
                                </label>

                                <input
                                    type="text"
                                    wire:model.live="name"
                                    placeholder="Acme Corporation"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-900 transition focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">

                                @error('name')
                                    <p class="mt-1 text-sm text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    wire:model.live="email"
                                    placeholder="info@company.com"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-900 transition focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">

                                @error('email')
                                    <p class="mt-1 text-sm text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Phone Number
                                </label>

                                <input
                                    type="text"
                                    wire:model.live="phone"
                                    placeholder="+971 50 123 4567"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-900 transition focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">

                                @error('phone')
                                    <p class="mt-1 text-sm text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Domain -->
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Website Domain
                                </label>

                                <input
                                    type="text"
                                    wire:model.live="domain"
                                    placeholder="company.com"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-900 transition focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">

                                @error('domain')
                                    <p class="mt-1 text-sm text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end gap-3 border-t border-slate-200 pt-6 dark:border-slate-700">

                        <button
                            type="button"
                            wire:click="closeModal"
                            class="rounded-lg border border-slate-300 px-6 py-2 font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800">

                            Cancel
                        </button>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="rounded-lg bg-blue-600 px-6 py-2 font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">

                            <span wire:loading.remove wire:target="save">
                                Create Company
                            </span>

                            <span wire:loading wire:target="save">
                                Creating...
                            </span>
                        </button>

                    </div>
                </form>
            </div>
        </div>

    @endif
</div>