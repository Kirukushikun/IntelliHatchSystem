<?php

namespace App\Livewire\Admin\ImportForms;

use App\Models\Form;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class IncubatorRoutine extends Component
{
    use WithFileUploads;

    public $csvFile;
    public int $step = 1; // 1=upload, 2=preview/map, 3=result

    // Parsed data
    public array $parsedRows = [];
    public array $parseErrors = [];
    public int $totalCsvRows = 0;
    public int $totalFormRecords = 0;

    // User mapping: CSV name => selected user ID
    public array $userMapping = [];
    public array $unmatchedNames = [];
    public array $availableUsers = [];

    // Import results
    public int $importedCount = 0;
    public int $skippedCount = 0;
    public array $importErrors = [];

    // CSV column to form field mapping
    protected const FIELD_MAP = [
        'PLENUM-INCUBATOR ROOF/PLENUM' => 'cleaning_incubator_roof_and_plenum',
        'GM-INCUBATOR DOOR' => 'check_incubator_doors_for_air_leakage',
        'GM-BAGGY/GASKET CHECK' => 'checking_of_baggy_against_the_gaskets',
        'GM-CURTAIN CHECK' => 'check_curtain_position_and_condition',
        'GM-WICK CHECK' => 'check_wick_for_replacement_washing',
        'GM-SPRAY NOZZLE CHECK' => 'check_spray_nozzle_and_water_pan',
        'GM-INCUBATOR FAN CHECK' => 'check_incubator_fans_for_vibration',
        'GM-RACK BAFFLE CHECK' => 'check_rack_baffle_condition',
        'GM-DRAIN WATER' => 'drain_water_out_from_air_compressor_tank',
        'CLEANING-CHECK WATER LEVEL' => 'check_water_level_of_blue_tank',
        'CLEANING-CLEAN INCUBATOR' => 'cleaning_of_incubator_floor_area',
        'CLEANING-CLEAN ENTRANCE FLOOR' => 'cleaning_of_entrance_and_exit_area_flooring',
        'CLEANING-REFILL WATER RESERVOIR' => 'clean_and_refill_water_reservoir',
        'OTHER-EGG SETTING PREP' => 'egg_setting_preparation',
        'OTHER-EGG SETTING ' => 'egg_setting',
        'OTHER-RECORD EGG SETTING' => 'record_egg_setting_on_board',
        'OTHER-RECORD EGG SETTING TIME' => 'record_egg_setting_time',
        'OTHER-ASSIST RANDOM CANDLING' => 'assist_in_random_candling',
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
        $this->parsedRows = [];
        $this->unmatchedNames = [];
        $this->userMapping = [];

        $path = $this->csvFile->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            $this->parseErrors[] = 'Could not open uploaded CSV file.';
            return;
        }

        // Read header
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            $this->parseErrors[] = 'CSV file is empty or has no header row.';
            return;
        }

        // Trim BOM and whitespace from headers
        $header = array_map(function ($h) {
            return trim(preg_replace('/^\x{FEFF}/u', '', $h));
        }, $header);

        // Validate required columns exist
        $requiredColumns = ['Hatcheryman', 'Date Submitted', 'SHIFT FROM FORM', 'Notes'];
        $missingColumns = array_diff($requiredColumns, $header);
        if (! empty($missingColumns)) {
            fclose($handle);
            $this->parseErrors[] = 'Missing required columns: ' . implode(', ', $missingColumns);
            return;
        }

        // Load incubator machines for lookup
        $incubators = DB::table('incubator-machines')->get();
        $incubatorMap = $incubators->keyBy('id');

        // Load existing users for auto-matching
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

            // Skip empty rows
            if (count(array_filter($row)) === 0) {
                continue;
            }

            // Combine header with row values
            $data = [];
            foreach ($header as $i => $col) {
                $data[$col] = $row[$i] ?? '';
            }

            $hatcheryman = trim($data['Hatcheryman'] ?? '');
            $dateSubmitted = trim($data['Date Submitted'] ?? '');
            $shiftFromForm = trim($data['SHIFT FROM FORM'] ?? '');
            $notes = $data['Notes'] ?? '';

            // Skip rows with no hatcheryman
            if (empty($hatcheryman)) {
                continue;
            }

            $allNames[$hatcheryman] = true;

            // Parse shift
            $shift = $this->normalizeShift($shiftFromForm);
            if (! $shift) {
                $this->parseErrors[] = "Row {$rowNum}: Invalid shift value '{$shiftFromForm}'";
                continue;
            }

            // Parse date
            $parsedDate = $this->parseDate($dateSubmitted);
            if (! $parsedDate) {
                $this->parseErrors[] = "Row {$rowNum}: Could not parse date '{$dateSubmitted}'";
                continue;
            }

            // Parse alarm and corrective action from Notes
            $alarm = $this->extractFromNotes($notes, 'Alarm system condition');
            $correctiveAction = $this->extractFromNotes($notes, 'Corrective Action');

            // Parse incubator machines from Notes
            $machineNumbers = $this->extractMachineNumbers($notes);
            if (empty($machineNumbers)) {
                $this->parseErrors[] = "Row {$rowNum}: No incubator machines found in Notes";
                continue;
            }

            // Map checklist fields
            $checklist = [];
            foreach (self::FIELD_MAP as $csvCol => $formField) {
                $value = trim($data[$csvCol] ?? '');
                $checklist[$formField] = $this->normalizeChecklistValue($value);
            }

            // Store parsed row (will be expanded per machine during import)
            $rows[] = [
                'csv_row' => $rowNum,
                'hatcheryman' => $hatcheryman,
                'date_submitted' => $parsedDate,
                'shift' => $shift,
                'alarm_system_condition' => $alarm ?: 'Operational',
                'corrective_action' => $correctiveAction ?: 'N/A',
                'machines' => $machineNumbers,
                'checklist' => $checklist,
            ];
        }

        fclose($handle);

        $this->parsedRows = $rows;
        $this->totalCsvRows = count($rows);
        $this->totalFormRecords = array_sum(array_map(fn ($r) => count($r['machines']), $rows));

        // Auto-match users, collect unmatched
        foreach (array_keys($allNames) as $name) {
            $lowerName = strtolower(trim($name));
            if (isset($userNameMap[$lowerName])) {
                $this->userMapping[$name] = $userNameMap[$lowerName];
            } else {
                $this->unmatchedNames[] = $name;
                $this->userMapping[$name] = '';
            }
        }

        $this->step = 2;
    }

    public function executeImport(): void
    {
        // Validate all users are mapped
        foreach ($this->userMapping as $name => $userId) {
            if (empty($userId)) {
                $this->dispatch('showToast', message: "Please map all hatcherymen to users before importing. '{$name}' is unmapped.", type: 'error');
                return;
            }
        }

        $formTypeId = DB::table('form_types')
            ->where('form_name', 'Incubator Routine Checklist Per Shift')
            ->value('id');

        if (! $formTypeId) {
            $this->importErrors[] = 'Form type "Incubator Routine Checklist Per Shift" not found in database.';
            $this->step = 3;
            return;
        }

        $incubators = DB::table('incubator-machines')->get()->keyBy('id');

        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->importErrors = [];

        DB::beginTransaction();
        try {
            $batch = [];

            foreach ($this->parsedRows as $row) {
                $userId = $this->userMapping[$row['hatcheryman']] ?? null;
                if (! $userId) {
                    $this->skippedCount += count($row['machines']);
                    continue;
                }

                foreach ($row['machines'] as $machineNum) {
                    $machineId = (int) $machineNum;
                    $incubator = $incubators->get($machineId);

                    if (! $incubator) {
                        $this->importErrors[] = "Row {$row['csv_row']}: Incubator machine #{$machineNum} not found in database, skipped.";
                        $this->skippedCount++;
                        continue;
                    }

                    $formInputs = array_merge($row['checklist'], [
                        'shift' => $row['shift'],
                        'alarm_system_condition' => $row['alarm_system_condition'],
                        'corrective_action' => $row['corrective_action'],
                        'incubator' => $machineId,
                        'machine_info' => [
                            'table' => 'incubator-machines',
                            'id' => $machineId,
                            'name' => $incubator->incubatorName,
                        ],
                    ]);

                    $batch[] = [
                        'form_type_id' => $formTypeId,
                        'form_inputs' => json_encode($formInputs),
                        'date_submitted' => $row['date_submitted'],
                        'uploaded_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Insert in chunks to avoid memory issues
                    if (count($batch) >= 500) {
                        DB::table('forms')->insert($batch);
                        $this->importedCount += count($batch);
                        $batch = [];
                    }
                }
            }

            // Insert remaining
            if (! empty($batch)) {
                DB::table('forms')->insert($batch);
                $this->importedCount += count($batch);
            }

            DB::commit();

            ActivityLogger::log(
                'csv_import',
                "Imported {$this->importedCount} Incubator Routine form records from CSV ({$this->totalCsvRows} CSV rows)",
                Form::class,
                null,
                [
                    'form_type' => 'Incubator Routine Checklist Per Shift',
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
        $this->reset(['csvFile', 'step', 'parsedRows', 'parseErrors', 'totalCsvRows', 'totalFormRecords', 'userMapping', 'unmatchedNames', 'importedCount', 'skippedCount', 'importErrors']);
    }

    protected function normalizeShift(string $value): ?string
    {
        $map = [
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

        // Try common formats: "30-Mar-26", "3/30/2026", "Mar 30, 2026"
        $formats = ['d-M-y', 'n/j/Y', 'M d, Y', 'Y-m-d', 'd/m/Y'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateStr);
            if ($date && $date->format($format) === $dateStr) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        // Fallback: try PHP's strtotime
        $ts = strtotime($dateStr);
        if ($ts !== false) {
            return date('Y-m-d H:i:s', $ts);
        }

        return null;
    }

    protected function normalizeChecklistValue(string $value): string
    {
        $upper = strtoupper(trim($value));

        return match ($upper) {
            'DONE' => 'Done',
            'PENDING' => 'Pending',
            'N/A', '' => 'N/A',
            default => $value,
        };
    }

    protected function extractFromNotes(string $notes, string $field): ?string
    {
        $pattern = '/' . preg_quote($field, '/') . ':\s*(.+)/i';
        if (preg_match($pattern, $notes, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function extractMachineNumbers(string $notes): array
    {
        if (preg_match('/Incubator Machine Inspected:\s*([\d\s\n,]+?)(?:\n\n|———|$)/s', $notes, $matches)) {
            return array_values(array_filter(
                array_map('trim', preg_split('/[\s,]+/', $matches[1])),
                fn ($v) => $v !== '' && is_numeric($v)
            ));
        }

        return [];
    }

    public function render()
    {
        return view('livewire.admin.import-forms.incubator-routine');
    }
}
