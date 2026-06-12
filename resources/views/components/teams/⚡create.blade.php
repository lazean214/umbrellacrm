<?php

use Livewire\Component;
use App\Models\Team;

new class extends Component
{
    public $name;
    public $description;

    public function createTeam()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $team = Team::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        auth()->user()->teams()->attach($team->id);

        session()->flash('message', 'Team created successfully.');

        return redirect()->route('teams');
    }
};
?>

<div x-data="{ open: false }" class="p-6">
    <button @click="open = true" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Create New Team
    </button>

    <div x-show="open" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" >
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg p-6 w-full max-w-md" @click.away="open = false">
        <h1 class="text-2xl font-bold mb-4">Create New Team</h1>

            <form wire:submit.prevent="createTeam" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Team Name</label>
                    <input type="text" id="name" wire:model="name" class="py-2 px-4 mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" wire:model="description" class="py-2 px-4 mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Team
                </button>
            </form>
        </div>
    </div>
</div>

