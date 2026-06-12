<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Company;
use App\Imports\CompanyImport as CompanyImportClass;

new class extends Component
{
    use WithFileUploads;

    public $showModal = false;
    public $file;

    public $isImporting = false;
    public $importComplete = false;
    public $importProgress = 0;

    public $importStats = [
        'total' => 0,
        'imported' => 0,
        'failed' => 0,
        'skipped' => 0,
    ];

    public $importErrors = [];

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,csv,xls|max:10240',
    ];

    protected $messages = [
        'file.required' => 'Please select a file to import.',
        'file.mimes' => 'The file must be an Excel or CSV file.',
        'file.max' => 'File size cannot exceed 10MB.',
    ];

    public function openModal()
    {
        $this->resetForm();

        $this->resetValidation();
        $this->resetErrorBag();

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;

        $this->resetForm();

        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function removeFile()
    {
        $this->file = null;
    }

    public function import()
    {
        $this->validate();

        try {

            $this->isImporting = true;
            $this->importProgress = 10;

            $data = Excel::toArray(
                new CompanyImportClass(),
                $this->file->getRealPath()
            );

            if (
                empty($data) ||
                empty($data[0])
            ) {
                throw new Exception(
                    'The uploaded file is empty.'
                );
            }

            $rows = $data[0];

            // Remove header row
            $headers = array_shift($rows);

            $this->importStats['total'] =
                count($rows);

            $this->importProgress = 20;

            $this->processImport(
                $headers,
                $rows
            );

            $this->importProgress = 100;
            $this->importComplete = true;

            $this->dispatch(
                'import-completed'
            );

            $this->dispatch(
                'refresh-companies'
            );

            session()->flash(
                'success',
                "{$this->importStats['imported']} companies imported successfully."
            );

        } catch (\Throwable $e) {

            report($e);

            $this->importErrors[] =
                $e->getMessage();

            session()->flash(
                'error',
                'Import failed: ' .
                $e->getMessage()
            );

        } finally {

            $this->isImporting = false;
        }
    }

    private function processImport(
        $headers,
        $rows
    ) {
        $headerMap = array_flip(
            array_map(
                fn ($header) => strtolower(trim($header)),
                $headers
            )
        );

        $batch = [];
        $batchSize = 100;

        foreach ($rows as $index => $row) {

            if (
                empty(array_filter($row))
            ) {
                $this->importStats['skipped']++;
                continue;
            }

            try {

                $data = $this->mapRowData(
                    $row,
                    $headerMap
                );

                if (
                    empty($data['name'])
                ) {
                    $this->importErrors[] =
                        'Row ' .
                        ($index + 2) .
                        ': Company name is required.';

                    $this->importStats['failed']++;

                    continue;
                }

                // Skip duplicates
$exists = Company::where(
    'name',
    $data['name']
)->exists();

if ($exists) {

    $this->importStats['skipped']++;

    $this->importErrors[] =
        'Row ' .
        ($index + 2) .
        ': Company already exists.';

    continue;
}

/**
 * Add to batch
 */
$batch[] = array_merge(
    $data,
    [
        'created_at' => now(),
        'updated_at' => now(),
    ]
);

$this->importStats['imported']++;

/**
 * Batch insert
 */
if (count($batch) >= $batchSize) {

    Company::insert($batch);

    // Reset batch after insert
    $batch = [];
}

/**
 * Progress update
 */
$processed =
    $this->importStats['imported'] +
    $this->importStats['failed'] +
    $this->importStats['skipped'];

                $this->importProgress =
                    20 +
                    round(
                        (
                            $processed /
                            max(
                                $this->importStats['total'],
                                1
                            )
                        ) * 80
                    );

            } catch (\Throwable $e) {

                $this->importStats['failed']++;

                $this->importErrors[] =
                    'Row ' .
                    ($index + 2) .
                    ': ' .
                    $e->getMessage();
            }
        }

        // Insert remaining batch
        if (! empty($batch)) {
            Company::insert(
                $batch
            );
        }
    }

    private function mapRowData(
        $row,
        $headerMap
    ) {
        return [
            'name' => $this->getColumnValue(
                $row,
                $headerMap,
                ['name', 'company_name', 'company name']
            ),

            'email' => $this->getColumnValue(
                $row,
                $headerMap,
                ['email', 'company_email']
            ),

            'phone' => $this->getColumnValue(
                $row,
                $headerMap,
                ['phone', 'company_phone']
            ),

            'domain' => $this->getColumnValue(
                $row,
                $headerMap,
                ['domain', 'website']
            ),
        ];
    }

    private function getColumnValue(
        $row,
        $headerMap,
        array $possibleHeaders
    ) {
        foreach (
            $possibleHeaders
            as $header
        ) {
            if (
                isset(
                    $headerMap[$header]
                )
            ) {
                return trim(
                    $row[
                        $headerMap[$header]
                    ] ?? ''
                ) ?? null;
            }
        }

        return null;
    }

    private function resetForm()
    {
        $this->reset([
            'file',
            'isImporting',
            'importComplete',
            'importProgress',
            'importErrors',
        ]);

        $this->importStats = [
            'total' => 0,
            'imported' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];
    }
};

