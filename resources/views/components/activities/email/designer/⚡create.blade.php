<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public ?EmailTemplate $template = null;

    public ?int $templateId = null;

    public string $name = '';
    public string $description = '';
    public string $subject = '';
    public string $body = '';

    public bool $is_html = true;
    public bool $is_active = true;

    public $attachments = [];

    public $savedAttachments = [];

    public array $tokens = [
        'Deal' => [
            '[deal.name]',
            '[deal.amount]',
            '[deal.stage]',
            '[deal.consultant_name]',
        ],
        'Contact' => [
            '[contact.first_name]',
            '[contact.last_name]',
            '[contact.full_name]',
            '[contact.email]',
        ],
        'Company' => [
            '[company.name]',
            '[company.email]',
        ],
        'User' => [
            '[user.name]',
            '[user.email]',
        ],
    ];

    public function mount(
        ?int $templateId = null
    ) {

        if (! $templateId) {
            return;
        }

        $this->template =
            EmailTemplate::with(
                'attachments'
            )->findOrFail(
                $templateId
            );

        $this->templateId =
            $this->template->id;

        $this->fill([
            'name' =>
                $this->template->name,

            'description' =>
                $this->template->description ?? '',

            'subject' =>
                $this->template->subject,

            'body' =>
                $this->template->body,

            'is_active' =>
                $this->template->is_active,
        ]);

        $this->savedAttachments =
            $this->template
                ->attachments;
    }

    public function insertToken(
        string $token
    ) {
        $this->body .= ' ' . $token;
    }

    public function removeAttachment(
        int $id
    ) {

        $attachment =
            EmailTemplateAttachment::findOrFail(
                $id
            );

        Storage::disk(
            'local'
        )->delete(
            $attachment->file_path
        );

        $attachment->delete();

        $this->savedAttachments =
            $this->template
                ->fresh()
                ->attachments;
    }

    public function save()
    {
        $this->validate([
            'name' =>
                'required|min:3|max:255',

            'subject' =>
                'required|max:255',

            'body' =>
                'required|min:10',

            'attachments.*' =>
                'file|max:10240',
        ]);

        $template =
            EmailTemplate::updateOrCreate(
                [
                    'id' =>
                        $this->templateId,
                ],
                [
                    'name' =>
                        $this->name,

                    'description' =>
                        $this->description,

                    'subject' =>
                        $this->subject,

                    'body' =>
                        $this->body,

                    'is_html' =>
                        $this->is_html,

                    'is_active' =>
                        $this->is_active,

                    'created_by' =>
                        Auth::id(),
                ]
            );

        foreach ($this->attachments as $file) {

            if (! $file) {
                continue;
            }

            $path = $file->store(
                'email-template-attachments',
                'local'
            );

            $template
                ->attachments()
                ->create([
                    'file_name' =>
                        $file->getClientOriginalName(),

                    'file_path' =>
                        $path,

                    'mime_type' =>
                        $file->getMimeType(),

                    'file_size' =>
                        Storage::disk('local')
                            ->size($path),
                ]);
        }

        $this->attachments = [];

        $this->savedAttachments =
            $template
                ->fresh()
                ->attachments;

        $this->attachments = [];

        session()->flash(
            'success',
            'Template saved successfully.'
        );
    }
};
?>

<div class="space-y-6">

    @if (session('success'))
        <div
            class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6">

        {{-- Main Editor --}}
        <div class="col-span-8 space-y-5">

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Email Template Designer
                    </h2>

                    <button wire:click="save" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        {{ $templateId ? 'Update Template' : 'Save Template' }}
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-slate-700">
                            Template Name
                        </label>

                        <input
                            type="text"
                            wire:model="name"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:border-emerald-500 focus:outline-none" />
                    </div>

                    <div class="flex items-end">
                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                wire:model="is_active"
                                class="rounded border-slate-300">

                            <span
                                class="text-sm text-slate-700">
                                Active
                            </span>
                        </label>
                    </div>

                </div>

                <div class="mt-4">
                    <label
                        class="mb-1 block text-sm font-medium text-slate-700">
                        Description
                    </label>

                    <textarea
                        wire:model="description"
                        rows="2"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:border-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="mt-4">
                    <label
                        class="mb-1 block text-sm font-medium text-slate-700">
                        Subject
                    </label>

                    <input
                        type="text"
                        wire:model.live="subject"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:border-emerald-500 focus:outline-none" />
                </div>

                <div class="mt-4">
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Email Body
                    </label>
                    <div class="flex items-center gap-4 mb-2">
                        <label class="flex items-center gap-2 text-xs">
                            <input type="radio" wire:model="is_html" value="1"> HTML
                        </label>
                        <label class="flex items-center gap-2 text-xs">
                            <input type="radio" wire:model="is_html" value="0"> Plain Text
                        </label>
                    </div>
                    
                    <textarea
                        wire:model.live="body"
                        rows="18"
                        class="w-full rounded-2xl border border-slate-300 px-4 py-4 font-mono text-sm focus:border-emerald-500 focus:outline-none"
                        placeholder="Write your email {{ $is_html ? 'HTML' : 'plain text' }} here..."></textarea>
                </div>
            <div class="mt-5">
    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
        Template Attachments
    </label>

    <input
        type="file"
        wire:model="attachments"
        multiple
        class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3">

    <div
        wire:loading
        wire:target="attachments"
        class="mt-2 text-xs text-slate-500">
        Uploading...
    </div>

    @if(count($savedAttachments))

        <div class="mt-4 space-y-2">

            @foreach(
                $savedAttachments
                as $file
            )

                <div class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-3">

                    <div>
                        <div class="text-sm font-medium text-slate-800 dark:text-slate-200">
                            {{ $file->file_name }}
                        </div>

                        <div class="text-xs text-slate-500">
                            {{ number_format($file->file_size / 1024, 1) }} KB
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="removeAttachment({{ $file->id }})"
                        class="text-red-500 hover:text-red-700">

                        Remove
                    </button>

                </div>

            @endforeach

        </div>

    @endif
</div>
            </div>

            {{-- Preview --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-base font-semibold text-slate-800">Preview</h3>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <div class="mb-3 border-b border-slate-200 pb-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Subject</div>
                        <div class="font-medium text-slate-800">{{ $subject }}</div>
                    </div>
                    <div class="prose prose-sm max-w-none whitespace-pre-wrap text-slate-700">
                        @if($is_html)
                            {!! $body !!}
                        @else
                            {{ $body }}
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-span-4">

            <div
                class="sticky top-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <h3
                    class="mb-4 text-base font-semibold text-slate-800">
                    Dynamic Variables
                </h3>

                <div class="space-y-5">

                    @foreach ($tokens as $group => $items)
                        <div>

                            <h4
                                class="mb-2 text-sm font-semibold text-slate-600">
                                {{ $group }}
                            </h4>

                            <div
                                class="flex flex-wrap gap-2">

                                @foreach ($items as $token)
                                    <button
                                        wire:click="insertToken('{{ $token }}')"
                                        type="button"
                                        class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 hover:border-emerald-500 hover:bg-emerald-50">

                                        {{ $token }}

                                    </button>
                                @endforeach

                            </div>

                        </div>
                    @endforeach

                </div>

            </div>

        </div>

    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <!-- Include the Quill library -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <!-- Initialize Quill editor -->
    <script>
    const quill = new Quill('#editor', {
        theme: 'snow'
    });
    </script>
</div>