<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
<section x-data="{ expanded: true }" class="bg-gray-100 text-black rounded-lg border p-4 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
    <h2 class="text-sm uppercase font-bold mb-4 flex justify-between">Deal Details  <button
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
        <div class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">Deal Created: {{ $created_at }}</div>

        <div class="mt-4 mb-4">
        <label class="text-xs font-bold uppercase tracking-wider">
            Deal Owner
        </label>
        
        <div class="relative" x-data @click.away="$wire.closeOwnerDropdown()">
            <input
                type="text"
                wire:model.live.debounce.300ms="ownerSearch"
                wire:focus="showOwnerDropdown = true"
                placeholder="Search or select owner…"
                class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                autocomplete="off"

                @if(auth()->user()->isSalesTeam())
                    disabled
                    class="cursor-not-allowed opacity-60"
                    title="You don't have permission to change the owner"
                @endif
            />
            
            <input type="hidden" wire:model="user_id" />

            @if($showOwnerDropdown)
                <div class="flex flex-col absolute z-50 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg mt-1 w-full max-h-60 overflow-auto shadow-lg">
                    @foreach($ownerSuggestions as $user)
                        {{-- Accessing as an array element since suggestions are cast to arrays --}}
                        <div class="flex items-center px-3 py-2 cursor-pointer text-sm normal-case text-slate-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-slate-700" 
                            wire:click.prevent="selectOwner(
                                {{ $user['id'] }},
                                @js($user['name'])
                            )">
                            <svg class="text-indigo-500" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span class="ml-2 font-medium">{{ $user['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
            @error('user_id') <span class="text-xs text-red-500 lowercase mt-1 block">{{ $message }}</span> @enderror
    </div>
        <label class="text-xs font-bold uppercase tracking-wider">Deal Name</label>
        <input type="text" wire:model="name" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />

        <label class="text-xs font-bold uppercase tracking-wider">TimeSheet Value</label>
        <input type="number" wire:model="amount" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />

        <label class="text-xs font-bold uppercase tracking-wider">Hours</label>
        <input type="number" wire:model="hours" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />

        <label class="text-xs font-bold uppercase tracking-wider">Rate</label>
        <input type="number" wire:model="rate" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />

        <label class="text-xs font-bold uppercase tracking-wider">Stage</label>
        <input type="text" wire:model="stage" readonly class="text-sm w-full capitalize border border-gray-200 bg-gray-50 rounded text-gray-500 px-3 py-2 mb-4 cursor-not-allowed dark:bg-slate-800 dark:text-slate-100" title="Use the stage selector above mb-4" />

            <label class="text-xs font-bold uppercase tracking-wider">Recruitment Agency</label>
            <select wire:model.live="recruitment_agency" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4">
            <option value="" disabled selected>Select Recruitment Agency</option>
            <option value="Inbound">Inbound</option>
            <option value="Referral">Referral</option>
            </select>

        <label class="text-xs font-bold uppercase tracking-wider">Consultant Name</label>
        <div class="relative" x-data @click.away="$wire.closeConsultantDropdown()">
            <input
                wire:model.live="consultant_name"
                type="text"
                placeholder="Search or enter consultant…"
                class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4"
                autocomplete="off"
            >
            @if($showConsultantDropdown)
                <div class="flex flex-col absolute z-10 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg mt-1 w-full max-h-60 overflow-auto">
                    @foreach($consultantSuggestions as $suggestion)
                        <div class="flex items-center px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700" wire:click="selectConsultant('{{ addslashes($suggestion) }}')">
                            <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                <circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M4.5 6.5l1.5 1.5 2.5-3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="ml-2">{{ $suggestion }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @error('consultant_name') <span class="deal-error">{{ $message }}</span> @enderror

        <label class="text-xs font-bold uppercase tracking-wider">Agency Deal Value</label>
        <input type="number" wire:model="agency_deal_value" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />

        <label class="text-xs font-bold uppercase tracking-wider">Margin Agreed</label>
        <input type="number" wire:model="margin_agreed" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-4" />
    </div>
</section>
