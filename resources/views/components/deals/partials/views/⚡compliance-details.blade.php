<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>


<section class="bg-gray-100 text-black rounded-lg border dark:border-slate-700 p-4 dark:bg-slate-800 dark:text-slate-100 mb-4 mt-4" x-data="{ expanded: true }">
    <div class="flex items-center justify-between ">
        <h2 class="text-sm uppercase font-bold mb-4">Compliance</h2>
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
    </div>
    <div x-show="expanded" x-collapse.duration.1000ms class="grid grid-cols-3 border dark:border-slate-700 rounded-lg p-4 gap-4 mb-4 transition-all duration-300 ease-in-out">
        <div>
            <label class="text-xs font-bold uppercase tracking-wider"> Date Sent <span class="italic capitalize font-normal">(Signable)</span></label>
            <input type="date" wire:model="date_sent" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
        </div>
        <div>
            <label class="text-xs font-bold uppercase tracking-wider">Date Signed <span class="italic capitalize font-normal">(Signable)</span></label>
            <input type="date" wire:model="date_signed" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition  mb-2" />
        </div>
        <div>
            <label class="text-xs font-bold uppercase tracking-wider">Who Signed? <span class="italic capitalize font-normal">(Signable)</span></label>
            <input type="text" wire:model="who_signed" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
        </div>
        <div>
            <label class="text-xs font-bold uppercase tracking-wider">Signed Document</label>
            <input type="text" wire:model="signed_doc" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
        </div>
        <div>
            <label class="text-xs font-bold uppercase tracking-wider"> New Starter Checklist Recieved Date  </label>
            <input type="date" wire:model="starter_checklist_recieved_date" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
        </div>
        <div>
            <label class="text-xs font-bold uppercase tracking-wider"> Starter Form  </label>
            <select wire:model="starter_form" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2">
                <option value="" class="italic" disabled>Select Code</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-bold uppercase tracking-wider">Tax Code </label>        
            <select wire:model="tax_code" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2">
                    <option value="" class="italic" disabled>Select Code</option>
                    <option value="1257L">1257L</option>
                    <option value="1257L1">1257L1</option>
                    <option value="BR">BR</option>  
            </select>
        </div>
        <div>
            <label class="text-xs font-bold uppercase tracking-wider"> Employee Contract Recieved Date  </label>
            <input type="date" wire:model="contract_recieved_date" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" />
        </div>
        <div>
            <label class="text-xs font-bold uppercase tracking-wider"> Right to Work Document</label>
            <select wire:model="photo_id_passport" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2">
                    <option value="" disabled selected>Select Document</option>
                    <option value="UK Passport">UK Passport</option>
                    <option value="Foreign Passport">Foreign Passport</option>
                    <option value="Irish Passport">Irish Passport</option>
                    <option value="Driving License">Driving License</option>
                </select>
        </div>

        <div>
                <label class="text-xs font-bold uppercase tracking-wider"> Proof of Address</label>
                <select type="text" wire:model="proof_of_address" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2">
                <option value="" disabled selected>Select Option</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-bold uppercase tracking-wider"> Right to Work</label>
            <select type="text" wire:model="right_to_work" class="block w-full pl-4 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition mb-2" >
                <option value="" disabled selected>Select Option</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
    </div>

    <div class="space-y-5 mt-6 border border-slate-200 dark:border-slate-700 rounded-lg p-4 bg-white dark:bg-slate-800">

            {{-- Compliance Documents --}}
        <div>

            <label
                class="text-xs font-bold uppercase tracking-wider block mb-2"
            >
                Compliance Documents
            </label>

            <input
                type="file"
                wire:model="compliance_documents"
                multiple
                class="block w-full pl-4 pr-3 py-2 text-sm
                bg-white dark:bg-slate-800
                border border-slate-200 dark:border-slate-700
                rounded-lg text-slate-900 dark:text-slate-100
                focus:outline-none focus:ring-2
                focus:ring-indigo-500/30
                focus:border-indigo-500 transition"
            >

            <div
                wire:loading
                wire:target="compliance_documents"
                class="text-xs text-indigo-500 mt-2"
            >
                Uploading compliance files...
            </div>

            @error('compliance_documents.*')
                <span class="text-red-500 text-xs">
                    {{ $message }}
                </span>
            @enderror

            {{-- Preview selected files --}}
            @if($compliance_documents)

                <div class="mt-3 space-y-1">

                    @foreach($compliance_documents as $file)

                        <div
                            class="text-xs text-slate-600
                            dark:text-slate-300"
                        >
                            📄 {{ $file->getClientOriginalName() }}
                        </div>

                    @endforeach

                </div>

            @endif

        </div>


    {{-- Contract Documents --}}
    <div>

        <label
            class="text-xs font-bold uppercase tracking-wider block mb-2"
        >
            Contract Documents
        </label>

        <input
            type="file"
            wire:model="contract_documents"
            multiple
            class="block w-full pl-4 pr-3 py-2 text-sm
            bg-white dark:bg-slate-800
            border border-slate-200 dark:border-slate-700
            rounded-lg text-slate-900 dark:text-slate-100
            focus:outline-none focus:ring-2
            focus:ring-indigo-500/30
            focus:border-indigo-500 transition"
        >

        <div
            wire:loading
            wire:target="contract_documents"
            class="text-xs text-indigo-500 mt-2"
        >
            Uploading contract files...
        </div>

            @error('contract_documents.*')
                <span class="text-red-500 text-xs">
                    {{ $message }}
                </span>
            @enderror

            @if($contract_documents)
                <div class="mt-3 space-y-1">
                    @foreach($contract_documents as $file)
                        <div
                            class="text-xs text-slate-600
                            dark:text-slate-300"
                        >
                            📄 {{ $file->getClientOriginalName() }}
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</section>
