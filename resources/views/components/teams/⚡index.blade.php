<?php

use Livewire\Component;
use App\Models\Team;
use App\Models\User;

new class extends Component
{
    public $teams;
    public $users;

    protected $listeners = ['teamUpdated' => 'loadData'];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->teams = Team::with('users')->get();
        $this->users = User::all();
    }
};
?>

<div>
    @livewire('teams.edit')
    
    <h1 class="text-2xl font-bold mb-4">Teams</h1>
    @livewire('teams.create')
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($teams as $team)
            <div class="border rounded-lg p-4">
                <div class="flex justify-between items-start mb-2">
                    <h2 class="text-xl font-semibold">{{ $team->name }}</h2>
                    <button wire:click="$dispatch('editTeam', { teamId: {{ $team->id }} })"
                            class="text-indigo-600 hover:text-indigo-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-gray-600 mb-4">{{ $team->description }}</p>
                <h3 class="font-bold mb-1">Members:</h3>
                <ul class="list-disc list-inside">
                    @foreach($team->users as $user)
                        <li>{{ $user->name }} ({{ $user->email }})</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>