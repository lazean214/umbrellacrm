<?php
// If Livewire is causing issues, use this simpler approach
// Place this in routes/web.php or a controller

Route::post('/import-companies', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
    ]);

    try {
        \Maatwebsite\Excel\Facades\Excel::import(
            new \App\Imports\CompanyImport,
            $request->file('file')
        );

        return back()->with('success', '✅ ' . count($request->file('file')) . ' companies imported!');
    } catch (\Exception $e) {
        return back()->with('error', '❌ Import failed: ' . $e->getMessage());
    }
})->name('import.companies');
?>

<!-- Simple HTML form without Livewire -->
<!-- Save as: resources/views/import.blade.php -->

<div class="w-full max-w-md mx-auto p-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Import Companies</h2>
        <p class="text-gray-500 mb-6">Upload CSV or Excel file</p>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-md text-sm border border-green-300">
                {{ session('success') }}
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-md text-sm border border-red-300">
                {{ session('error') }}
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('import.companies') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- File Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select File
                </label>
                <input 
                    type="file" 
                    name="file"
                    accept=".csv,.xlsx,.xls"
                    required
                    class="block w-full text-sm text-gray-400
                        file:mr-3 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100
                        cursor-pointer"
                >
            </div>

            <!-- Error for file field -->
            @error('file')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition"
            >
                📤 Import Companies
            </button>

        </form>

        <!-- Help Text -->
        <div class="mt-6 p-3 bg-gray-50 rounded-md text-xs text-gray-600">
            <p class="font-semibold mb-1">📋 File Requirements:</p>
            <p><strong>Columns needed:</strong></p>
            <ul class="list-disc list-inside mt-1">
                <li>Company name</li>
                <li>Email (optional)</li>
                <li>Phone (optional)</li>
            </ul>
            <p class="mt-2"><strong>File format:</strong> CSV, XLSX, or XLS</p>
            <p><strong>Max size:</strong> 10 MB</p>
        </div>

    </div>
</div>
