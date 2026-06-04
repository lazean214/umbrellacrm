<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<section x-data="{ expanded: true }" class="bg-gray-100 text-black rounded-lg border dark:border-slate-700 p-4 dark:bg-slate-800 dark:text-slate-100 mt-4">
            <h2 class="text-sm uppercase font-bold mb-4 flex justify-between">Worker Details 
                <button
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
        </button>
    </h2>
    <div x-show="expanded" x-collapse.duration.300ms>
            <label class="text-xs font-bold uppercase tracking-wider">First Name</label>
            <input type="text" wire:model="first_name" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">Last Name</label>
            <input type="text" wire:model="last_name" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">Email</label>
            <input type="email" wire:model="email" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">Date of Birth</label>
            <input type="date" wire:model="date_of_birth" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            
            <label class="text-xs font-bold uppercase tracking-wider">Gender</label>
            <select wire:model="gender" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4">
                <option value="" disabled selected>Select Gender</option>
                <option value="Male">Male</option>  
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>   
           <label class="text-xs font-bold uppercase tracking-wider">Marital Status</label>
            <select wire:model="marital_status" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4">
                <option value="" disabled selected>Select Marital Status</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Divorced">Divorced</option>
                <option value="Widowed">Widowed</option>
            </select>
            <label class="text-xs font-bold uppercase tracking-wider">Phone</label>
            <input type="text" wire:model="phone" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">Street Address</label>
            <input type="text" wire:model="street_address" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">City</label>
            <input type="text" wire:model="city" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">State</label>
            <input type="text" wire:model="state" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">Postal Code</label>
            <input type="text" wire:model="postal_code" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">Country</label>
            <input type="text" wire:model="country" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">NI Number</label>
            <input type="text" wire:model="ni_number" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">Bank</label>
            <input type="text" wire:model="bank" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">Account Number</label>
            <input type="text" wire:model="account_number" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
            <label class="text-xs font-bold uppercase tracking-wider">Sort Code</label>
            <input type="text" wire:model="sort_code" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
</div>
</section>
