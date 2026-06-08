<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<section x-data="{ expanded: true }" class="bg-gray-100 text-black rounded-lg border dark:border-slate-700 p-4 dark:bg-slate-800 dark:text-slate-100 mt-4">
    <h2 class="text-sm uppercase font-bold mb-4 flex justify-between">Deal Details  
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
           <label class="text-xs font-bold uppercase tracking-wider">
            Deal Name
            <input type="text" wire:model="name" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
            </label>
           <label class="text-xs font-bold uppercase tracking-wider">
            Deal Owner</label>
               @php
                $users = \App\Models\User::all();

                $dealOwnerOptions = $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                    ];
                });
               @endphp

                <select wire:model="user_id" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2">
                      <option value="" disabled selected>Select owner…</option>
                      @foreach($dealOwnerOptions as $option)
                            <option value="{{ $option['id'] }}" @if($option['id'] == $user_id) selected @endif>{{ $option['name'] }}</option>
                      @endforeach
                 </select>
            
           <label class="text-xs font-bold uppercase tracking-wider">
            Deal Created At</label>
                <input type="date" wire:model="created_at" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
            
           <label class="text-xs font-bold uppercase tracking-wider">
            Amount (TSV)</label>
              <div>
            <div class="mt-2">
                <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-indigo-600">
                <div class="shrink-0 text-base text-gray-500 select-none sm:text-sm/6">£</div>
                <input id="price" type="text"  wire:model="amount" placeholder="0.00" class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                
                </div>
            </div>
            </div>
                        
           <label class="text-xs font-bold uppercase tracking-wider">
            Recruitment Source</label>
               <select wire:model="recruitment_agency" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2">
                    <option value="" disabled selected>Select source…</option>
                    <option value="Inbound">Inbound</option>
                    <option value="Referral">Referral</option>
                </select>
           <label class="text-xs font-bold uppercase tracking-wider">
            Recruitment Agency</label>

            <div class="deal-field">
                <div class="deal-autocomplete-wrap">
                    <input
                        wire:model.live="consultant_name"
                        type="text"
                        placeholder="Search or enter agency"
                        class="deal-input"
                        autocomplete="off"
                    >
                    @if($showConsultantDropdown)
                        <div class="deal-autocomplete-dropdown">
                            @foreach($consultantSuggestions as $suggestion)
                                <div class="deal-autocomplete-item" wire:click="selectConsultant('{{ addslashes($suggestion) }}')">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.3"/>
                                        <path d="M4.5 6.5l1.5 1.5 2.5-3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ $suggestion }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @error('consultant_name') <span class="deal-error">{{ $message }}</span> @enderror
            </div>
            <div class="deal-field-grid-2" style="margin-top:12px">
                <div class="deal-field">
                    <label class="deal-label">Agency Deal Value</label>
                    <div class="mt-2">
                        <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-indigo-600">
                        <div class="shrink-0 text-base text-gray-500 select-none sm:text-sm/6">£</div>
                        <input id="price" type="text"  wire:model="agency_deal_value" placeholder="0.00" class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                        </div>
                    </div>
                </div>
                <div class="deal-field">
                    <label class="deal-label">Margin Agreed</label>
                    <div class="mt-2">
                        <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-indigo-600">
                        <div class="shrink-0 text-base text-gray-500 select-none sm:text-sm/6">£</div>
                        <input id="price" type="text"  wire:model="margin_agreed" placeholder="0.00" class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                        </div>
                    </div>
                </div>
            </div>
            
    </div>
</section>
