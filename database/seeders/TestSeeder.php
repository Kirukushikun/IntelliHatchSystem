<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Incubator;
use App\Models\Hatcher;
use App\Models\Plenum;
use App\Models\PsNumber;
use App\Models\HouseNumber;
use App\Models\Form;
use App\Models\FormType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        echo "Creating test data...\n";

        $incubators = Incubator::all()->all();
        $hatchers   = Hatcher::all()->all();

        // NOTE: user_type 2 = Hatchery User (after superadmin migration shifted types)
        $hatcheryUsers = User::where('user_type', 2)->get();

        if ($hatcheryUsers->isEmpty()) {
            echo "No hatchery users found. Please run HatcheryUserSeeder first.\n";
            return;
        }

        // Seed supporting registries if empty
        $this->seedPsAndHouseNumbers();
        $this->seedGetSets();

        $psNumbers    = PsNumber::where('isActive', true)->get()->all();
        $houseNumbers = HouseNumber::where('isActive', true)->get()->all();

        // Get all form type IDs keyed by name
        $formTypeIds = FormType::pluck('id', 'form_name')->all();

        // ── Form Type 1: Incubator Routine Checklist Per Shift ──────────────
        $this->seedIncubatorRoutine($incubators, $hatcheryUsers, $formTypeIds);

        // ── Form Type 2: Hatcher Blower Air Speed Monitoring ────────────────
        $this->seedHatcherBlowerAir($hatchers, $hatcheryUsers, $formTypeIds);

        // ── Form Type 3: Incubator Blower Air Speed Monitoring ──────────────
        $this->seedIncubatorBlowerAir($incubators, $hatcheryUsers, $formTypeIds);

        // ── Form Type 4: Hatchery Sullair Air Compressor Weekly PMS ─────────
        $this->seedHatcherySullair($hatcheryUsers, $formTypeIds);

        // ── Form Type 5: Hatcher Machine Accuracy Temperature Checking ───────
        $this->seedHatcherMachineAccuracy($hatchers, $hatcheryUsers, $formTypeIds);

        // ── Form Type 6: Plenum Temperature and Humidity Monitoring ──────────
        $this->seedPlenumTempHumidity($incubators, $hatchers, $hatcheryUsers, $formTypeIds);

        // ── Form Type 7: Incubator Machine Accuracy Temperature Checking ─────
        $this->seedIncubatorMachineAccuracy($incubators, $hatcheryUsers, $formTypeIds);

        // ── Form Type 8: Entrance Damper Spacing Monitoring ──────────────────
        $this->seedEntranceDamperSpacing($incubators, $hatcheryUsers, $formTypeIds);

        // ── Form Type 9: Incubator Entrance Temperature Monitoring ───────────
        $this->seedIncubatorEntranceTemp($incubators, $hatcheryUsers, $formTypeIds);

        // ── Form Type 10: Incubator Temperature Calibration ──────────────────
        $this->seedIncubatorTempCalibration($incubators, $hatcheryUsers, $formTypeIds);

        // ── Form Type 11: Hatcher Temperature Calibration ────────────────────
        $this->seedHatcherTempCalibration($hatchers, $hatcheryUsers, $formTypeIds);

        // ── Form Type 12: PASGAR Score ────────────────────────────────────────
        $this->seedPasgarScore($incubators, $hatchers, $psNumbers, $houseNumbers, $hatcheryUsers, $formTypeIds);

        // ── Form Type 13: Incubator Rack Preventive Maintenance Checklist ─────
        $this->seedIncubatorRackPm($incubators, $hatcheryUsers, $formTypeIds);

        // ── Form Type 14: Weekly Voltage and Ampere Monitoring ───────────────
        $this->seedWeeklyVoltAmpere($hatcheryUsers, $formTypeIds);

        // ── Form Type 15: Hatchery Diesel Generator Weekly Maintenance ────────
        $this->seedDieselGeneratorWeekly($hatcheryUsers, $formTypeIds);

        echo "Test data creation completed!\n";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Supporting registry seeders
    // ─────────────────────────────────────────────────────────────────────────

    private function seedPsAndHouseNumbers(): void
    {
        if (PsNumber::count() === 0) {
            echo "Seeding PS numbers...\n";
            for ($i = 1; $i <= 10; $i++) {
                PsNumber::create([
                    'psNumber'     => 'PS-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'isActive'     => true,
                    'creationDate' => Carbon::now()->subDays(rand(90, 180)),
                ]);
            }
        }

        if (HouseNumber::count() === 0) {
            echo "Seeding house numbers...\n";
            for ($i = 1; $i <= 10; $i++) {
                HouseNumber::create([
                    'houseNumber'  => 'H-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'isActive'     => true,
                    'creationDate' => Carbon::now()->subDays(rand(90, 180)),
                ]);
            }
        }
    }

    private function seedGetSets(): void
    {
        if (DB::table('get-sets')->count() === 0) {
            echo "Seeding diesel generator sets...\n";
            DB::table('get-sets')->insert([
                ['getSetName' => 'Gen-Set 1 (Main Building)', 'isActive' => true, 'created_at' => now(), 'updated_at' => now()],
                ['getSetName' => 'Gen-Set 2 (Hatchery Area)',  'isActive' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Form type seeders
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Form 1: Incubator Routine Checklist Per Shift
     * Per incubator, per shift, per day — 3 shifts × ~8 incubators × 90 days
     */
    private function seedIncubatorRoutine(array $incubators, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Incubator Routine Checklist Per Shift'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Incubator Routine form type not found.\n";
            return;
        }

        echo "Creating incubator routine checklist forms...\n";

        $schedule = \App\Livewire\Configs\IncubatorRoutineConfig::schedule();
        $shifts   = ['1st Shift', '2nd Shift', '3rd Shift'];
        $count    = 0;

        for ($day = 0; $day < 90; $day++) {
            $date    = Carbon::now()->subDays($day);
            $dayName = $date->format('l');

            foreach ($shifts as $shift) {
                $tasks = $schedule['_daily'];
                $key   = "{$dayName}-{$shift}";
                if (isset($schedule[$key])) {
                    $tasks = array_merge($tasks, $schedule[$key]);
                }

                foreach ($incubators as $incubator) {
                    if (rand(1, 100) > 85) continue; // 85% logging rate

                    $user    = $hatcheryUsers->random();
                    $alarmOk = rand(1, 10) > 1; // 90% operational

                    $inputs = [
                        'shift'     => $shift,
                        'incubator' => $incubator->id,
                        'machine_info' => [
                            'table' => 'incubator-machines',
                            'id'    => $incubator->id,
                            'name'  => $incubator->incubatorName,
                        ],
                        'hatchery_man'           => $user->id,
                        'alarm_system_condition' => $alarmOk ? 'Operational' : 'Unoperational',
                        'corrective_action'      => $alarmOk
                            ? 'No corrective action needed'
                            : fake()->randomElement([
                                'Alarm wiring checked and repaired',
                                'Sensor replaced and tested',
                                'Reported to maintenance team for repair',
                            ]),
                    ];

                    foreach ($tasks as $task) {
                        if (in_array($task, ['shift', 'hatchery_man', 'incubator', 'alarm_system_condition', 'corrective_action'])) {
                            continue;
                        }
                        $inputs[$task] = rand(1, 100) <= 92 ? 'Done' : 'Pending';
                    }

                    Form::create([
                        'form_type_id'   => $formTypeId,
                        'form_inputs'    => $inputs,
                        'date_submitted' => $date,
                        'uploaded_by'    => $user->id,
                    ]);
                    $count++;
                }
            }
        }

        echo "Created {$count} incubator routine forms.\n";
    }

    /**
     * Form 2: Hatcher Blower Air Speed Monitoring
     * Per hatcher, daily — 10 hatchers × 90 days
     */
    private function seedHatcherBlowerAir(array $hatchers, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Hatcher Blower Air Speed Monitoring'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Hatcher Blower Air Speed form type not found.\n";
            return;
        }

        echo "Creating hatcher blower air speed forms...\n";

        $actions = [
            'No action needed', 'Cleaned fan blades', 'Adjusted fan speed',
            'Scheduled maintenance', 'Replaced fan motor',
        ];
        $count = 0;

        for ($day = 0; $day < 90; $day++) {
            $date = Carbon::now()->subDays($day);
            foreach ($hatchers as $hatcher) {
                if (rand(1, 100) > 85) continue;

                $user    = $hatcheryUsers->random();
                $baseCfm = rand(380, 480);
                $lines   = [];
                for ($fan = 1; $fan <= 4; $fan++) {
                    $cfm    = $baseCfm + round((rand(-25, 25) / 1000) * $baseCfm);
                    $lines[] = "Fan {$fan} - {$cfm} cfm";
                }

                Form::create([
                    'form_type_id' => $formTypeId,
                    'form_inputs'  => [
                        'machine_info' => [
                            'table' => 'hatcher-machines',
                            'id'    => $hatcher->id,
                            'name'  => $hatcher->hatcherName,
                        ],
                        'hatchery_man'         => $user->id,
                        'hatcher'              => $hatcher->id,
                        'cfm_fan_reading'      => implode("\n", $lines),
                        'cfm_fan_action_taken' => $actions[array_rand($actions)],
                    ],
                    'date_submitted' => $date,
                    'uploaded_by'    => $user->id,
                ]);
                $count++;
            }
        }

        echo "Created {$count} hatcher blower air speed forms.\n";
    }

    /**
     * Form 3: Incubator Blower Air Speed Monitoring
     * Per incubator, daily — 10 incubators × 90 days
     */
    private function seedIncubatorBlowerAir(array $incubators, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Incubator Blower Air Speed Monitoring'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Incubator Blower Air Speed form type not found.\n";
            return;
        }

        echo "Creating incubator blower air speed forms...\n";

        $actions = [
            'No action needed', 'Cleaned fan blades', 'Adjusted fan speed',
            'Scheduled maintenance', 'Replaced fan motor',
        ];
        $count = 0;

        for ($day = 0; $day < 90; $day++) {
            $date = Carbon::now()->subDays($day);
            foreach ($incubators as $incubator) {
                if (rand(1, 100) > 85) continue;

                $user    = $hatcheryUsers->random();
                $baseCfm = rand(350, 460);
                $lines   = [];
                for ($fan = 1; $fan <= 4; $fan++) {
                    $cfm    = $baseCfm + round((rand(-25, 25) / 1000) * $baseCfm);
                    $lines[] = "Fan {$fan} - {$cfm} cfm";
                }

                Form::create([
                    'form_type_id' => $formTypeId,
                    'form_inputs'  => [
                        'machine_info' => [
                            'table' => 'incubator-machines',
                            'id'    => $incubator->id,
                            'name'  => $incubator->incubatorName,
                        ],
                        'hatchery_man'         => $user->id,
                        'incubator'            => $incubator->id,
                        'cfm_fan_reading'      => implode("\n", $lines),
                        'cfm_fan_action_taken' => $actions[array_rand($actions)],
                    ],
                    'date_submitted' => $date,
                    'uploaded_by'    => $user->id,
                ]);
                $count++;
            }
        }

        echo "Created {$count} incubator blower air speed forms.\n";
    }

    /**
     * Form 4: Hatchery Sullair Air Compressor Weekly PMS Checklist
     * 2 sullair units × ~13 weeks = ~26 records
     */
    private function seedHatcherySullair($hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Hatchery Sullair Air Compressor Weekly PMS Checklist'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Hatchery Sullair form type not found.\n";
            return;
        }

        echo "Creating hatchery sullair forms...\n";

        $sullairOptions    = ['Sullair 1 (Inside Incubation Area)', 'Sullair 2 (Maintenance Area)'];
        $componentStatuses = ['Good Condition', 'With Minimal Damage', 'For Replacement'];
        $hoseStatuses      = ['No Leak', 'With Leak for Repair', 'With Leak for Replacement'];
        $oilStatuses       = ['Above or On Its Level Requirement', 'For Refill'];
        $beltStatuses      = ['Good Condition', 'For Replacement'];
        $pipeStatuses      = ['No Any Leak', 'With Leak For Repair or Replacement'];
        $dryerStatuses     = ['Clean and Good Status', 'For Repair and Replacement'];
        $count             = 0;

        for ($week = 0; $week < 13; $week++) {
            $date = Carbon::now()->subDays($week * 7);
            foreach ($sullairOptions as $sullair) {
                $user   = $hatcheryUsers->random();
                $phone  = '09' . str_pad((string) rand(0, 999999999), 9, '0', STR_PAD_LEFT);

                Form::create([
                    'form_type_id' => $formTypeId,
                    'form_inputs'  => [
                        'hatchery_man'     => $user->id,
                        'cellphone_number' => $phone,
                        'sullair_number'   => $sullair,

                        'actual_psi_reading'         => rand(70, 88),
                        'actual_temperature_reading' => rand(155, 195),
                        'actual_volt_reading'        => rand(218, 232) . ' V',
                        'actual_ampere_reading'      => round(rand(90, 140) / 10, 1) . ' A',

                        'status_wiring_lugs_control' => fake()->randomElement($componentStatuses),
                        'status_solenoid_valve'      => fake()->randomElement($componentStatuses),
                        'status_fan_motor'           => fake()->randomElement($componentStatuses),

                        'status_hose'             => fake()->randomElement($hoseStatuses),
                        'actual_oil_level_status' => fake()->randomElement($oilStatuses),
                        'tension_belt_status'     => fake()->randomElement($beltStatuses),

                        'status_water_filter' => rand(1, 10) > 2 ? 'Good Condition' : 'For Replacement',
                        'air_pipe_status'     => fake()->randomElement($pipeStatuses),
                        'air_dryer_status'    => fake()->randomElement($dryerStatuses),
                        'inspected_by'        => $user->first_name . ' ' . $user->last_name,
                    ],
                    'date_submitted' => $date,
                    'uploaded_by'    => $user->id,
                ]);
                $count++;
            }
        }

        echo "Created {$count} hatchery sullair forms.\n";
    }

    /**
     * Form 5: Hatcher Machine Accuracy Temperature Checking
     * Per hatcher, per shift, daily — 3 shifts × ~8 hatchers × 90 days
     */
    private function seedHatcherMachineAccuracy(array $hatchers, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Hatcher Machine Accuracy Temperature Checking'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Hatcher Machine Accuracy form type not found.\n";
            return;
        }

        echo "Creating hatcher machine accuracy forms...\n";

        $shifts     = ['1st Shift', '2nd Shift', '3rd Shift'];
        $shiftTimes = ['1st Shift' => '06:00', '2nd Shift' => '14:00', '3rd Shift' => '22:00'];
        $count      = 0;

        for ($day = 0; $day < 90; $day++) {
            $date = Carbon::now()->subDays($day);
            foreach ($shifts as $shift) {
                foreach ($hatchers as $hatcher) {
                    if (rand(1, 100) > 80) continue;

                    $user          = $hatcheryUsers->random();
                    $displayTemp   = round(rand(978, 995) / 10, 1); // 97.8–99.5°F
                    $calibrator    = round($displayTemp + (rand(-5, 5) / 10), 1);
                    $wetBulb       = round(rand(840, 870) / 10, 1); // 84–87°F
                    $dryBulb       = round(rand(980, 1000) / 10, 1); // 98–100°F
                    $phone         = '09' . str_pad((string) rand(0, 999999999), 9, '0', STR_PAD_LEFT);

                    Form::create([
                        'form_type_id' => $formTypeId,
                        'form_inputs'  => [
                            'machine_info' => [
                                'table' => 'hatcher-machines',
                                'id'    => $hatcher->id,
                                'name'  => $hatcher->hatcherName,
                            ],
                            'hatchery_man'    => $user->id,
                            'cellphone_number' => $phone,
                            'date_submitted'  => $date->format('Y-m-d'),
                            'time_of_reading' => $shiftTimes[$shift],
                            'shift'           => $shift,
                            'hatcher'         => $hatcher->id,
                            'display_temp'    => $displayTemp,
                            'calibrator'      => $calibrator,
                            'wet_bulb'        => $wetBulb,
                            'dry_bulb'        => $dryBulb,
                        ],
                        'date_submitted' => $date,
                        'uploaded_by'    => $user->id,
                    ]);
                    $count++;
                }
            }
        }

        echo "Created {$count} hatcher machine accuracy forms.\n";
    }

    /**
     * Form 6: Plenum Temperature and Humidity Monitoring
     * 5 readings per day (6 AM, 11 AM, 4 PM, 9 PM, 2 AM) — 90 days
     */
    private function seedPlenumTempHumidity(array $incubators, array $hatchers, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Plenum Temperature and Humidity Monitoring'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Plenum Temperature and Humidity form type not found.\n";
            return;
        }

        echo "Creating plenum temperature and humidity forms...\n";

        $times       = ['6:00 AM', '11:00 AM', '4:00 PM', '9:00 PM', '2:00 AM'];
        $timeShifts  = [
            '6:00 AM' => '1st Shift', '11:00 AM' => '1st Shift',
            '4:00 PM' => '2nd Shift', '9:00 PM'  => '2nd Shift',
            '2:00 AM' => '3rd Shift',
        ];
        $weatherOpts = ['Sunny', 'Cloudy', 'Rainy', 'Partly Cloudy'];
        $count       = 0;

        for ($day = 0; $day < 90; $day++) {
            $date = Carbon::now()->subDays($day);
            foreach ($times as $time) {
                if (rand(1, 100) > 88) continue;

                $user  = $hatcheryUsers->random();
                $shift = $timeShifts[$time];

                // 3–5 incubators sampled per reading
                $shuffled  = $incubators;
                shuffle($shuffled);
                $sampleInc = array_slice($shuffled, 0, rand(3, min(5, count($incubators))));

                $incubatorReadings = [];
                foreach ($sampleInc as $inc) {
                    $incubatorReadings[] = [
                        'incubator_id' => $inc->id,
                        'temperature'  => round(rand(995, 1005) / 10, 1), // ~99.5–100.5°F
                        'humidity'     => round(rand(580, 680) / 10, 1),  // 58–68% RH
                    ];
                }

                // 2–4 hatchers sampled per reading
                $shuffledH  = $hatchers;
                shuffle($shuffledH);
                $sampleHat  = array_slice($shuffledH, 0, rand(2, min(4, count($hatchers))));

                $hatcherReadings = [];
                foreach ($sampleHat as $hat) {
                    $hatcherReadings[] = [
                        'hatcher_id'  => $hat->id,
                        'temperature' => round(rand(980, 995) / 10, 1), // ~98–99.5°F
                        'humidity'    => round(rand(700, 820) / 10, 1), // 70–82% RH
                    ];
                }

                Form::create([
                    'form_type_id' => $formTypeId,
                    'form_inputs'  => [
                        'hatcheryman'  => $user->id,
                        'date'         => $date->format('Y-m-d'),
                        'shift'        => $shift,
                        'time'         => $time,

                        'incubator_readings' => $incubatorReadings,
                        'hatcher_readings'   => $hatcherReadings,

                        'random_humidity_entrance_dumper'  => round(rand(600, 720) / 10, 1),
                        'random_humidity_top_baggy'        => round(rand(580, 680) / 10, 1),
                        'random_entrance_dumper_incubator' => round(rand(450, 550) / 10, 1),

                        'aircon_count'               => (string) rand(0, 4),
                        'humidity_incubator_hallway' => round(rand(550, 680) / 10, 1),
                        'humidity_outside'           => round(rand(600, 850) / 10, 1),
                        'weather_condition'          => $weatherOpts[array_rand($weatherOpts)],
                    ],
                    'date_submitted' => $date,
                    'uploaded_by'    => $user->id,
                ]);
                $count++;
            }
        }

        echo "Created {$count} plenum temperature and humidity forms.\n";
    }

    /**
     * Form 7: Incubator Machine Accuracy Temperature Checking
     * Per incubator, per shift, daily — 3 shifts × ~8 incubators × 90 days
     */
    private function seedIncubatorMachineAccuracy(array $incubators, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Incubator Machine Accuracy Temperature Checking'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Incubator Machine Accuracy form type not found.\n";
            return;
        }

        echo "Creating incubator machine accuracy forms...\n";

        $shifts     = ['1st Shift', '2nd Shift', '3rd Shift'];
        $shiftTimes = ['1st Shift' => '06:00', '2nd Shift' => '14:00', '3rd Shift' => '22:00'];
        $count      = 0;

        for ($day = 0; $day < 90; $day++) {
            $date = Carbon::now()->subDays($day);
            foreach ($shifts as $shift) {
                foreach ($incubators as $incubator) {
                    if (rand(1, 100) > 80) continue;

                    $user        = $hatcheryUsers->random();
                    $displayTemp = round(rand(995, 1005) / 10, 1); // 99.5–100.5°F
                    $calibrator  = round($displayTemp + (rand(-4, 4) / 10), 1);
                    $phone       = '09' . str_pad((string) rand(0, 999999999), 9, '0', STR_PAD_LEFT);

                    Form::create([
                        'form_type_id' => $formTypeId,
                        'form_inputs'  => [
                            'machine_info' => [
                                'table' => 'incubator-machines',
                                'id'    => $incubator->id,
                                'name'  => $incubator->incubatorName,
                            ],
                            'hatchery_man'    => $user->id,
                            'mobile_number'   => $phone,
                            'date_submitted'  => $date->format('Y-m-d'),
                            'time_of_reading' => $shiftTimes[$shift],
                            'shift'           => $shift,
                            'incubator'       => $incubator->id,
                            'display_temp'    => $displayTemp,
                            'calibrator'      => $calibrator,
                        ],
                        'date_submitted' => $date,
                        'uploaded_by'    => $user->id,
                    ]);
                    $count++;
                }
            }
        }

        echo "Created {$count} incubator machine accuracy forms.\n";
    }

    /**
     * Form 8: Entrance Damper Spacing Monitoring
     * Per incubator, per shift, daily
     */
    private function seedEntranceDamperSpacing(array $incubators, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Entrance Damper Spacing Monitoring'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Entrance Damper Spacing form type not found.\n";
            return;
        }

        echo "Creating entrance damper spacing forms...\n";

        $shifts     = ['1st Shift', '2nd Shift', '3rd Shift'];
        $shiftTimes = ['1st Shift' => '07:00', '2nd Shift' => '15:00', '3rd Shift' => '23:00'];
        $count      = 0;

        for ($day = 0; $day < 90; $day++) {
            $date = Carbon::now()->subDays($day);
            foreach ($shifts as $shift) {
                foreach ($incubators as $incubator) {
                    if (rand(1, 100) > 78) continue;

                    $user        = $hatcheryUsers->random();
                    // Damper spacing: 2.5–8.0 inches; normal range 3–6 inches
                    $measurement = round(rand(25, 80) / 10, 1);

                    Form::create([
                        'form_type_id' => $formTypeId,
                        'form_inputs'  => [
                            'machine_info' => [
                                'table' => 'incubator-machines',
                                'id'    => $incubator->id,
                                'name'  => $incubator->incubatorName,
                            ],
                            'hatchery_man'    => $user->id,
                            'shift'           => $shift,
                            'time_of_reading' => $shiftTimes[$shift],
                            'incubator'       => $incubator->id,
                            'measurement'     => $measurement,
                        ],
                        'date_submitted' => $date,
                        'uploaded_by'    => $user->id,
                    ]);
                    $count++;
                }
            }
        }

        echo "Created {$count} entrance damper spacing forms.\n";
    }

    /**
     * Form 9: Incubator Entrance Temperature Monitoring
     * Per incubator, 1–2 checks daily
     */
    private function seedIncubatorEntranceTemp(array $incubators, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Incubator Entrance Temperature Monitoring'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Incubator Entrance Temperature form type not found.\n";
            return;
        }

        echo "Creating incubator entrance temperature forms...\n";

        $incubationDays  = ['Day 1 to 10', 'Day 10 to 12', 'Day 12 to 14', 'Day 14 to 18'];
        $checkTimes      = ['07:00', '10:00', '13:00', '16:00', '19:00'];
        $finishOffsets   = [30, 30, 30, 30, 30]; // minutes after start
        $adjustmentNotes = [
            'Temperature within acceptable range, no adjustment needed',
            'Adjusted set point +0.2°F to compensate for entrance temp drop',
            'Curtain position corrected to improve temperature retention',
            'Baggy repositioned for better sealing',
            'Minor adjustment made; temperature stabilized after 15 minutes',
        ];
        $count = 0;

        for ($day = 0; $day < 90; $day++) {
            $date = Carbon::now()->subDays($day);
            foreach ($incubators as $incubator) {
                $checks     = rand(1, 2);
                $usedSlots  = [];
                for ($c = 0; $c < $checks; $c++) {
                    if (rand(1, 100) > 85) continue;

                    // Pick an unused time slot
                    do {
                        $ti = array_rand($checkTimes);
                    } while (in_array($ti, $usedSlots) && count($usedSlots) < count($checkTimes));
                    $usedSlots[] = $ti;

                    $user          = $hatcheryUsers->random();
                    $setTemp       = rand(0, 1) ? '99.5°F' : '100.0°F';
                    $setHumid      = rand(0, 1) ? '65%' : '68%';
                    $entranceLeft  = round(rand(990, 1010) / 10, 1);
                    $entranceRight = round(rand(990, 1010) / 10, 1);
                    $baggyLeft     = round(rand(985, 1005) / 10, 1);
                    $baggyRight    = round(rand(985, 1005) / 10, 1);
                    [$sh, $sm]     = explode(':', $checkTimes[$ti]);
                    $fh            = (int) $sh;
                    $fm            = (int) $sm + $finishOffsets[$ti];
                    if ($fm >= 60) { $fh++; $fm -= 60; }

                    Form::create([
                        'form_type_id' => $formTypeId,
                        'form_inputs'  => [
                            'machine_info' => [
                                'table' => 'incubator-machines',
                                'id'    => $incubator->id,
                                'name'  => $incubator->incubatorName,
                            ],
                            'hatchery_man'         => $user->id,
                            'time_of_check'        => $checkTimes[$ti],
                            'days_of_incubation'   => $incubationDays[array_rand($incubationDays)],
                            'incubator'            => $incubator->id,
                            'set_point_temp'       => $setTemp,
                            'set_point_humidity'   => $setHumid,
                            'entrance_temp'        => "Left: {$entranceLeft}°F / Right: {$entranceRight}°F",
                            'baggy'                => "Left: {$baggyLeft}°F / Right: {$baggyRight}°F",
                            'temp_adjustment_notes' => $adjustmentNotes[array_rand($adjustmentNotes)],
                            'time_finished'        => sprintf('%02d:%02d', $fh, $fm),
                        ],
                        'date_submitted' => $date,
                        'uploaded_by'    => $user->id,
                    ]);
                    $count++;
                }
            }
        }

        echo "Created {$count} incubator entrance temperature forms.\n";
    }

    /**
     * Form 10: Incubator Temperature Calibration
     * Per incubator, once daily (rotating shifts)
     */
    private function seedIncubatorTempCalibration(array $incubators, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Incubator Temperature Calibration'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Incubator Temperature Calibration form type not found.\n";
            return;
        }

        echo "Creating incubator temperature calibration forms...\n";

        $shifts    = ['1st Shift', '2nd Shift', '3rd Shift'];
        $approvers = ['Jeff Montiano', 'Iverson Guno', 'Senior Technician'];
        $count     = 0;

        for ($day = 0; $day < 90; $day++) {
            $date  = Carbon::now()->subDays($day);
            $shift = $shifts[$day % 3];

            foreach ($incubators as $incubator) {
                if (rand(1, 100) > 82) continue;

                $user           = $hatcheryUsers->random();
                $machineTemp    = round(rand(995, 1005) / 10, 1); // 99.5–100.5°F
                $calibratorTemp = round($machineTemp + (rand(-3, 3) / 10), 1);
                $humidity       = round(rand(580, 680) / 10, 1);  // 58–68%
                $startH         = $shift === '1st Shift' ? rand(6, 8) : ($shift === '2nd Shift' ? rand(14, 16) : rand(22, 23));
                $startM         = rand(0, 59);
                $endH           = $startH;
                $endM           = $startM + rand(15, 45);
                if ($endM >= 60) { $endH++; $endM -= 60; }

                Form::create([
                    'form_type_id' => $formTypeId,
                    'form_inputs'  => [
                        'machine_info' => [
                            'table' => 'incubator-machines',
                            'id'    => $incubator->id,
                            'name'  => $incubator->incubatorName,
                        ],
                        'hatchery_man'     => $user->id,
                        'shift'            => $shift,
                        'time_started'     => sprintf('%02d:%02d', $startH, $startM),
                        'incubator'        => $incubator->id,
                        'machine_temp'     => $machineTemp,
                        'calibrator_temp'  => $calibratorTemp,
                        'humidity_reading' => $humidity,
                        'approver'         => $approvers[array_rand($approvers)],
                        'time_finished'    => sprintf('%02d:%02d', $endH, $endM),
                    ],
                    'date_submitted' => $date,
                    'uploaded_by'    => $user->id,
                ]);
                $count++;
            }
        }

        echo "Created {$count} incubator temperature calibration forms.\n";
    }

    /**
     * Form 11: Hatcher Temperature Calibration
     * Per hatcher, once daily (rotating shifts)
     */
    private function seedHatcherTempCalibration(array $hatchers, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Hatcher Temperature Calibration'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Hatcher Temperature Calibration form type not found.\n";
            return;
        }

        echo "Creating hatcher temperature calibration forms...\n";

        $shifts    = ['1st Shift', '2nd Shift', '3rd Shift'];
        $approvers = ['Jeff Montiano', 'Iverson Guno', 'Senior Technician'];
        $count     = 0;

        for ($day = 0; $day < 90; $day++) {
            $date  = Carbon::now()->subDays($day);
            $shift = $shifts[$day % 3];

            foreach ($hatchers as $hatcher) {
                if (rand(1, 100) > 82) continue;

                $user           = $hatcheryUsers->random();
                $machineTemp    = round(rand(980, 996) / 10, 1); // 98.0–99.6°F (hatchers run cooler)
                $calibratorTemp = round($machineTemp + (rand(-3, 3) / 10), 1);
                $humidity       = round(rand(700, 820) / 10, 1); // 70–82% (hatchers run more humid)
                $startH         = $shift === '1st Shift' ? rand(6, 8) : ($shift === '2nd Shift' ? rand(14, 16) : rand(22, 23));
                $startM         = rand(0, 59);
                $endH           = $startH;
                $endM           = $startM + rand(15, 45);
                if ($endM >= 60) { $endH++; $endM -= 60; }

                Form::create([
                    'form_type_id' => $formTypeId,
                    'form_inputs'  => [
                        'machine_info' => [
                            'table' => 'hatcher-machines',
                            'id'    => $hatcher->id,
                            'name'  => $hatcher->hatcherName,
                        ],
                        'hatchery_man'     => $user->id,
                        'shift'            => $shift,
                        'time_started'     => sprintf('%02d:%02d', $startH, $startM),
                        'hatcher'          => $hatcher->id,
                        'machine_temp'     => $machineTemp,
                        'calibrator_temp'  => $calibratorTemp,
                        'humidity_reading' => $humidity,
                        'approver'         => $approvers[array_rand($approvers)],
                        'time_finished'    => sprintf('%02d:%02d', $endH, $endM),
                    ],
                    'date_submitted' => $date,
                    'uploaded_by'    => $user->id,
                ]);
                $count++;
            }
        }

        echo "Created {$count} hatcher temperature calibration forms.\n";
    }

    /**
     * Form 12: PASGAR Score
     * 1–3 evaluations per day — ~150 records over 90 days
     */
    private function seedPasgarScore(
        array $incubators,
        array $hatchers,
        array $psNumbers,
        array $houseNumbers,
        $hatcheryUsers,
        array $formTypeIds
    ): void {
        $formTypeId = $formTypeIds['PASGAR Score'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: PASGAR Score form type not found.\n";
            return;
        }
        if (empty($psNumbers) || empty($houseNumbers)) {
            echo "Skipping PASGAR Score: no PS/House numbers seeded.\n";
            return;
        }

        echo "Creating PASGAR score forms...\n";

        $scoringOptions = ['9.0', '9.1', '9.2', '9.3', '9.4', '9.5', '9.6', '9.7', '9.8', '9.9', '10.0'];
        $qcPersonnel    = ['Maria Santos', 'Roberto Cruz', 'Ana Reyes', 'Jose Garcia'];
        $count          = 0;

        for ($day = 0; $day < 90; $day++) {
            $date = Carbon::now()->subDays($day);

            $evaluations = rand(1, 3);
            for ($e = 0; $e < $evaluations; $e++) {
                if (rand(1, 100) > 75) continue;

                $user      = $hatcheryUsers->random();
                $psNumber  = $psNumbers[array_rand($psNumbers)];
                $houseNum  = $houseNumbers[array_rand($houseNumbers)];
                $incubator = $incubators[array_rand($incubators)];
                $hatcher   = $hatchers[array_rand($hatchers)];

                $chickWeight   = round(rand(380, 440) / 10, 1); // 38–44 g
                $dopPrimeQty   = rand(50, 200);
                $dopJrPrimeQty = rand(20, 100);
                $startH        = rand(6, 18);
                $startM        = rand(0, 59);
                $endH          = $startH + rand(0, 2);
                $endM          = rand(0, 59);

                Form::create([
                    'form_type_id' => $formTypeId,
                    'form_inputs'  => [
                        'personnel_name'           => $user->id,
                        'hatch_date'               => $date->format('Y-m-d'),
                        'time_started'             => sprintf('%02d:%02d', $startH, $startM),
                        'ps_number'                => $psNumber->id,
                        'house_number'             => $houseNum->id,
                        'incubator_number'         => $incubator->id,
                        'hatcher_number'           => $hatcher->id,
                        'average_chick_weight'     => $chickWeight,
                        'low_reflex_alertness_qty' => rand(0, 5),
                        'navel_issue_qty'          => rand(0, 8),
                        'leg_issue_qty'            => rand(0, 4),
                        'beak_issue_qty'           => rand(0, 3),
                        'belly_bloated_qty'        => rand(0, 6),
                        'pasgar_average_scoring'   => $scoringOptions[array_rand($scoringOptions)],
                        'dop_prime_qty'            => $dopPrimeQty,
                        'dop_prime_box_numbers'    => 'Box ' . rand(1, 20) . '-' . rand(21, 40),
                        'dop_jr_prime_qty'         => $dopJrPrimeQty,
                        'dop_jr_prime_box_numbers' => 'Box ' . rand(1, 10) . '-' . rand(11, 20),
                        'qc_personnel'             => $qcPersonnel[array_rand($qcPersonnel)],
                        'time_finished'            => sprintf('%02d:%02d', $endH, $endM),
                    ],
                    'date_submitted' => $date,
                    'uploaded_by'    => $user->id,
                ]);
                $count++;
            }
        }

        echo "Created {$count} PASGAR score forms.\n";
    }

    /**
     * Form 13: Incubator Rack Preventive Maintenance Checklist
     * ~1 PM per rack per month — 10 incubators × 3 months = ~30 records
     */
    private function seedIncubatorRackPm(array $incubators, $hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Incubator Rack Preventive Maintenance Checklist'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Incubator Rack PM form type not found.\n";
            return;
        }

        echo "Creating incubator rack PM forms...\n";

        $correctiveActions = [
            'Tightened and secured', 'Replaced worn component',
            'Cleaned and lubricated', 'Adjusted alignment',
            'Scheduled for full replacement',
        ];
        $count = 0;

        $statusAction = function () use ($correctiveActions): array {
            $ok     = rand(1, 10) > 2; // 80% pass
            $action = $ok ? 'N/A' : $correctiveActions[array_rand($correctiveActions)];
            return [$ok ? 'Yes' : 'No', $action];
        };

        for ($month = 0; $month < 3; $month++) {
            foreach ($incubators as $incubator) {
                $dayOffset = ($month * 30) + rand(0, 28);
                $date      = Carbon::now()->subDays($dayOffset);
                $user      = $hatcheryUsers->random();

                $startH = rand(8, 10);
                $startM = rand(0, 59);
                $endH   = $startH + rand(1, 3);
                $endM   = rand(0, 59);

                [$chord, $chordA]     = $statusAction();
                [$hose, $hoseA]       = $statusAction();
                [$wheels, $wheelsA]   = $statusAction();
                [$steel, $steelA]     = $statusAction();
                [$bolts, $boltsA]     = $statusAction();
                [$sensor, $sensorA]   = $statusAction();
                [$pneu, $pneuA]       = $statusAction();
                [$smooth, $smoothA]   = $statusAction();
                [$angle, $angleA]     = $statusAction();
                [$lub, $lubA]         = $statusAction();
                [$curtain, $curtainA] = $statusAction();

                Form::create([
                    'form_type_id' => $formTypeId,
                    'form_inputs'  => [
                        'machine_info' => [
                            'table' => 'incubator-machines',
                            'id'    => $incubator->id,
                            'name'  => $incubator->incubatorName,
                        ],
                        'rack_number'           => 'Rack-' . str_pad($incubator->id, 2, '0', STR_PAD_LEFT),
                        'date'                  => $date->format('Y-m-d'),
                        'time_started'          => sprintf('%02d:%02d', $startH, $startM),
                        'maintenance_personnel' => $user->id,

                        'chord_connection_status'              => $chord,
                        'chord_connection_corrective_action'   => $chordA,
                        'air_hose_status'                      => $hose,
                        'air_hose_corrective_action'           => $hoseA,
                        'wheels_status'                        => $wheels,
                        'wheels_corrective_action'             => $wheelsA,
                        'steel_frame_status'                   => $steel,
                        'steel_frame_corrective_action'        => $steelA,
                        'bolts_status'                         => $bolts,
                        'bolts_corrective_action'              => $boltsA,
                        'turning_sensor_status'                => $sensor,
                        'turning_sensor_corrective_action'     => $sensorA,
                        'pneumatic_cylinder_status'            => $pneu,
                        'pneumatic_cylinder_corrective_action' => $pneuA,
                        'smooth_turning_status'                => $smooth,
                        'smooth_turning_corrective_action'     => $smoothA,
                        'turning_angle_status'                 => $angle,
                        'left_turning_angle'                   => round(rand(420, 460) / 10, 1),
                        'right_turning_angle'                  => round(rand(420, 460) / 10, 1),
                        'turning_angle_corrective_action'      => $angleA,
                        'lubricate_bolts_status'               => $lub,
                        'lubricate_bolts_corrective_action'    => $lubA,
                        'plastic_curtain_status'               => $curtain,
                        'plastic_curtain_corrective_action'    => $curtainA,
                        'time_finished'                        => sprintf('%02d:%02d', $endH, $endM),
                    ],
                    'date_submitted' => $date,
                    'uploaded_by'    => $user->id,
                ]);
                $count++;
            }
        }

        echo "Created {$count} incubator rack PM forms.\n";
    }

    /**
     * Form 14: Weekly Voltage and Ampere Monitoring
     * Once per week — 13 records over 90 days
     */
    private function seedWeeklyVoltAmpere($hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Weekly Voltage and Ampere Monitoring'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Weekly Voltage and Ampere form type not found.\n";
            return;
        }

        echo "Creating weekly voltage and ampere forms...\n";

        $problemActions = [
            'No problems encountered, all readings within normal range',
            'Minor voltage fluctuation detected; checked and tightened connections',
            'Ampere reading slightly elevated on Line 2; cleaned contacts and monitored',
            'Voltage drop on Line 3; reported to electrical team for inspection',
        ];
        $count = 0;

        for ($week = 0; $week < 13; $week++) {
            $date = Carbon::now()->subDays($week * 7);
            $user = $hatcheryUsers->random();

            $v1 = rand(218, 232); $v2 = rand(218, 232); $v3 = rand(218, 232);
            $a1 = round(rand(80, 140) / 10, 1);
            $a2 = round(rand(80, 140) / 10, 1);
            $a3 = round(rand(80, 140) / 10, 1);
            $startH = rand(8, 10); $startM = rand(0, 59);
            $endH   = $startH;    $endM   = $startM + rand(20, 50);
            if ($endM >= 60) { $endH++; $endM -= 60; }

            Form::create([
                'form_type_id' => $formTypeId,
                'form_inputs'  => [
                    'maintenance_personnel'     => $user->id,
                    'date'                      => $date->format('Y-m-d'),
                    'time_started'              => sprintf('%02d:%02d', $startH, $startM),
                    'voltage_readings'          => "Line 1: {$v1}V | Line 2: {$v2}V | Line 3: {$v3}V",
                    'ampere_readings'           => "Line 1: {$a1}A | Line 2: {$a2}A | Line 3: {$a3}A",
                    'problem_corrective_action' => $problemActions[array_rand($problemActions)],
                    'time_finished'             => sprintf('%02d:%02d', $endH, $endM),
                ],
                'date_submitted' => $date,
                'uploaded_by'    => $user->id,
            ]);
            $count++;
        }

        echo "Created {$count} weekly voltage and ampere forms.\n";
    }

    /**
     * Form 15: Hatchery Diesel Generator Weekly Maintenance Checklist
     * 1 per gen-set per week — 2 gen-sets × 13 weeks = 26 records
     */
    private function seedDieselGeneratorWeekly($hatcheryUsers, array $formTypeIds): void
    {
        $formTypeId = $formTypeIds['Hatchery Diesel Generator Weekly Maintenance Checklist'] ?? null;
        if (! $formTypeId) {
            echo "Skipping: Diesel Generator Weekly form type not found.\n";
            return;
        }

        $genSets = DB::table('get-sets')->get()->all();
        if (empty($genSets)) {
            echo "Skipping Diesel Generator: no gen-sets found.\n";
            return;
        }

        echo "Creating diesel generator weekly maintenance forms...\n";

        $okayOrNot   = fn() => rand(1, 10) > 2 ? 'Okay' : 'Not Okay';
        $naOrProblem = fn(string $status) => $status === 'Okay'
            ? 'N/A'
            : fake()->randomElement(['Minor leak detected, scheduled for repair', 'Component worn; replacement ordered', 'Cleaned and re-tightened']);
        $tankLevels  = ['Full Tank', 'Half Tank', 'For Refill'];
        $runConds    = ['Normal', 'Normal', 'Normal', 'Abnormal']; // mostly normal
        $count       = 0;

        for ($week = 0; $week < 13; $week++) {
            $date = Carbon::now()->subDays($week * 7);
            foreach ($genSets as $genSet) {
                $user = $hatcheryUsers->random();

                $lubLeaks   = $okayOrNot(); $lubOil      = $okayOrNot();
                $coolLeaks  = $okayOrNot(); $coolRad     = $okayOrNot();
                $coolHose   = $okayOrNot(); $coolant     = $okayOrNot();
                $coolBelt   = $okayOrNot(); $fuelLeaks   = $okayOrNot();
                $airLeaks   = $okayOrNot(); $airCleaner  = $okayOrNot();
                $exhaust    = $okayOrNot(); $vibration   = $okayOrNot();
                $genAir     = $okayOrNot(); $genWindings = $okayOrNot();
                $switchGear = $okayOrNot();

                $prevHours = ($week * 7) + rand(100, 200);
                $presHours = $prevHours + rand(5, 20);
                $v1 = rand(218, 232); $v2 = rand(218, 232); $v3 = rand(218, 232);
                $a1 = round(rand(100, 180) / 10, 1);
                $a2 = round(rand(100, 180) / 10, 1);
                $a3 = round(rand(100, 180) / 10, 1);

                Form::create([
                    'form_type_id' => $formTypeId,
                    'form_inputs'  => [
                        'technician_id'  => $user->id,
                        'gen_set_number' => $genSet->id,

                        'lub_leaks_status'                     => $lubLeaks,
                        'lub_leaks_problem'                    => $naOrProblem($lubLeaks),
                        'lub_leaks_corrective_action'          => $naOrProblem($lubLeaks),
                        'lub_oil_level_status'                 => $lubOil,
                        'lub_oil_level_problem'                => $naOrProblem($lubOil),
                        'lub_oil_level_corrective_action'      => $naOrProblem($lubOil),

                        'cool_leaks_status'                    => $coolLeaks,
                        'cool_leaks_problem'                   => $naOrProblem($coolLeaks),
                        'cool_leaks_corrective_action'         => $naOrProblem($coolLeaks),
                        'cool_radiator_status'                 => $coolRad,
                        'cool_radiator_problem'                => $naOrProblem($coolRad),
                        'cool_radiator_corrective_action'      => $naOrProblem($coolRad),
                        'cool_hose_status'                     => $coolHose,
                        'cool_hose_problem'                    => $naOrProblem($coolHose),
                        'cool_hose_corrective_action'          => $naOrProblem($coolHose),
                        'cool_coolant_level_status'            => $coolant,
                        'cool_coolant_level_problem'           => $naOrProblem($coolant),
                        'cool_coolant_level_corrective_action' => $naOrProblem($coolant),
                        'cool_belt_status'                     => $coolBelt,
                        'cool_belt_problem'                    => $naOrProblem($coolBelt),
                        'cool_belt_corrective_action'          => $naOrProblem($coolBelt),

                        'fuel_leaks_status'                    => $fuelLeaks,
                        'fuel_leaks_problem'                   => $naOrProblem($fuelLeaks),
                        'fuel_leaks_corrective_action'         => $naOrProblem($fuelLeaks),

                        'air_intake_leaks_status'              => $airLeaks,
                        'air_intake_leaks_problem'             => $naOrProblem($airLeaks),
                        'air_intake_leaks_corrective_action'   => $naOrProblem($airLeaks),
                        'air_intake_cleaner_status'            => $airCleaner,
                        'air_intake_cleaner_problem'           => $naOrProblem($airCleaner),
                        'air_intake_cleaner_corrective_action' => $naOrProblem($airCleaner),

                        'exhaust_leaks_status'                 => $exhaust,
                        'exhaust_leaks_problem'                => $naOrProblem($exhaust),
                        'exhaust_leaks_corrective_action'      => $naOrProblem($exhaust),

                        'engine_vibration_status'              => $vibration,
                        'engine_vibration_problem'             => $naOrProblem($vibration),
                        'engine_vibration_corrective_action'   => $naOrProblem($vibration),

                        'main_gen_air_status'                  => $genAir,
                        'main_gen_air_problem'                 => $naOrProblem($genAir),
                        'main_gen_air_corrective_action'       => $naOrProblem($genAir),
                        'main_gen_windings_status'             => $genWindings,
                        'main_gen_windings_problem'            => $naOrProblem($genWindings),
                        'main_gen_windings_corrective_action'  => $naOrProblem($genWindings),

                        'switch_gear_status'                   => $switchGear,
                        'switch_gear_problem'                  => $naOrProblem($switchGear),
                        'switch_gear_corrective_action'        => $naOrProblem($switchGear),

                        'test_run_conducted'    => 'Conducted',
                        'test_run_time'         => rand(15, 30) . ' minutes',
                        'previous_running_time' => $prevHours . ' hrs',
                        'present_running_time'  => $presHours . ' hrs',
                        'line_voltages'         => "{$v1}V / {$v2}V / {$v3}V",
                        'line_amperes'          => "{$a1}A / {$a2}A / {$a3}A",
                        'hertz_reading'         => round(rand(595, 605) / 10, 1) . ' Hz',
                        'oil_pressure_kpa'      => rand(280, 350) . ' kPa',
                        'oil_temperature_f'     => rand(185, 215) . '°F',
                        'running_condition'     => $runConds[array_rand($runConds)],

                        'notes'                  => 'Weekly preventive maintenance completed. All systems checked.',
                        'diesel_tank_level'      => $tankLevels[array_rand($tankLevels)],
                        'refill_date'            => rand(1, 10) > 7 ? $date->format('Y-m-d') : 'N/A',
                        'available_diesel_stock' => rand(50, 200) . ' liters',
                    ],
                    'date_submitted' => $date,
                    'uploaded_by'    => $user->id,
                ]);
                $count++;
            }
        }

        echo "Created {$count} diesel generator weekly maintenance forms.\n";
    }
}
