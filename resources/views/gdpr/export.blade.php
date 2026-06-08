<x-layouts::app :title="__('GDPR Export')">


    <div class="max-w-2xl mx-auto">
        <flux:card>
            <div class="p-8 text-center">
          
                
                <flux:heading size="xl" class="mb-2">GDPR Data Export</flux:heading>
                <flux:subheading class="mb-6">Request a copy of your personal data</flux:subheading>

                @if(session('status'))
                    <flux:callout variant="success" heading="Export Requested" class="mb-6">
                        {{ session('status') }}
                    </flux:callout>
                @endif

                <div class="text-left space-y-4 mb-8">
                    <p>Under GDPR regulations, you have the right to request a copy of all personal data we hold about you.</p>
                    
                    <p>This export will include:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-400">
                        <li>Your profile information</li>
                        <li>Your contact details</li>
                        <li>Companies you're associated with</li>
                        <li>Deal history</li>
                        <li>Communication logs</li>
                    </ul>

                    <div class="bg-blue-50 dark:bg-blue-950 p-4 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Note:</strong> Once requested, you will receive a download link via email. 
                            The link will be valid for <strong>7 days</strong>.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('gdpr.export.request') }}">
                    @csrf

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition">
                        Request My Data Export
                    </button>
                </form>
            </div>
        </flux:card>
    </div>

</x-layouts::app>