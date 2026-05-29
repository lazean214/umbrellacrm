<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Deal;
use App\Models\EmailTemplate;
use App\Services\DealEmailService;
use App\Services\EmailTemplateParser;

new class extends Component
{
    use WithFileUploads;

    public Deal $deal;

    public $contact = null;
    public $company = null;

    public $templates = [];

    public ?int $templateId = null;

    public string $toEmail = '';

    public string $subject = '';
    public string $body = '';

    public bool $showPreview = false;

    /*
    |--------------------------------------------------------------------------
    | Attachments
    |--------------------------------------------------------------------------
    */

    // uploaded manually
    public $attachments = [];

    // template attachments
    public array $selectedTemplateAttachments = [];

    public $templateAttachments = [];

    public function mount(
        int $dealId
    ) {

        $this->deal =
            Deal::with([
                'contacts',
                'companies',
                'emailLogs.user',
                'emailLogs.template',
            ])->findOrFail(
                $dealId
            );

        $this->contact =
            $this->deal->primaryContact();

        $this->company =
            $this->deal->primaryCompany();

        $this->toEmail =
            $this->contact?->email ?? '';

        $this->templates =
            EmailTemplate::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->get();
    }

    public function updatedTemplateId()
    {
        if (! $this->templateId) {
            return;
        }

        $template =
            EmailTemplate::with(
                'attachments'
            )->find(
                $this->templateId
            );

        if (! $template) {
            return;
        }

        $this->subject =
            EmailTemplateParser::parse(
                $template->subject,
                $this->deal,
                $this->contact,
                $this->company,
                auth()->user(),
            );

        $this->body =
            EmailTemplateParser::parse(
                $template->body,
                $this->deal,
                $this->contact,
                $this->company,
                auth()->user(),
            );

        /*
        |--------------------------------------------------------------------------
        | Auto-load template attachments
        |--------------------------------------------------------------------------
        */

        $this->templateAttachments =
            $template
                ->attachments
                ->toArray();

        // auto select all
        $this->selectedTemplateAttachments =
            collect(
                $template->attachments
            )
            ->pluck('id')
            ->toArray();
    }

    public function send()
    {
        $this->validate([
            'templateId' =>
                'required',

            'toEmail' =>
                'required|email',

            'subject' =>
                'required|max:255',

            'body' =>
                'required|min:10',

            'attachments.*' =>
                'file|max:10240',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Save temporary uploads
        |--------------------------------------------------------------------------
        */

        $manualAttachments = [];

        foreach (
            $this->attachments
            as $file
        ) {

            $manualAttachments[] =
                $file->store(
                    'private/email-temp-attachments'
                );
        }

        DealEmailService::send(
            deal: $this->deal,

            templateId:
                $this->templateId,

            to:
                $this->toEmail,

            customSubject:
                $this->subject,

            customBody:
                $this->body,

            selectedTemplateAttachments:
                $this->selectedTemplateAttachments,

            manualAttachments:
                $manualAttachments,
        );

        $this->deal->refresh();

        $this->attachments = [];

        session()->flash(
            'success',
            'Email sent successfully.'
        );
    }
};
?>

<div class="space-y-6">

    @if (session('success'))
        <div
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6">

        {{-- Email Composer --}}
        <div class="col-span-8">

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">

                <div
                    class="mb-5 flex items-center justify-between">

                    <div>
                        <h2
                            class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                            Welcome Email
                        </h2>

                        <p
                            class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $deal->name }}
                        </p>
                    </div>

                    <button
                    wire:click="send"
                    wire:loading.attr="disabled"
                    wire:target="send"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2 text-sm font-medium text-white transition disabled:cursor-not-allowed disabled:opacity-70 hover:bg-emerald-700">

                    {{-- Normal State --}}
                    <span
                        wire:loading.remove
                        wire:target="send">

                        Send Email

                    </span>

                    {{-- Sending State --}}
                    <span
                        wire:loading.flex
                        wire:target="send"
                        class="items-center gap-2">

                        <svg
                            class="h-4 w-4 animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24">

                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4">
                            </circle>

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                            </path>

                        </svg>

                        Sending...

                    </span>

                </button>
                </div>

                <div class="space-y-4">

                    {{-- Recipient --}}
                    <div>
                        <label
                            class="text-xs font-bold uppercase tracking-wider">
                            To
                        </label>

                        <input
                            wire:model="toEmail"
                            type="email"
                            class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                    </div>

                    {{-- Template --}}
                    <div>
                        <label
                            class="text-xs font-bold uppercase tracking-wider">
                            Template
                        </label>

                        <select
                            wire:model.live="templateId"
                            class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">

                            <option value="">
                                Select Template
                            </option>

                            @foreach ($templates as $template)
                                <option
                                    value="{{ $template->id }}">
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Subject --}}
                    <div>
                        <label
                            class="text-xs font-bold uppercase tracking-wider">
                            Subject
                        </label>

                        <input
                            wire:model.live="subject"
                            type="text"
                            class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                    </div>

                    {{-- Body --}}
                    <div>
                        <label
                            class="text-xs font-bold uppercase tracking-wider">
                            Email Body
                        </label>

                        <textarea
                            wire:model.live="body"
                            rows="16"
                            class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"></textarea>
                    </div>
                <div class="space-y-5">

                    {{-- TEMPLATE ATTACHMENTS --}}
                    @if(count($templateAttachments))

                        <div>

                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Template Attachments
                            </label>

                            <div class="mt-3 space-y-2">

                                @foreach(
                                    $templateAttachments
                                    as $file
                                )

                                    <label
                                        class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-3 cursor-pointer">

                                        <div class="flex items-center gap-3">

                                            <input
                                                type="checkbox"
                                                wire:model.live="selectedTemplateAttachments"
                                                value="{{ $file['id'] }}"
                                                class="rounded border-slate-300">

                                            <div>

                                                <div class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                                    {{ $file['file_name'] }}
                                                </div>

                                                <div class="text-xs text-slate-500">
                                                    {{ number_format($file['file_size'] / 1024, 1) }} KB
                                                </div>

                                            </div>

                                        </div>

                                    </label>

                                @endforeach

                            </div>

                        </div>

                    @endif

                    {{-- EXTRA ATTACHMENTS --}}
                    <div>

                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Extra Attachments
                        </label>

                        <input
                            type="file"
                            wire:model="attachments"
                            multiple
                            class="mt-2 block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3">

                        <div
                            wire:loading
                            wire:target="attachments"
                            class="mt-2 text-xs text-slate-500">

                            Uploading...

                        </div>

                        @if($attachments)

                            <div class="mt-4 space-y-2">

                                @foreach(
                                    $attachments
                                    as $file
                                )

                                    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">

                                        {{ $file->getClientOriginalName() }}

                                    </div>

                                @endforeach

                            </div>

                        @endif

                    </div>

                </div>
                </div>
            </div>

            {{-- Preview --}}
            <div
                class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">

                <div
                    class="mb-4 flex items-center justify-between">

                    <h3
                        class="text-base font-semibold text-slate-800">
                        Preview
                    </h3>

                    <button
                        wire:click="$toggle('showPreview')"
                        class="text-sm text-emerald-600 hover:text-emerald-700">

                        {{ $showPreview ? 'Hide' : 'Show' }}

                    </button>
                </div>

                @if ($showPreview)
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-5">

                        <div
                            class="mb-4 border-b border-slate-200 pb-3">

                            <div
                                class="text-xs uppercase tracking-wide text-slate-500">
                                Subject
                            </div>

                            <div
                                class="font-semibold text-slate-800">
                                {{ $subject }}
                            </div>

                        </div>

                        <div
                            class="whitespace-pre-wrap text-sm text-slate-700">
                            {!! nl2br(e($body)) !!}
                        </div>

                    </div>
                @endif

            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-span-4 space-y-6">

            {{-- Deal Info --}}
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">

                <h3
                    class="mb-4 text-base font-semibold text-slate-800 dark:text-slate-100">
                    Deal Details
                </h3>

                <div class="space-y-3 text-sm">

                    <div>
                        <span
                            class="font-medium text-slate-500 dark:text-slate-400">
                            Contact:
                        </span>

                        {{ $contact?->first_name }}
                        {{ $contact?->last_name }}
                    </div>

                    <div>
                        <span
                            class="font-medium text-slate-500 dark:text-slate-400">
                            Company:
                        </span>

                        {{ $company?->name }}
                    </div>

                    <div>
                        <span
                            class="font-medium text-slate-500 dark:text-slate-400">
                            Stage:
                        </span>

                        {{ $deal->stage }}
                    </div>

                </div>

            </div>

            {{-- Email History --}}
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">

                <div
                    class="mb-4 flex items-center justify-between">

                    <h3
                        class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        Email History
                    </h3>

                    <span
                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-100">

                        {{ $deal->emailLogs->count() }}

                    </span>
                </div>

                <div class="space-y-3">

                    @forelse($deal->emailLogs->sortByDesc('created_at') as $email)

                        <div
                            class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">

                            <div
                                class="mb-2 flex items-center justify-between">

                                <div
                                    class="font-medium text-slate-800 dark:text-slate-100">
                                    {{ $email->subject }}
                                </div>

                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium
                                    @if($email->status === 'sent')
                                        bg-emerald-100 text-emerald-700
                                    @elseif($email->status === 'failed')
                                        bg-red-100 text-red-700
                                    @else
                                        bg-amber-100 text-amber-700
                                    @endif">

                                    {{ ucfirst($email->status) }}

                                </span>
                            </div>

                            <div
                                class="text-xs text-slate-500">
                                To:
                                {{ $email->to_email }}
                            </div>

                            <div
                                class="mt-2 text-xs text-slate-400">

                                {{ $email->created_at?->diffForHumans() }}

                            </div>

                        </div>

                    @empty

                        <div
                            class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">

                            No email history yet.

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>