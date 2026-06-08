<x-layouts::app :title="__('GDPR Dashboard')">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <flux:heading size="xl">GDPR Compliance Dashboard</flux:heading>
                <flux:subheading>Manage data retention and privacy settings</flux:subheading>
            </div>
            <div class="flex gap-2">
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.gdpr.run') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition" onclick="return confirm('Are you sure? This will anonymize expired contacts and may take a few minutes.')">
                            Run Retention Now
                        </button>
                    </form>
                    <a href="{{ route('admin.gdpr.export-settings') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition inline-block">
                        Export Settings
                    </a>
                </div>
                <a href="{{ route('admin.gdpr.export-settings') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition inline-block">
                    Export Settings
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                <div>
                    <div class="text-white/80 text-sm uppercase tracking-wide">Total Contacts</div>
                    <div class="text-3xl font-bold mt-1">{{ number_format($stats['contacts']['total'] ?? 0) }}</div>
                    <div class="text-xs mt-2 opacity-75">
                        {{ $stats['contacts']['anonymised'] ?? 0 }} anonymised | {{ $stats['contacts']['pending_retention'] ?? 0 }} pending
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                <div>
                    <div class="text-white/80 text-sm uppercase tracking-wide">Total Users</div>
                    <div class="text-3xl font-bold mt-1">{{ number_format($stats['users']['total'] ?? 0) }}</div>
                    <div class="text-xs mt-2 opacity-75">
                        {{ $stats['users']['anonymised'] ?? 0 }} anonymised
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
                <div>
                    <div class="text-white/80 text-sm uppercase tracking-wide">Email Logs</div>
                    <div class="text-3xl font-bold mt-1">{{ number_format($stats['email_logs']['total'] ?? 0) }}</div>
                    <div class="text-xs mt-2 opacity-75">
                        Retention: {{ ($stats['email_logs']['retention_enabled'] ?? false) ? 'Enabled' : 'Disabled' }}
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                <div>
                    <div class="text-white/80 text-sm uppercase tracking-wide">Export Requests</div>
                    <div class="text-3xl font-bold mt-1">{{ $recentExports->count() }}</div>
                    <div class="text-xs mt-2 opacity-75">
                        Last 10 requests
                    </div>
                </div>
            </div>
        </div>

        <!-- Retention Settings Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <flux:heading size="lg">Data Retention Settings</flux:heading>
                <flux:subheading>Configure how long personal data is retained</flux:subheading>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ route('admin.gdpr.update-settings') }}">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-3 font-semibold">Entity Type</th>
                                    <th class="text-left py-3 px-3 font-semibold">Retention (months)</th>
                                    <th class="text-left py-3 px-3 font-semibold">Enabled</th>
                                    <th class="text-left py-3 px-3 font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($settings as $index => $setting)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-3 px-3">
                                        <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $setting->entity_type)) }}</span>
                                        <input type="hidden" name="settings[{{ $index }}][entity_type]" value="{{ $setting->entity_type }}">
                                    </td>
                                    <td class="py-3 px-3">
                                        <input 
                                            type="number" 
                                            name="settings[{{ $index }}][retention_months]" 
                                            value="{{ $setting->retention_months }}"
                                            class="w-24 px-4 py-2 border border-slate-200 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 focus:border-blue-500"
                                            min="1"
                                            max="120"
                                        />
                                    </td>
                                    <td class="py-3 px-3">
                                        <input 
                                            type="checkbox" 
                                            name="settings[{{ $index }}][is_enabled]"
                                            {{ $setting->is_enabled ? 'checked' : '' }}
                                            value="1"
                                            class="rounded border-gray-300 dark:border-gray-600"
                                        />
                                    </td>
                                    <td class="py-3 px-3">
                                        <select 
                                            name="settings[{{ $index }}][custom_action]"
                                            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 focus:border-blue-500"
                                        >
                                            <option value="anonymize" {{ $setting->custom_action == 'anonymize' ? 'selected' : '' }}>Anonymize</option>
                                            <option value="delete" {{ $setting->custom_action == 'delete' ? 'selected' : '' }}>Delete Permanently</option>
                                            <option value="notify" {{ $setting->custom_action == 'notify' ? 'selected' : '' }}>Notify Admin Only</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                            Save Retention Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Export Requests -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <flux:heading size="lg">Recent GDPR Export Requests</flux:heading>
                <flux:subheading>User data export history</flux:subheading>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-3 font-semibold">User</th>
                                <th class="text-left py-3 px-3 font-semibold">Requested At</th>
                                <th class="text-left py-3 px-3 font-semibold">Exported At</th>
                                <th class="text-left py-3 px-3 font-semibold">Expires At</th>
                                <th class="text-left py-3 px-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentExports as $export)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-3 px-3">{{ $export->user->name ?? 'Deleted User' }}</td>
                                <td class="py-3 px-3">{{ $export->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-3 px-3">{{ $export->exported_at ? $export->exported_at->format('Y-m-d H:i') : 'Pending' }}</td>
                                <td class="py-3 px-3">{{ $export->expires_at ? $export->expires_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                <td class="py-3 px-3">
                                    @if($export->expires_at && $export->expires_at->isPast())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Expired
                                        </span>
                                    @elseif($export->exported_at)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Ready
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Processing
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-gray-500 dark:text-gray-400">
                                    No export requests yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Import Settings Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <flux:heading size="lg">Import GDPR Settings</flux:heading>
                <flux:subheading>Restore settings from a JSON backup</flux:subheading>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ route('admin.gdpr.import-settings') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Settings JSON File
                            </label>
                            <input 
                                type="file" 
                                name="settings_file" 
                                accept=".json" 
                                required 
                                class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-200"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Upload a previously exported GDPR settings JSON file.
                            </p>
                        </div>
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-6 rounded-lg transition">
                            Import Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>