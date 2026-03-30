<?php

namespace App\Livewire\Admin\ImportForms;

use App\Models\Form;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class HatcherTempCalibration extends Component
{
    use WithFileUploads;

    public $csvFile;
    public int $step = 1; // 1=upload, 2=preview/map, 3=result

    // Parsed data (only preview rows kept in Livewire state)
    public array $previewRows = [];
    public array $parseErrors = [];
    public int $totalCsvRows = 0;
    public int $totalFormRecords = 0;
    public string $cacheKey = '';

    // User mapping: index => selected user ID, csvNames: index => CSV name
    public array $userMapping = [];
    public array $csvNames = [];
    public int $unmatchedCount = 0;
    public array $availableUsers = [];

    // Import results
    public int $importedCount = 0;
    public int $skippedCount = 0;
    public array $importErrors = [];

    /**
     * Hatcher column definitions by index position.
     * Column 30 (index 29) is Hatcher 5 Humidity but CSV has a duplicate header name,
     * so we map by column index instead.
     *
     * Each entry: [hatcher_number, temp_col_index, humidity_col_index]
     */
    protected const HATCHER_COLUMNS = [
        [1,  20, 21],
        [2,  22, 23],
        [3,  24, 25],
        [4,  26, 27],
        [5,  28, 29],
        [6,  30, 31],
        [7,  32, 33],
        [8,  34, 35],
        [9,  36, 37],
        [10, 38, 39],
    ];

    protected function rules(): array
    {
        return [
            'csvFile' => 'required|file|mimes:csv,txt|max:10240',
        ];
    }

    public function mount(): void
    {
        $this->availableUsers = User::query()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'username'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'full_name' => $u->full_name,
                'username' => $u->username,
            ])
            ->toArray();
    }

    public function uploadAndParse(): void
    {
        $this->validate();

        $this->parseErrors = [];
        $this->previewRows = [];
        $this->unmatchedNames = [];
        $this->userMapping = [];

        $path = $this->csvFile->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            $this->parseErrors[] = 'Could not open uploaded CSV file.';
            return;
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            $this->parseErrors[] = 'CSV file is empty or has no header row.';
            return;
        }

        $header = array_map(function ($h) {
            return trim(preg_replace('/^\x{FEFF}/u', '', $h));
        }, $header);

        if (count($header) < 43) {
            fclose($handle);
            $this->parseErrors[] = 'CSV has ' . count($header) . ' columns, expected at least 43.';
            return;
        }

        $users = User::all();
        $userNameMap = [];
        foreach ($users as $user) {
            $fullName = strtolower(trim($user->first_name . ' ' . $user->last_name));
            $userNameMap[$fullName] = $user->id;
        }

        $rowNum = 1;
        $allNames = [];
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            if (count(array_filter($row)) === 0) {
                continue;
            }

            $hatcheryman = trim($row[16] ?? '');
            $dateSubmitted = trim($row[17] ?? '');
            $shift = trim($row[18] ?? '');
            $timeStarted = trim($row[19] ?? '');
            $correctiveAction = trim($row[40] ?? '');
            $approver = trim($row[41] ?? '');
            $timeFinished = trim($row[42] ?? '');

            if (empty($hatcheryman) || is_numeric($hatcheryman)) {
                continue;
            }

            $allNames[$hatcheryman] = true;

            $normalizedShift = $this->normalizeShift($shift);
            if (! $normalizedShift) {
                $this->parseErrors[] = "Row {$rowNum}: Invalid shift '{$shift}'";
                continue;
            }

            $parsedDate = $this->parseDate($dateSubmitted);
            if (! $parsedDate) {
                $this->parseErrors[] = "Row {$rowNum}: Could not parse date '{$dateSubmitted}'";
                continue;
            }

            $parsedTimeStarted = $this->normalizeTime($timeStarted);
            $parsedTimeFinished = $this->normalizeTime($timeFinished);

            $hatcherReadings = [];
            foreach (self::HATCHER_COLUMNS as [$hatcherNum, $tempColIdx, $humidityColIdx]) {
                $tempRaw = trim($row[$tempColIdx] ?? '');
                $humidityRaw = trim($row[$humidityColIdx] ?? '');

                if (($tempRaw === '' || $tempRaw === '0') && ($humidityRaw === '' || $humidityRaw === '0')) {
                    continue;
                }

                $temps = $this->parseTemperatureReading($tempRaw);
                $humidity = $this->parseNumericValue($humidityRaw);

                if ($temps === null && $humidity === null) {
                    continue;
                }

                $hatcherReadings[] = [
                    'hatcher_num' => $hatcherNum,
                    'machine_temp' => $temps['machine'] ?? 0,
                    'calibrator_temp' => $temps['calibrator'] ?? 0,
                    'humidity_reading' => $humidity ?? 0,
                ];
            }

            if (empty($hatcherReadings)) {
                $this->parseErrors[] = "Row {$rowNum}: No valid hatcher readings found";
                continue;
            }

            $rows[] = [
                'csv_row' => $rowNum,
                'hatcheryman' => $hatcheryman,
                'date_submitted' => $parsedDate,
                'shift' => $normalizedShift,
                'time_started' => $parsedTimeStarted ?: '',
                'time_finished' => $parsedTimeFinished ?: '',
                'corrective_action' => $correctiveAction ?: 'N/A',
                'approver' => $approver ?: 'N/A',
                'hatchers' => $hatcherReadings,
            ];
        }

        fclose($handle);

        // Store full data in cache, keep only preview in Livewire state
        $this->cacheKey = 'import_hatcher_temp_' . auth()->id() . '_' . now()->timestamp;
        Cache::put($this->cacheKey, $rows, now()->addHour());

        $this->previewRows = array_slice($rows, 0, 5);
        $this->totalCsvRows = count($rows);
        $this->totalFormRecords = array_sum(array_map(fn ($r) => count($r['hatchers']), $rows));

        $index = 0;
        foreach (array_keys($allNames) as $name) {
            $lowerName = strtolower(trim($name));
            $this->csvNames[$index] = $name;
            if (isset($userNameMap[$lowerName])) {
                $this->userMapping[$index] = (string) $userNameMap[$lowerName];
            } else {
                $this->unmatchedCount++;
                $this->userMapping[$index] = '';
            }
            $index++;
        }

        $this->step = 2;
    }

    public function executeImport(): void
    {
        // Build name => userId map from indexed arrays
        $nameToUser = [];
        foreach ($this->csvNames as $index => $name) {
            $userId = $this->userMapping[$index] ?? '';
            if (empty($userId)) {
                $this->dispatch('showToast', message: "Please map all hatcherymen to users before importing. '{$name}' is unmapped.", type: 'error');
                return;
            }
            $nameToUser[$name] = $userId;
        }

        // Retrieve full data from cache
        $parsedRows = Cache::get($this->cacheKey);
        if (! $parsedRows) {
            $this->importErrors[] = 'Parsed data expired. Please re-upload the CSV file.';
            $this->step = 3;
            return;
        }

        $formTypeId = DB::table('form_types')
            ->where('form_name', 'Hatcher Temperature Calibration')
            ->value('id');

        if (! $formTypeId) {
            $this->importErrors[] = 'Form type "Hatcher Temperature Calibration" not found in database.';
            $this->step = 3;
            return;
        }

        $hatchers = DB::table('hatcher-machines')->get()->keyBy('id');

        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->importErrors = [];

        DB::beginTransaction();
        try {
            $batch = [];

            foreach ($parsedRows as $row) {
                $userId = $nameToUser[$row['hatcheryman']] ?? null;
                if (! $userId) {
                    $this->skippedCount += count($row['hatchers']);
                    continue;
                }

                foreach ($row['hatchers'] as $reading) {
                    $machineId = (int) $reading['hatcher_num'];
                    $hatcher = $hatchers->get($machineId);

                    if (! $hatcher) {
                        $this->importErrors[] = "Row {$row['csv_row']}: Hatcher #{$reading['hatcher_num']} not found in database, skipped.";
                        $this->skippedCount++;
                        continue;
                    }

                    $formInputs = [
                        'shift' => $row['shift'],
                        'time_started' => $row['time_started'],
                        'machine_temp' => $reading['machine_temp'],
                        'calibrator_temp' => $reading['calibrator_temp'],
                        'humidity_reading' => $reading['humidity_reading'],
                        'approver' => $row['approver'],
                        'time_finished' => $row['time_finished'],
                        'corrective_action' => $row['corrective_action'],
                        'date_submitted' => $row['date_submitted'],
                        'machine_info' => [
                            'table' => 'hatcher-machines',
                            'id' => $machineId,
                            'name' => $hatcher->hatcherName,
                        ],
                    ];

                    $batch[] = [
                        'form_type_id' => $formTypeId,
                        'form_inputs' => json_encode($formInputs),
                        'date_submitted' => $row['date_submitted'],
                        'uploaded_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($batch) >= 500) {
                        DB::table('forms')->insert($batch);
                        $this->importedCount += count($batch);
                        $batch = [];
                    }
                }
            }

            if (! empty($batch)) {
                DB::table('forms')->insert($batch);
                $this->importedCount += count($batch);
            }

            DB::commit();

            // Clean up cache
            Cache::forget($this->cacheKey);

            ActivityLogger::log(
                'csv_import',
                "Imported {$this->importedCount} Hatcher Temperature Calibration form records from CSV ({$this->totalCsvRows} CSV rows)",
                Form::class,
                null,
                [
                    'form_type' => 'Hatcher Temperature Calibration',
                    'csv_rows' => $this->totalCsvRows,
                    'records_imported' => $this->importedCount,
                    'records_skipped' => $this->skippedCount,
                ]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $this->importErrors[] = 'Import failed: ' . $e->getMessage();
        }

        $this->step = 3;
    }

    public function resetImport(): void
    {
        if ($this->cacheKey) {
            Cache::forget($this->cacheKey);
        }
        $this->reset(['csvFile', 'step', 'previewRows', 'parseErrors', 'totalCsvRows', 'totalFormRecords', 'userMapping', 'csvNames', 'unmatchedCount', 'importedCount', 'skippedCount', 'importErrors', 'cacheKey']);
    }

    protected function normalizeShift(string $value): ?string
    {
        $map = [
            '1ST' => '1st Shift',
            '2ND' => '2nd Shift',
            '3RD' => '3rd Shift',
            '1ST SHIFT' => '1st Shift',
            '2ND SHIFT' => '2nd Shift',
            '3RD SHIFT' => '3rd Shift',
        ];

        return $map[strtoupper(trim($value))] ?? null;
    }

    protected function parseDate(string $dateStr): ?string
    {
        $dateStr = trim($dateStr);
        if (empty($dateStr)) {
            return null;
        }

        $formats = ['d-M-y', 'n/j/Y', 'M d, Y', 'Y-m-d', 'd/m/Y'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateStr);
            if ($date && $date->format($format) === $dateStr) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        $ts = strtotime($dateStr);
        if ($ts !== false) {
            return date('Y-m-d H:i:s', $ts);
        }

        return null;
    }

    protected function normalizeTime(string $timeStr): ?string
    {
        $timeStr = trim($timeStr);
        if (empty($timeStr)) {
            return null;
        }

        $ts = strtotime($timeStr);
        if ($ts !== false) {
            return date('H:i', $ts);
        }

        return $timeStr;
    }

    protected function parseTemperatureReading(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '' || $raw === '0') {
            return null;
        }

        if (preg_match_all('/(\d+\.?\d*)/', $raw, $matches)) {
            $numbers = $matches[1];
            if (count($numbers) >= 2) {
                return [
                    'machine' => (float) $numbers[0],
                    'calibrator' => (float) $numbers[1],
                ];
            }
            if (count($numbers) === 1) {
                return [
                    'machine' => (float) $numbers[0],
                    'calibrator' => (float) $numbers[0],
                ];
            }
        }

        return null;
    }

    protected function parseNumericValue(string $raw): ?float
    {
        $raw = trim($raw);
        if ($raw === '' || $raw === '0') {
            return null;
        }

        if (is_numeric($raw)) {
            return (float) $raw;
        }

        if (preg_match('/(\d+\.?\d*)/', $raw, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    public function render()
    {
        return view('livewire.admin.import-forms.hatcher-temp-calibration');
    }
}
