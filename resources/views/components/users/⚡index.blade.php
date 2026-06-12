<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Team;

new class extends Component
{
    public $users = [];
    public $teams = [];

    protected $listeners = ['userUpdated' => 'loadData'];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->users = User::with('teams')->get();
        $this->teams = Team::all();
    }

    public function assignTeam($userId, $teamId)
    {
        if (!$teamId) {
            return;
        }

        $user = User::findOrFail($userId);
        $user->teams()->syncWithoutDetaching([$teamId]);

        session()->flash('message', 'Team assigned successfully.');
        $this->loadData();
    }
};
?>

<div class="p-6">
    @livewire('users.edit')
    
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">
            Users
        </h1>

        @livewire('users.create')
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-xl bg-green-100 px-4 py-3 text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($users as $user)
            <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-lg font-bold">
                                {{ $user->name }}
                            </h2>
                            <p class="text-sm text-zinc-500">
                                {{ $user->email }}
                            </p>
                        </div>
                        <button wire:click="$dispatch('editUser', { userId: {{ $user->id }} })" 
                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Current Teams --}}
                <div class="mb-4">
                    <p class="font-medium text-sm mb-2">
                        Assigned Teams
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @forelse($user->teams as $team)
                            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                {{ $team->name }}
                            </span>
                        @empty
                            <span class="text-sm text-zinc-500">
                                No team assigned
                            </span>
                        @endforelse
                    </div>
                </div>

                {{-- Assign Team --}}
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Assign Team
                    </label>
                    <select wire:change="assignTeam({{ $user->id }}, $event.target.value)"
                            class="w-full rounded-xl border border-zinc-300 px-4 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                        <option value="">Select Team</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endforeach
    </div>
</div>