<div x-show="step === 1" x-transition>
    <div class="rounded-2xl border bg-white shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
        <div class="border-b p-6">
            <h2 class="text-lg font-semibold">Envelope Details</h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium">Envelope Title <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    x-model="form.envelope_title"
                    class="w-full rounded-xl border px-4 py-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Tenancy Agreement"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">User ID <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    x-model="form.user_id"
                    class="w-full rounded-xl border px-4 py-2 border-gray-300 text-gray-500 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="12345"
                    disabled
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Redirect URL</label>
                <input
                    type="url"
                    x-model="form.envelope_redirect_url"
                    class="w-full rounded-xl border px-4 py-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="https://example.com"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Auto Expire (hours)</label>
                <input
                    type="number"
                    x-model="form.envelope_auto_expire_hours"
                    class="w-full rounded-xl border px-4 py-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Auto Remind (hours)</label>
                <input
                    type="text"
                    x-model="form.envelope_auto_remind_hours"
                    class="w-full rounded-xl border px-4 py-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div class="md:col-span-2 flex flex-wrap gap-6">
                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        x-model="form.envelope_all_at_once_enabled"
                        class="rounded border px-4 py-2border-gray-300 text-blue-600"
                    >
                    <span class="text-sm">Send to all at once</span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        x-model="form.envelope_requires_otp"
                        class="rounded border-gray-300 text-blue-600"
                    >
                    <span class="text-sm">Require OTP</span>
                </label>
            </div>
        </div>
    </div>
</div>
