<?php

use Livewire\Component;
use App\Models\Contact;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ContactsImport;
use Illuminate\Support\Facades\DB;

new class extends Component
{
        use WithFileUploads;
 
    public $showModal = false;
    public $file;
    public $isImporting = false;
    public $importProgress = 0;
    public $importStats = [
        'total' => 0,
        'imported' => 0,
        'failed' => 0,
        'skipped' => 0,
    ];
    public $importErrors = [];
    public $importComplete = false;

    public $listeners = ['import-completed' => 'refreshContacts'];
 
    protected $rules = [
        'file' => 'required|file|mimes:xlsx,csv,xls|max:10240', // 10MB max
    ];
 
    protected $messages = [
        'file.required' => 'Please select a file to import.',
        'file.mimes' => 'The file must be an Excel or CSV file.',
        'file.max' => 'The file size must not exceed 10MB.',
    ];
 
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }
 
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }
 
    public function import()
    {
        try {
            $this->validate();
 
            $this->isImporting = true;
            $this->importProgress = 10;
 
            // Read the file
            $path = $this->file->getRealPath();
            $filename = $this->file->getClientOriginalName();
 
            // Get the file data
            $data = Excel::toArray(new ContactsImport(), $path);
 
            if (empty($data) || empty($data[0])) {
                throw new \Exception('The file appears to be empty or invalid.');
            }
 
            $rows = $data[0];
            $this->importStats['total'] = count($rows);
            $this->importProgress = 20;
 
            // Process the rows
            $this->processImport($rows);
 
            $this->importComplete = true;
            $this->importProgress = 100;
 
            // Dispatch event to refresh the contacts list
            $this->dispatch('import-completed');
 
            session()->flash('success', "Import completed! {$this->importStats['imported']} contacts imported successfully.");
 
        } catch (\Exception $e) {
            $this->isImporting = false;
            $this->importErrors[] = 'Error: ' . $e->getMessage();
            session()->flash('error', 'Import failed: ' . $e->getMessage());
        }
    }
 
    private function processImport($rows)
    {
        $headers = array_shift($rows); // Remove header row
        $imported = 0;
        $failed = 0;
        $skipped = 0;
        $batchSize = 50;
        $batch = [];
 
        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                $skipped++;
                continue;
            }
 
            try {
                // Map the row data
                $data = $this->mapRowData($headers, $row);
 
                // Validate required fields
                if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                    $this->importErrors[] = "Row " . ($index + 2) . ": Missing required fields (First Name, Last Name, Email).";
                    $failed++;
                    continue;
                }
 
                // Check if contact already exists by email
                if (Contact::where('email', $data['email'])->exists()) {
                    $this->importErrors[] = "Row " . ($index + 2) . ": Contact with email '{$data['email']}' already exists. Skipped.";
                    $skipped++;
                    continue;
                }
 
                $batch[] = $data;
                $imported++;
 
                // Insert batch when it reaches the batch size
                if (count($batch) >= $batchSize) {
                    Contact::insert($batch);
                    $batch = [];
                    $this->importProgress = 20 + (($imported / $this->importStats['total']) * 70);
                }
 
            } catch (\Exception $e) {
                $this->importErrors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                $failed++;
            }
        }
 
        // Insert remaining rows
        if (!empty($batch)) {
            Contact::insert($batch);
        }
 
        $this->importStats['imported'] = $imported;
        $this->importStats['failed'] = $failed;
        $this->importStats['skipped'] = $skipped;
    }
 
    private function mapRowData($headers, $row)
    {
        $data = [];
        $headerMap = array_flip(array_map('strtolower', $headers));
 
        $fieldMappings = [
            'first_name' => ['first_name', 'firstname', 'first name'],
            'last_name' => ['last_name', 'lastname', 'last name'],
            'email' => ['email', 'email_address', 'e-mail'],
            'phone' => ['phone', 'phone_number', 'telephone'],
            'street_address' => ['street_address', 'street address', 'address'],
            'city' => ['city'],
            'state' => ['state', 'province'],
            'postal_code' => ['postal_code', 'postcode', 'zip', 'zip_code'],
            'country' => ['country'],
            'ni_number' => ['ni_number', 'ni number', 'national_insurance'],
            'bank' => ['bank', 'bank_name'],
            'account_number' => ['account_number', 'account number'],
            'sort_code' => ['sort_code', 'sort code'],
            'date_of_birth' => ['date_of_birth', 'date of birth', 'dob', 'birth_date'],
            'marital_status' => ['marital_status', 'marital status'],
            'gender' => ['gender', 'sex'],
        ];
 
        foreach ($fieldMappings as $field => $possibleHeaders) {
            foreach ($possibleHeaders as $header) {
                if (isset($headerMap[$header])) {
                    $columnIndex = $headerMap[$header];
                    $value = $row[$columnIndex] ?? null;
                    
                    if ($field === 'date_of_birth' && !empty($value)) {
                        // Handle date conversion
                        try {
                            $value = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->toDateString();
                        } catch (\Exception $e) {
                            $value = null;
                        }
                    }
 
                    if (!empty($value)) {
                        $data[$field] = $value;
                    }
                    break;
                }
            }
        }
 
        // Set created_at and updated_at
        $data['created_at'] = now();
        $data['updated_at'] = now();
 
        return $data;
    }
 
    private function resetForm()
    {
        $this->file = null;
        $this->isImporting = false;
        $this->importProgress = 0;
        $this->importStats = [
            'total' => 0,
            'imported' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];
        $this->importErrors = [];
        $this->importComplete = false;
    }
 
    public function refreshContacts()
    {
        $this->dispatch('refresh-contacts');
    }
};
?>

