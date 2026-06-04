<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<section x-data="{ expanded: true }" class="bg-gray-100 text-black rounded-lg border dark:border-slate-700 p-4 dark:bg-slate-800 dark:text-slate-100 mt-4">
            <h2 class="text-sm uppercase font-bold mb-4 flex justify-between">MDA  <button
                @click="expanded = !expanded"
                class="group inline-flex items-center justify-center rounded-lg p-2 transition hover:bg-slate-100 dark:hover:bg-slate-800"
            >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-slate-500 transition-all duration-300 ease-in-out group-hover:text-slate-700 dark:text-slate-400 dark:group-hover:text-slate-200"
                :class="{ 'rotate-180': expanded }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19 14l-7-7-7 7"
                />
            </svg>
        </button></h2>
    <div x-show="expanded" x-collapse.duration.300ms>
           <label class="text-xs font-bold uppercase tracking-wider">
            MDA Setup
             <select wire:model="mda_setup" class="capitalize block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2">
                <option value="" disabled selected>Select MDA Setup</option>
                @foreach ($internalCompanies as $company)
                    <option value="{{ $company['name'] }}" class="text-black dark:text-slate-100">{{ $company['name'] }}</option>
                @endforeach
            </select>
            </label>
           <label class="text-xs font-bold uppercase tracking-wider">
            MDA Reference Number</label>
                <input type="text" wire:model="mda_reference_number" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
            
           <label class="text-xs font-bold uppercase tracking-wider">
            Date Set Up</label>
                <input type="date" wire:model="date_set_up" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
            
           <label class="text-xs font-bold uppercase tracking-wider">
            Remittance Received?</label>
                <select wire:model="remittance_received" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
                <option value="" disabled>Select Option</option>
                <option value="No" {{ $remittance_received === 'No' ? 'selected' : '' }}>No</option>
                <option value="Yes" {{ $remittance_received === 'Yes' ? 'selected' : '' }}>Yes</option>
            </select>
            
           <label class="text-xs font-bold uppercase tracking-wider">
            Date Logged</label>
                <input type="date" wire:model="date_logged" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
    </div>
</section>
