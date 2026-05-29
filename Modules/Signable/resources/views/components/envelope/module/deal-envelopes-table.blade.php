<div x-show="dealId" class="mt-8 min-w-0 w-full max-w-full overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700">
    <div class="flex items-center justify-between border-b px-6 py-4 bg-slate-50 dark:bg-slate-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Created Envelopes For This Deal</h3>
        <button
            type="button"
            @click="loadDealEnvelopes({ syncStatuses: true })"
            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 transition hover:bg-gray-50 dark:hover:bg-slate-700"
            :disabled="loadingDealEnvelopes"
        >
            Refresh
        </button>
    </div>

    <div x-show="loadingDealEnvelopes" class="px-6 py-10">
        <div class="flex items-center justify-center gap-3 text-gray-500 dark:text-slate-400">
            <svg class="h-6 w-6 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm font-medium">Fetching envelope records...</span>
        </div>
    </div>

    <div x-show="!loadingDealEnvelopes" class="w-full max-w-full overflow-x-auto">
        <table class="min-w-max divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-slate-300">Deal ID</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-slate-300">User ID</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-slate-300">Envelope Title</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-slate-300">Envelope Fingerprint</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-slate-300">Date Created</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-slate-300">Date Signed</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-slate-300">Envelope Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-slate-300">Download Link</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white dark:bg-slate-800 dark:divide-slate-700">
                <template x-if="!loadingDealEnvelopes && dealEnvelopes.length === 0">
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-500 dark:text-slate-400">No envelopes saved for this deal yet.</td>
                    </tr>
                </template>

                <template x-for="envelope in dealEnvelopes" :key="envelope.id">
                    <tr>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300" x-text="envelope.deal_id"></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300" x-text="envelope.user_id"></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300" x-text="envelope.envelope_title"></td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-slate-300" x-text="envelope.envelope_fingerprint"></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300" x-text="envelope.envelope_created || '-' "></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300" x-text="envelope.envelope_processed || '-' "></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300" x-text="envelope.envelope_status || '-' " :class="{'bg-green-500 text-white capitalize font-bold': envelope.envelope_processed, 'bg-red-500 text-white capitalize': !envelope.envelope_processed}"></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">
                            <template x-if="envelope.download_link">
                                <a :href="envelope.download_link" target="_blank" rel="noopener" class="text-gray-600 dark:text-slate-300 hover:text-gray-800 dark:hover:text-slate-100 hover:underline font-bold">Open</a>
                            </template>
                            <template x-if="!envelope.download_link">
                                <span>-</span>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
