<?php

use Livewire\Component;
use App\Models\Team;
use App\Models\User;

new class extends Component
{
    public $teams;
    public $users;

    public function mount()
    {
        $this->teams = Team::all();
        $this->users = User::all();
    }
};
?>

<div>
    
    <h1 class="text-2xl font-bold mb-4">Teams</h1>
    @livewire('teams.create')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($teams as $team)
            <div class="border rounded-lg p-4">
                <h2 class="text-xl font-semibold mb-2">{{ $team->name }}</h2>
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