<div>
    <!-- Trigger Button -->
    <button 
        @click="$wire.openModal()" 
        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        Import Contacts
    </button>

    <!-- Modal Overlay -->
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 transition-opacity duration-200" @click="$wire.closeModal()"></div>

    <!-- Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-green-50 to-green-100 dark:from-slate-800 dark:to-slate-700 border-b border-green-200 dark:border-slate-600 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Import Contacts</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Upload a file to import multiple contacts</p>
                </div>
                <button 
                    @click="$wire.closeModal()" 
                    class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
                @if(!$importComplete)
                    <!-- File Upload Section -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">
                            Select File to Import
                        </label>

                        <!-- Drag & Drop Area -->
                        <div 
                            @dragover.prevent="$el.classList.add('border-green-500', 'bg-green-50', 'dark:bg-green-900/10')"
                            @dragleave.prevent="$el.classList.remove('border-green-500', 'bg-green-50', 'dark:bg-green-900/10')"
                            @drop.prevent="$el.classList.remove('border-green-500', 'bg-green-50', 'dark:bg-green-900/10'); $wire.file = $event.dataTransfer.files[0]"
                            class="relative border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg p-8 text-center transition-colors duration-200 cursor-pointer hover:border-green-400 dark:hover:border-green-500"
                        >
                            <input 
                                type="file"
                                wire:model="file"
                                accept=".xlsx,.csv,.xls"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            >

                            @if(!$file)
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full">
                                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Drag and drop your file here</p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">or click to browse</p>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-500 mt-2">Supported formats: Excel (.xlsx, .xls) or CSV</p>
                                </div>
                            @else
                                <div class="flex items-center gap-3">
                                    <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                                    </svg>
                                    <div class="text-left flex-1">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $file->getClientOriginalName() }}</p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400">{{ $file->getSize() / 1024 }}KB</p>
                                    </div>
                                    <button 
                                        type="button"
                                        @click.prevent="$wire.file = null"
                                        class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        @error('file')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Format Guide -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm">
                                <p class="font-semibold text-blue-900 dark:text-blue-100">File Format Requirements</p>
                                <p class="text-blue-800 dark:text-blue-200 text-xs mt-1">Your file should have headers in the first row. Expected columns: First Name, Last Name, Email, Phone, Street Address, City, State, Postal Code, Country, Date of Birth, Gender, etc.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Progress Section -->
                @if($isImporting || $importComplete)
                    <div class="space-y-4">
                        <!-- Progress Bar -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Import Progress</span>
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $importProgress }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                <div 
                                    class="bg-gradient-to-r from-green-500 to-green-600 h-full transition-all duration-300"
                                    style="width: {{ $importProgress }}%"
                                ></div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-3 text-center">
                                <p class="text-xs text-slate-600 dark:text-slate-400 uppercase font-semibold">Total</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $importStats['total'] }}</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                                <p class="text-xs text-green-600 dark:text-green-400 uppercase font-semibold">Imported</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $importStats['imported'] }}</p>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 text-center">
                                <p class="text-xs text-yellow-600 dark:text-yellow-400 uppercase font-semibold">Skipped</p>
                                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $importStats['skipped'] }}</p>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 text-center">
                                <p class="text-xs text-red-600 dark:text-red-400 uppercase font-semibold">Failed</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $importStats['failed'] }}</p>
                            </div>
                        </div>

                        <!-- Error Messages -->
                        @if(!empty($importErrors))
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 max-h-48 overflow-y-auto">
                                <p class="text-sm font-semibold text-red-900 dark:text-red-100 mb-2">Import Errors & Warnings</p>
                                <ul class="space-y-1 text-xs text-red-800 dark:text-red-200">
                                    @forelse($importErrors as $error)
                                        <li class="flex items-start gap-2">
                                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span>{{ $error }}</span>
                                        </li>
                                    @empty
                                        <li>No errors</li>
                                    @endforelse
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Success Message -->
                @if($importComplete)
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-green-900 dark:text-green-100">Import Completed Successfully!</p>
                                <p class="text-sm text-green-800 dark:text-green-200 mt-1">{{ $importStats['imported'] }} contacts have been imported. Your contacts list has been updated.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer Actions -->
            <div class="border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-6 py-4 flex items-center gap-3 justify-end sticky bottom-0">
                @if(!$importComplete)
                    <button 
                        type="button"
                        @click="$wire.closeModal()"
                        class="px-6 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-medium transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        @click="$wire.import()"
                     
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 disabled:bg-slate-400 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors shadow-md hover:shadow-lg"
                    >
                        @if($isImporting)
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v8m0 0a8 8 0 11-8-8"></path>
                                </svg>
                                Importing...
                            </span>
                        @else
                            Start Import
                        @endif
                    </button>
                @else
                    <button 
                        type="button"
                        @click="$wire.closeModal()"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors shadow-md hover:shadow-lg"
                    >
                        Done
                    </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>