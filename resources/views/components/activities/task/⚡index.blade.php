<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Deal;
use App\Models\ActivityLog;
use App\Services\DealEmailService;

new class extends Component
{
    public ?int $dealId = null;

    public string $type = 'tasks';
    public string $activityName = '';
    public string $message = '';
    public string $userEmail = '';
    public ?int $emailTemplateId = null;

    public function mount(?int $dealId = null): void
    {
        $this->dealId = $dealId;
        $this->userEmail = Deal::find($dealId)?->user()->first()?->email ?? '';
    }

    #[Computed]
    public function activities()
    {
        return ActivityLog::where('deal_id', $this->dealId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function submit(): void
    {
        $this->validate([
            'type'            => 'required|string|in:tasks,email,todo',
            'activityName'    => 'required|string|max:255',
            'message'         => 'nullable|string',
            'userEmail'       => 'required_if:type,email|nullable|email',
            'emailTemplateId' => 'required_if:type,email|integer|exists:email_templates,id',
        ]);

        ActivityLog::create([
            'deal_id'       => $this->dealId,
            'type'          => $this->type,
            'activity_name' => $this->activityName,
            'message'       => $this->message,
            'user_email'    => $this->userEmail,
        ]);

        // Send email via DealEmailService when type is email
        if ($this->type === 'email' && $this->userEmail) {
            $deal = Deal::findOrFail($this->dealId);

            DealEmailService::send(
                deal: $deal,
                templateId: $this->emailTemplateId,
                to: $this->userEmail,
                customSubject: $this->activityName ?: null,
                customBody: $this->message ?: null,
            );
        }

        $this->reset(['type', 'activityName', 'message', 'userEmail', 'emailTemplateId']);
        $this->type = 'tasks';
    }
};
?>

<div>
    <div class="bg-white p-4 rounded-lg shadow mb-4 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
        <form wire:submit.prevent="submit">
            <div class="flex items-center justify-between gap-4 mb-4">
                <select wire:model.live="type" class="border border-gray-300 rounded px-3 py-2">
                    <option value="tasks" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">Tasks</option>
                    <option value="email" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">Email</option>
                    <option value="todo" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">To Do</option>
                </select>

                <input
                    wire:model="activityName"
                    type="text"
                    class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                    placeholder="{{ $type === 'email' ? 'Subject' : 'Activity Name' }}"
                />
            </div>

            @if ($type === 'email')
                <div class="flex gap-4 mb-4">
                    <input
                        wire:model="userEmail"
                        type="email"
                        class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                        placeholder="Recipient email"
                    />
                    <select wire:model="emailTemplateId" class="border border-gray-300 rounded px-3 py-2 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
                        <option value="">Select template…</option>
                        @foreach(\App\Models\EmailTemplate::all() as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <textarea
                wire:model="message"
                class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4"
                placeholder="{{ $type === 'email' ? 'Email body (leave blank to use template)' : 'Description' }}"
            ></textarea>

            <button type="submit" class="rounded bg-green-500 text-white px-4 py-2">
                {{ $type === 'email' ? 'Send Email' : 'Save' }}
            </button>
        </form>
    </div>

    <div class="bg-white p-4 rounded-lg shadow dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
        <h2 class="text-xl font-bold mb-4 dark:text-slate-100">Activity Feed</h2>

        <div>
            @forelse ($this->activities as $activity)
                <div class="border-b border-gray-200 py-2">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                        <span class="text-sm font-semibold">{{ ucfirst($activity->type) }}</span>
                        @if ($activity->activity_name)
                            <span class="text-sm text-gray-700">{{ $activity->activity_name }}</span>
                        @endif
                        @if ($activity->user_email)
                            <span class="text-sm text-gray-500">{{ $activity->user_email }}</span>
                        @endif
                    </div>
                    @if ($activity->message)
                        <p class="text-sm text-gray-600 mt-1">{{ $activity->message }}</p>
                    @endif
                    <livewire:activities.task.comment :activity-id="$activity->id" />
                </div>
            @empty
                <p class="text-sm text-gray-400">No activity yet.</p>
            @endforelse
        </div>
    </div>
</div>