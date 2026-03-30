<div>
    {{-- Step 1: Upload CSV --}}
    @if ($step === 1)
        <div class="flex flex-col gap-4 mb-6">
            <div class="text-center md:text-left">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Import Diesel Generator Weekly Checklist</h1>
                <p class="text-gray-600 dark:text-gray-400">Upload a CSV file to bulk import Hatchery Diesel Generator Weekly Maintenance Checklist submissions</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-md dark:shadow-lg rounded-lg p-6">
            <form wire:submit="uploadAndParse">
                <div class="space-y-4">
                    <div
                        x-data="{ dragging: false }"
                        x-on:dragover.prevent="dragging = true"
                        x-on:dragleave.prevent="dragging = false"
                        x-on:drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                        :class="dragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'"
                        class="border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors hover:border-blue-400 dark:hover:border-blue-500"
                        @click="$refs.fileInput.click()"
                    >
                        <input type="file" wire:model="csvFile" accept=".csv,.txt" class="hidden" x-ref="fileInput" />
                        <div wire:loading.remove wire:target="csvFile">
                            <svg class="mx-auto w-12 h-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            @if ($csvFile)
                                <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ $csvFile->getClientOriginalName() }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ number_format($csvFile->getSize() / 1024, 1) }} KB — Click or drag to replace</p>
                            @else
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">CSV file (max 10MB)</p>
                            @endif
                        </div>
                        <div wire:loading wire:target="csvFile" class="flex flex-col items-center">
                            <svg class="animate-spin w-8 h-8 text-blue-500 mb-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Uploading file...</p>
                        </div>
                    </div>

                    @error('csvFile')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Expected CSV Format (Asana Export)</h3>
                        <ul class="text-xs text-blue-700 dark:text-blue-400 space-y-1 list-disc list-inside">
                            <li>Required columns: <strong>Maintenance Technician</strong>, <strong>Date Submitted</strong>, <strong>Diesel Generator from Form</strong></li>
                            <li>Notes column must contain the full form data (inspection statuses, test run readings, etc.)</li>
                            <li>Generator set format: "1 (375kVA Cummins Genset)" or "2 (275kVA Camda Genset)"</li>
                        </ul>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" @if (!$csvFile) disabled @endif>
                            <span wire:loading.remove wire:target="uploadAndParse">Parse CSV</span>
                            <span wire:loading wire:target="uploadAndParse" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Parsing...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    {{-- Step 2: Preview & Map Users --}}
    @if ($step === 2)
        <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
            <div class="text-center md:text-left">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Import Preview</h1>
                <p class="text-gray-600 dark:text-gray-400">Review parsed data and map technicians to system users before importing</p>
            </div>
            <button wire:click="resetImport" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">Back to Upload</button>
        </div>

        @if (count($parseErrors) > 0)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-2">Parse Warnings ({{ count($parseErrors) }})</h3>
                <div class="max-h-40 overflow-y-auto">
                    <ul class="text-xs text-yellow-700 dark:text-yellow-400 space-y-1 list-disc list-inside">
                        @foreach (array_slice($parseErrors, 0, 50) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        @if (count($parseErrors) > 50)
                            <li class="font-medium">... and {{ count($parseErrors) - 50 }} more</li>
                        @endif
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalCsvRows) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">CSV Rows Parsed</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalFormRecords) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Form Records to Create</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 text-center">
                <p class="text-2xl font-bold {{ $unmatchedCount > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">{{ count($csvNames) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Unique Technicians</p>
                @if ($unmatchedCount > 0)
                    <p class="text-xs text-orange-500 dark:text-orange-400">{{ $unmatchedCount }} need mapping</p>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-md dark:shadow-lg rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Technician to User Mapping</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Map each CSV technician name to an existing system user. All must be mapped to proceed.</p>
            <div class="space-y-3">
                @foreach ($csvNames as $index => $name)
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 p-3 rounded-lg {{ empty($userMapping[$index]) ? 'bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800' : 'bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800' }}">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">from CSV</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            <select wire:model.live="userMapping.{{ $index }}" class="w-full sm:w-64 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                <option value="">-- Select User --</option>
                                @foreach ($availableUsers as $user)
                                    <option value="{{ $user['id'] }}">{{ $user['full_name'] }} ({{ $user['username'] }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-md dark:shadow-lg rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sample Data (first 5 rows)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left table-auto min-w-max">
                    <thead>
                        <tr>
                            <th class="p-3 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-slate-200">Row</th>
                            <th class="p-3 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-slate-200">Technician</th>
                            <th class="p-3 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-slate-200">Date</th>
                            <th class="p-3 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-slate-200">Generator Set</th>
                            <th class="p-3 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-slate-200">Test Run</th>
                            <th class="p-3 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-slate-200">Tank Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($previewRows as $row)
                            <tr class="even:bg-slate-50 dark:even:bg-gray-700/50">
                                <td class="p-3 text-xs text-slate-800 dark:text-slate-200">{{ $row['csv_row'] }}</td>
                                <td class="p-3 text-xs text-slate-800 dark:text-slate-200">{{ $row['technician'] }}</td>
                                <td class="p-3 text-xs text-slate-800 dark:text-slate-200 whitespace-nowrap">{{ \Carbon\Carbon::parse($row['date_submitted'])->format('d M Y') }}</td>
                                <td class="p-3 text-xs text-slate-800 dark:text-slate-200">{{ $row['gen_set_label'] }}</td>
                                <td class="p-3 text-xs text-slate-800 dark:text-slate-200">{{ $row['form_fields']['test_run_conducted'] ?? 'N/A' }}</td>
                                <td class="p-3 text-xs text-slate-800 dark:text-slate-200">{{ $row['form_fields']['diesel_tank_level'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button wire:click="resetImport" class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">Cancel</button>
            <button wire:click="executeImport" wire:confirm="Are you sure you want to import {{ number_format($totalFormRecords) }} form records? This action cannot be undone." class="px-6 py-3 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors shadow-sm cursor-pointer disabled:opacity-50" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="executeImport">Import {{ number_format($totalFormRecords) }} Records</span>
                <span wire:loading wire:target="executeImport" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Importing...
                </span>
            </button>
        </div>
    @endif

    {{-- Step 3: Results --}}
    @if ($step === 3)
        <div class="flex flex-col gap-4 mb-6">
            <div class="text-center md:text-left">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Import Complete</h1>
                <p class="text-gray-600 dark:text-gray-400">CSV import has finished processing</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 text-center">
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-green-100 dark:bg-green-900/30">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($importedCount) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Records Imported</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 text-center">
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($skippedCount) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Records Skipped</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 text-center">
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-blue-100 dark:bg-blue-900/30">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalCsvRows) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">CSV Rows Processed</p>
            </div>
        </div>

        @if (count($importErrors) > 0)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-300 mb-2">Import Errors ({{ count($importErrors) }})</h3>
                <div class="max-h-40 overflow-y-auto">
                    <ul class="text-xs text-red-700 dark:text-red-400 space-y-1 list-disc list-inside">
                        @foreach (array_slice($importErrors, 0, 50) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="flex justify-end gap-3">
            <button wire:click="resetImport" class="px-6 py-3 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm cursor-pointer">Import Another File</button>
        </div>
    @endif
</div>