?>

<div>

    <!-- Button -->
    <button
        wire:click="openModal"
        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 font-medium text-white shadow-md transition hover:bg-green-700">

        <svg class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24">

            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
            </path>
        </svg>

        Import Companies
    </button>

    @if($showModal)

        <!-- Overlay -->
        <div
            wire:click="closeModal"
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm">
        </div>

        <!-- Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">

            <div class="w-full max-w-2xl rounded-xl bg-white shadow-2xl dark:bg-slate-900">

                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        Import Companies
                    </h2>
                </div>

                <div class="space-y-5 p-6">

                    <!-- Upload -->
                    @if(! $file)

                        <label class="block cursor-pointer rounded-xl border-2 border-dashed border-slate-300 p-10 text-center transition hover:border-green-500 dark:border-slate-700">

                            <input
                                type="file"
                                wire:model="file"
                                accept=".xlsx,.xls,.csv"
                                class="hidden">

                            <p class="font-medium">
                                Click to upload
                            </p>

                            <p class="text-sm text-slate-500">
                                Excel or CSV only
                            </p>
                        </label>

                    @else

                        <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <div class="flex items-center justify-between">

                                <div>
                                    <p class="font-medium">
                                        {{ $file->getClientOriginalName() }}
                                    </p>

                                    <p class="text-sm text-slate-500">
                                        {{ round($file->getSize() / 1024, 2) }} KB
                                    </p>
                                </div>

                                <button
                                    wire:click="removeFile"
                                    class="text-red-500 hover:text-red-700">
                                    Remove
                                </button>
                            </div>
                        </div>

                    @endif

                    @error('file')
                        <p class="text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                    <!-- Progress -->
                    @if($isImporting || $importComplete)

                        <div class="space-y-3">

                            <div class="h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">

                                <div
                                    class="h-full bg-green-600 transition-all duration-300"
                                    style="width: {{ $importProgress }}%">
                                </div>
                            </div>

                            <p class="text-sm text-slate-500">
                                {{ $importProgress }}%
                            </p>
                        </div>

                    @endif

                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">

                    <button
                        wire:click="closeModal"
                        class="rounded-lg border border-slate-300 px-5 py-2 hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">

                        Cancel
                    </button>

                    <button
                        wire:click="import"
                        wire:loading.attr="disabled"
                        wire:target="import"
                        class="rounded-lg bg-green-600 px-5 py-2 text-white transition hover:bg-green-700 disabled:opacity-50">

                        <span
                            wire:loading.remove
                            wire:target="import">
                            Start Import
                        </span>

                        <span
                            wire:loading
                            wire:target="import">
                            Importing...
                        </span>
                    </button>

                </div>
            </div>
        </div>

    @endif
</div>