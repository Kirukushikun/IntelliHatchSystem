<?php

namespace App\Livewire\Admin\ImportForms;

use App\Models\Form;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class DieselGeneratorWeekly extends Component
{
    use WithFileUploads;

    public $csvFile;
    public int $step = 1;

    public array $previewRows = [];
    public array $parseErrors = [];
    public int $totalCsvRows = 0;
    public int $totalFormRecords = 0;
    public string $cacheKey = '';

    public array $userMapping = [];
    public array $csvNames = [];
    public int $unmatchedCount = 0;
    public array $availableUsers = [];

    public int $importedCount = 0;
    public int $skippedCount = 0;
    public array $importErrors = [];

    /**
     * Notes label → form field mapping.
     * Each section has: status label, problem label prefix, corrective action label.
     * We parse them in order from the Notes text.
     */
    protected const SECTION_MAP = [
        [
            'status_label' => 'LUBRICATION- Check for leaks',
            'status_field' => 'lub_leaks_status',
            'problem_field' => 'lub_leaks_problem',
            'corrective_field' => 'lub_leaks_corrective_action',
        ],
        [
            'status_label' => 'LUBRICATION- Check for oil level',
            'status_field' => 'lub_oil_level_status',
            'problem_field' => 'lub_oil_level_problem',
            'corrective_field' => 'lub_oil_level_corrective_action',
        ],
        [
            'status_label' => 'COOLING SYSTEM -Check for leaks',
            'status_field' => 'cool_leaks_status',
            'problem_field' => 'cool_leaks_problem',
            'corrective_field' => 'cool_leaks_corrective_action',
        ],
        [
            'status_label' => 'COOLING SYSTEM -Check for radiator restriction',
            'status_field' => 'cool_radiator_status',
            'problem_field' => 'cool_radiator_problem',
            'corrective_field' => 'cool_radiator_corrective_action',
        ],
        [
            'status_label' => 'COOLING SYSTEM -Check for hose and connections',
            'status_field' => 'cool_hose_status',
            'problem_field' => 'cool_hose_problem',
            'corrective_field' => 'cool_hose_corrective_action',
        ],
        [
            'status_label' => 'COOLING SYSTEM -Coolant level',
            'status_field' => 'cool_coolant_level_status',
            'problem_field' => 'cool_coolant_level_problem',
            'corrective_field' => 'cool_coolant_level_corrective_action',
        ],
        [
            'status_label' => 'COOLING SYSTEM - Belt condition and tension',
            'status_field' => 'cool_belt_status',
            'problem_field' => 'cool_belt_problem',
            'corrective_field' => 'cool_belt_corrective_action',
        ],
        [
            'status_label' => 'FUEL - Check for leaks',
            'status_field' => 'fuel_leaks_status',
            'problem_field' => 'fuel_leaks_problem',
            'corrective_field' => 'fuel_leaks_corrective_action',
        ],
        [
            'status_label' => 'AIR IN-TAKE (Check for leaks)',
            'status_field' => 'air_intake_leaks_status',
            'problem_field' => 'air_intake_leaks_problem',
            'corrective_field' => 'air_intake_leaks_corrective_action',
        ],
        [
            'status_label' => 'AIR IN-TAKE (Check for air cleaner restriction)',
            'status_field' => 'air_intake_cleaner_status',
            'problem_field' => 'air_intake_cleaner_problem',
            'corrective_field' => 'air_intake_cleaner_corrective_action',
        ],
        [
            'status_label' => 'EXHAUST- Check for leaks',
            'status_field' => 'exhaust_leaks_status',
            'problem_field' => 'exhaust_leaks_problem',
            'corrective_field' => 'exhaust_leaks_corrective_action',
        ],
        [
            'status_label' => 'ENGINE RELATED - Check for unusual vibration',
            'status_field' => 'engine_vibration_status',
            'problem_field' => 'engine_vibration_problem',
            'corrective_field' => 'engine_vibration_corrective_action',
        ],
        [
            'status_label' => 'MAIN GENERATOR - Check for air inlet and outlet from restrictions',
            'status_field' => 'main_gen_air_status',
            'problem_field' => 'main_gen_air_problem',
            'corrective_field' => 'main_gen_air_corrective_action',
        ],
        [
            'status_label' => 'MAIN GENERATOR - Check for windings and electrical connections',
            'status_field' => 'main_gen_windings_status',
            'problem_field' => 'main_gen_windings_problem',
            'corrective_field' => 'main_gen_windings_corrective_action',
        ],
        [
            'status_label' => 'SWITCH GEAR - Check power distribution wiring and connections',
            'status_field' => 'switch_gear_status',
            'problem_field' => 'switch_gear_problem',
            'corrective_field' => 'switch_gear_corrective_action',
        ],
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
        $this->csvNames = [];
        $this->userMapping = [];
        $this->unmatchedCount = 0;

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

        $header = array_map(fn ($h) => trim(preg_replace('/^\x{FEFF}/u', '', $h)), $header);

        $users = User::all();
        $userNameMap = [];
        foreach ($users as $user) {
            $fullName = strtolower(trim($user->first_name . ' ' . $user->last_name));
            $userNameMap[$fullName] = $user->id;
        }

        // Load gensets for mapping
        $genSets = DB::table('get-sets')->get();

        $rowNum = 1;
        $allNames = [];
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            if (count(array_filter($row)) === 0) {
                continue;
            }

            // Col 16 = Maintenance Technician, 17 = Date Submitted, 18 = Diesel Generator from Form
            $technician = trim($row[16] ?? '');
            $dateSubmitted = trim($row[17] ?? '');
            $genSetFromForm = trim($row[18] ?? '');
            $notes = $row[11] ?? ''; // Notes column

            if (empty($technician)) {
                continue;
            }

            $allNames[$technician] = true;

            $parsedDate = $this->parseDate($dateSubmitted);
            if (! $parsedDate) {
                $this->parseErrors[] = "Row {$rowNum}: Could not parse date '{$dateSubmitted}'";
                continue;
            }

            // Parse genset number from "1 (375kVA Cummins Genset)" or "2 (275kVA Camda Genset)"
            $genSetId = $this->parseGenSetId($genSetFromForm, $genSets);

            // Parse all form fields from Notes
            $formFields = $this->parseNotesFields($notes);
            $formFields['gen_set_from_csv'] = $genSetFromForm;

            $rows[] = [
                'csv_row' => $rowNum,
                'technician' => $technician,
                'date_submitted' => $parsedDate,
                'gen_set_id' => $genSetId,
                'gen_set_label' => $genSetFromForm,
                'form_fields' => $formFields,
            ];
        }

        fclose($handle);

        $this->cacheKey = 'import_diesel_gen_' . auth()->id() . '_' . now()->timestamp;
        Cache::put($this->cacheKey, $rows, now()->addHour());

        $this->previewRows = array_slice($rows, 0, 5);
        $this->totalCsvRows = count($rows);
        $this->totalFormRecords = count($rows); // 1:1 mapping

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
        $nameToUser = [];
        foreach ($this->csvNames as $index => $name) {
            $userId = $this->userMapping[$index] ?? '';
            if (empty($userId)) {
                $this->dispatch('showToast', message: "Please map all technicians to users before importing. '{$name}' is unmapped.", type: 'error');
                return;
            }
            $nameToUser[$name] = $userId;
        }

        $parsedRows = Cache::get($this->cacheKey);
        if (! $parsedRows) {
            $this->importErrors[] = 'Parsed data expired. Please re-upload the CSV file.';
            $this->step = 3;
            return;
        }

        $formTypeId = DB::table('form_types')
            ->where('form_name', 'Hatchery Diesel Generator Weekly Maintenance Checklist')
            ->value('id');

        if (! $formTypeId) {
            $this->importErrors[] = 'Form type "Hatchery Diesel Generator Weekly Maintenance Checklist" not found in database.';
            $this->step = 3;
            return;
        }

        $genSets = DB::table('get-sets')->get()->keyBy('id');

        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->importErrors = [];

        DB::beginTransaction();
        try {
            $batch = [];

            foreach ($parsedRows as $row) {
                $userId = $nameToUser[$row['technician']] ?? null;
                if (! $userId) {
                    $this->skippedCount++;
                    continue;
                }

                $genSetId = $row['gen_set_id'];
                $genSet = $genSetId ? $genSets->get($genSetId) : null;

                $formInputs = $row['form_fields'];
                $formInputs['technician_id'] = $userId;
                $formInputs['gen_set_number'] = $genSetId ?: '';

                if ($genSet) {
                    $formInputs['machine_info'] = [
                        'table' => 'get-sets',
                        'id' => $genSetId,
                        'name' => $genSet->getSetName,
                    ];
                    $formInputs['maintenance_personnel_name'] = $row['technician'];
                }

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

            if (! empty($batch)) {
                DB::table('forms')->insert($batch);
                $this->importedCount += count($batch);
            }

            DB::commit();
            Cache::forget($this->cacheKey);

            ActivityLogger::log(
                'import',
                "Imported {$this->importedCount} Diesel Generator Weekly form records from CSV ({$this->totalCsvRows} CSV rows)",
                module: 'Form',
                properties: [
                    'form_type' => 'Hatchery Diesel Generator Weekly Maintenance Checklist',
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

    protected function parseNotesFields(string $notes): array
    {
        $fields = [];
        $lines = explode("\n", $notes);

        // Build label→value map: label ends with ":" or "::", value is the next non-empty line
        $labelValues = [];
        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (preg_match('/^(.+?):+\s*$/', $line, $m)) {
                $label = trim($m[1]);
                // Get next non-empty line as value
                $value = '';
                for ($j = $i + 1; $j < count($lines); $j++) {
                    $nextLine = trim($lines[$j]);
                    if ($nextLine !== '') {
                        // Stop if this line is itself a label
                        if (preg_match('/^.+?:+\s*$/', $nextLine)) {
                            break;
                        }
                        $value = $nextLine;
                        break;
                    }
                }
                $labelValues[] = ['label' => $label, 'value' => $value];
            }
        }

        // Map inspection sections using ordered parsing
        $lvIndex = 0;
        foreach (self::SECTION_MAP as $section) {
            // Find the status label
            $statusVal = $this->findLabelValue($labelValues, $section['status_label'], $lvIndex);
            $fields[$section['status_field']] = $this->normalizeStatus($statusVal);

            // The next "If not okay" label is the problem
            $problemVal = $this->findNextLabelContaining($labelValues, 'If not okay', $lvIndex);
            $fields[$section['problem_field']] = $problemVal ?: 'N/A';

            // The next "Corrective Action" label
            $correctiveVal = $this->findNextLabelContaining($labelValues, 'Corrective Action', $lvIndex);
            $fields[$section['corrective_field']] = $correctiveVal ?: 'N/A';
        }

        // Test run section
        $fields['test_run_conducted'] = $this->normalizeStatus(
            $this->findLabelValueAnywhere($labelValues, 'Actual test run')
        );
        $fields['test_run_time'] = $this->findLabelValueAnywhere($labelValues, 'time start and end time') ?: '';
        $fields['previous_running_time'] = $this->findLabelValueAnywhere($labelValues, 'Previous reading of running time') ?: '';
        $fields['present_running_time'] = $this->findLabelValueAnywhere($labelValues, 'Present reading of running time') ?: '';
        $fields['line_voltages'] = $this->findLabelValueAnywhere($labelValues, 'line voltages') ?: '';
        $fields['line_amperes'] = $this->findLabelValueAnywhere($labelValues, 'line amperes') ?: '';
        $fields['hertz_reading'] = $this->findLabelValueAnywhere($labelValues, 'hertz') ?: '';
        $fields['oil_pressure_kpa'] = $this->findLabelValueAnywhere($labelValues, 'oil pressure') ?: '';
        $fields['oil_temperature_f'] = $this->findLabelValueAnywhere($labelValues, 'oil temperature') ?: '';
        $fields['running_condition'] = $this->findLabelValueAnywhere($labelValues, 'running condition') ?: '';
        $fields['notes'] = $this->findLabelValueAnywhere($labelValues, 'Notes and other concerns') ?: 'N/A';
        $fields['diesel_tank_level'] = $this->findLabelValueAnywhere($labelValues, 'Diesel Tank Level') ?: '';
        $fields['refill_date'] = $this->findLabelValueAnywhere($labelValues, 'refill') ?: '';
        $fields['available_diesel_stock'] = $this->findLabelValueAnywhere($labelValues, 'Available Diesel Stock') ?: '';

        return $fields;
    }

    protected function findLabelValue(array $labelValues, string $searchLabel, int &$startIndex): string
    {
        $searchLower = strtolower($searchLabel);
        for ($i = $startIndex; $i < count($labelValues); $i++) {
            if (strtolower($labelValues[$i]['label']) === $searchLower) {
                $startIndex = $i + 1;
                return $labelValues[$i]['value'];
            }
        }
        return '';
    }

    protected function findNextLabelContaining(array $labelValues, string $needle, int &$startIndex): string
    {
        $needleLower = strtolower($needle);
        for ($i = $startIndex; $i < count($labelValues); $i++) {
            if (str_contains(strtolower($labelValues[$i]['label']), $needleLower)) {
                $startIndex = $i + 1;
                return $labelValues[$i]['value'];
            }
        }
        return '';
    }

    protected function findLabelValueAnywhere(array $labelValues, string $needle): string
    {
        $needleLower = strtolower($needle);
        foreach ($labelValues as $lv) {
            if (str_contains(strtolower($lv['label']), $needleLower)) {
                return $lv['value'];
            }
        }
        return '';
    }

    protected function normalizeStatus(string $value): string
    {
        $upper = strtoupper(trim($value));
        if (str_starts_with($upper, 'OKAY') || str_starts_with($upper, 'OK')) {
            return 'Okay';
        }
        if (str_starts_with($upper, 'NOT OKAY') || str_starts_with($upper, 'NOT OK')) {
            return 'Not Okay';
        }
        return $value ?: 'N/A';
    }

    protected function parseGenSetId(string $genSetFromForm, $genSets): ?int
    {
        // Extract number from "1 (375kVA Cummins Genset)" or "2 (275kVA Camda Genset)"
        if (preg_match('/^(\d+)/', $genSetFromForm, $m)) {
            $num = (int) $m[1];
            foreach ($genSets as $gs) {
                if ($gs->id === $num) {
                    return $num;
                }
            }
        }
        return null;
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

    public function render()
    {
        return view('livewire.admin.import-forms.diesel-generator-weekly');
    }
}
