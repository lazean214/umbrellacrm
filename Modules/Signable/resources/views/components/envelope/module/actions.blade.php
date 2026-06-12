<div class="mt-8 flex items-center justify-between">
    <button
        x-show="step > 1"
        @click="prevStep()"
        type="button"
        class="rounded-xl border px-5 py-2.5 hover:bg-gray-50"
    >
        Previous
    </button>

    <div class="ml-auto flex items-center gap-3">
        <button
            x-show="step < 4"
            @click="nextStep()"
            type="button"
            class="rounded-xl bg-blue-600 px-6 py-2.5 text-white hover:bg-blue-700"
        >
            Next
        </button>

        <button
            x-show="step === 4"
            @click="submitForm()"
            type="button"
            class="rounded-xl bg-green-600 px-6 py-2.5 text-white hover:bg-green-700"
            :disabled="loading"
        >
            <span x-show="!loading">Send Envelope</span>
            <span x-show="loading">Sending...</span>
        </button>
    </div>
</div>

<div
    x-show="response"
    class="mt-8 rounded-2xl border p-5"
    :class="responseType === 'success' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'"
>
    <pre class="overflow-auto whitespace-pre-wrap text-xs" x-text="JSON.stringify(response, null, 2)"></pre>
</div>
