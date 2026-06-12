<div x-show="step === 2" x-transition>
    <div class="rounded-2xl border bg-white shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
        <div class="border-b p-6">
            <h2 class="text-lg font-semibold">Document Source</h2>
        </div>

        <div class="p-6">
            <div class="mb-6 flex gap-2">
                <button
                    type="button"
                    @click="activeTab = 'template'"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition"
                    :class="activeTab === 'template' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                >
                    Template
                </button>

                <button
                    type="button"
                    @click="activeTab = 'multi-template'"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition"
                    :class="activeTab === 'multi-template' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                >
                    Multiple Templates
                </button>

                <button
                    type="button"
                    @click="activeTab = 'document'"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition"
                    :class="activeTab === 'document' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                >
                    Upload / Link
                </button>
            </div>

            <div x-show="activeTab === 'template'">
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="mb-2 block text-sm font-medium">Select Template</label>
                        <select
                            x-model="form.template_id"
                            class="w-full rounded-xl border px-4 py-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            :disabled="loadingTemplates"
                        >
                            <option value="" x-text="loadingTemplates ? 'Loading templates...' : 'Select Template'"></option>
                            <template x-for="template in templates" :key="template.template_fingerprint">
                                <option :value="template.template_fingerprint" x-text="template.template_title || template.template_fingerprint"></option>
                            </template>
                        </select>
                    </div>

                    <button
                        type="button"
                        @click="loadTemplates()"
                        class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        :disabled="loadingTemplates"
                    >
                        Refresh
                    </button>

                    <button
                        type="button"
                        @click="syncSingleTemplateParties()"
                        class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        Sync Parties
                    </button>
                </div>

                <p class="mt-2 text-xs text-gray-500" x-text="templateStatus"></p>
            </div>

            <div x-show="activeTab === 'multi-template'">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-slate-100">Select Templates</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">Choose 2 or more templates. Party rows are mapped by index against each template's party list.</p>
                    </div>

                    <button
                        type="button"
                        @click="syncMultiTemplateParties()"
                        class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:text-slate-100 dark:border-slate-500 dark:hover:bg-slate-700"
                    >
                        Sync Parties
                    </button>
                </div>

                <div class="max-h-56 space-y-2 overflow-auto rounded-xl border border-gray-200 bg-gray-50 p-3 dark:bg-slate-800 dark:border-slate-700">
                    <template x-if="!templates.length">
                        <p class="text-sm text-gray-500">No templates available.</p>
                    </template>

                    <template x-for="template in templates" :key="template.template_fingerprint">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-transparent p-3 hover:border-gray-200 hover:bg-white dark:hover:bg-slate-700">
                            <input
                                type="checkbox"
                                :value="template.template_fingerprint"
                                x-model="form.multi_templates"
                                class="mt-1 rounded border-gray-300 text-blue-600 dark:bg-slate-600 dark:border-slate-500 dark:text-slate-100">
                            >

                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium dark:text-slate-100" x-text="template.template_title || template.template_fingerprint"></p>
                                <p class="text-xs text-gray-500 dark:text-slate-400" x-text="template.template_fingerprint"></p>
                            </div>
                        </label>
                    </template>
                </div>

                <p class="mt-2 text-xs text-gray-500" x-text="multiTemplateStatus"></p>
            </div>

            <div x-show="activeTab === 'document'">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Document Title</label>
                        <input
                            type="text"
                            x-model="form.doc_title"
                            class="w-full rounded-xl border px-4 py-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Document URL</label>
                        <input
                            type="url"
                            x-model="form.doc_url"
                            class="w-full rounded-xl border px-4 py-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
