<div class="mb-8">
    <div class="flex items-center gap-2">
        <template x-for="i in 4" :key="i">
            <div class="flex flex-1 items-center">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full border text-sm font-semibold transition"
                    :class="step >= i ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-400'"
                >
                    <span x-text="i"></span>
                </div>

                <div
                    x-show="i < 4"
                    class="mx-2 h-1 flex-1 rounded"
                    :class="step > i ? 'bg-blue-600' : 'bg-gray-200'"
                ></div>
            </div>
        </template>
    </div>

    <div class="mt-2 flex justify-between text-xs text-gray-500">
        <span>Envelope</span>
        <span>Document</span>
        <span>Parties</span>
        <span>Review</span>
    </div>
</div>
