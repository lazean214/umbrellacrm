<div x-show="step === 3" x-transition>
    <div class="rounded-2xl border bg-white shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
        <div class="flex items-center justify-between border-b p-6">
            <h2 class="text-lg font-semibold">Signing Parties</h2>

            <button
                type="button"
                @click="addParty()"
                class="rounded-xl bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700"
            >
                Add Party
            </button>
        </div>

        <div class="space-y-5 p-6">
            <template x-for="(party, index) in form.parties" :key="index">
                <div class="rounded-2xl border bg-gray-50 p-5 dark:bg-slate-700">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-slate-100">Party <span x-text="index + 1"></span></h3>
                        <button type="button" @click="removeParty(index)" class="text-sm text-red-500">Remove</button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Full Name</label>
                            <input type="text" x-model="party.party_name" class="w-full rounded-xl border px-4 py-2 border-gray-300 dark:bg-slate-600 dark:border-slate-500 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Email</label>
                            <input type="email" x-model="party.party_email" class="w-full rounded-xl border px-4 py-2 border-gray-300 dark:bg-slate-600 dark:border-slate-500 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Role</label>
                            <input type="text" x-model="party.party_role" class="w-full rounded-xl border px-4 py-2 border-gray-300 dark:bg-slate-600 dark:border-slate-500 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Mobile</label>
                            <input type="text" x-model="party.party_mobile" class="w-full rounded-xl border px-4 py-2 border-gray-300 dark:bg-slate-600 dark:border-slate-500 dark:text-slate-100">
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Message</label>
                            <textarea x-model="party.party_message" rows="3" class="w-full rounded-xl border px-4 py-2 border-gray-300 dark:bg-slate-600 dark:border-slate-500 dark:text-slate-100"></textarea>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
