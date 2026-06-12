<div x-show="step === 4" x-transition>
    <div class="rounded-2xl border bg-white shadow-sm">
        <div class="border-b p-6">
            <h2 class="text-lg font-semibold">Review & Submit</h2>
        </div>

        <div class="space-y-6 p-6">
            <div>
                <h3 class="mb-2 font-semibold text-gray-700">Envelope</h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong>Title:</strong> <span x-text="form.envelope_title"></span></p>
                    <p><strong>User ID:</strong> <span x-text="form.user_id"></span></p>
                </div>
            </div>

            <div>
                <h3 class="mb-2 font-semibold text-gray-700">Parties</h3>
                <div class="space-y-2">
                    <template x-for="(party, index) in form.parties">
                        <div class="rounded-xl border bg-gray-50 p-3 text-sm">
                            <strong x-text="party.party_name"></strong>
                            -
                            <span x-text="party.party_email"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
