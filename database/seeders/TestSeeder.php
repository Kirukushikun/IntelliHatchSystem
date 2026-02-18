<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Plenum;
use App\Models\Incubator;
use App\Models\Hatcher;
use App\Models\Form;
use App\Models\FormType;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        echo "Creating test data...\n";
        
        // Create 10 plenums
        echo "Creating 10 plenums...\n";
        $plenums = [];
        for ($i = 1; $i <= 10; $i++) {
            $plenums[] = Plenum::create([
                'plenumName' => 'Plenum-' . $i,
                'isActive' => true,
                'creationDate' => Carbon::now()->subDays(rand(1, 90)),
            ]);
        }
        
        // Create 10 incubators
        echo "Creating 10 incubators...\n";
        $incubators = [];
        for ($i = 1; $i <= 10; $i++) {
            $incubators[] = Incubator::create([
                'incubatorName' => 'Incubator-' . $i,
                'isActive' => true,
                'creationDate' => Carbon::now()->subDays(rand(1, 90)),
            ]);
        }
        
        // Create 10 hatchers
        echo "Creating 10 hatchers...\n";
        $hatchers = [];
        for ($i = 1; $i <= 10; $i++) {
            $hatchers[] = Hatcher::create([
                'hatcherName' => 'Hatcher-' . $i,
                'isActive' => true,
                'creationDate' => Carbon::now()->subDays(rand(1, 90)),
            ]);
        }
        
        // Create 10 hatchery users using factory with user_type 1
        echo "Creating 10 hatchery users...\n";
        $users = User::factory()->count(10)->create([
            'user_type' => 1,
        ]);
        
        // Get hatchery users (user_type = 1)
        $hatcheryUsers = User::where('user_type', 1)->get();
        
        if ($hatcheryUsers->isEmpty()) {
            echo "No hatchery users found. Please create users first.\n";
            return;
        }
        
        // Create 50 hatcher blower air speed forms for past 3 months
        echo "Creating 50 hatcher blower air speed forms...\n";
        $usedMachines = []; // Track used machines to prevent repetition
        
        for ($i = 1; $i <= 50; $i++) {
            $currentDate = Carbon::now()->subDays(rand(1, 90));
            $dateKey = $currentDate->format('Y-m-d');
            
            // Get random subset of active hatchers (60-100% to simulate some being inactive)
            $activeHatchers = $hatchers;
            $inactiveCount = rand(0, floor(count($hatchers) * 0.4)); // 0-40% may be inactive
            if ($inactiveCount > 0) {
                $randomKeys = array_rand($hatchers, $inactiveCount);
                if (!is_array($randomKeys)) $randomKeys = [$randomKeys];
                foreach ($randomKeys as $key) {
                    unset($activeHatchers[$key]);
                }
                $activeHatchers = array_values($activeHatchers);
            }
            
            // Filter out already used machines for this date
            $availableHatchers = array_filter($activeHatchers, function($hatcher) use ($usedMachines, $dateKey) {
                return !isset($usedMachines[$dateKey][$hatcher->id]);
            });
            
            // If all machines used for this date, skip to next date
            if (empty($availableHatchers)) {
                $i--; // Retry with different date
                continue;
            }
            
            $hatcher = $availableHatchers[array_rand($availableHatchers)];
            $usedMachines[$dateKey][$hatcher->id] = true;
            $user = $hatcheryUsers->random();
            
            // Generate base CFM reading with +/- 2.5% variance for each fan
            $baseCfm = rand(300, 500); // Base CFM between 300-500
            $cfmReadings = [];
            for ($fan = 1; $fan <= 4; $fan++) {
                $variance = (rand(-25, 25) / 1000) * $baseCfm; // +/- 2.5% variance
                $cfmReading = round($baseCfm + $variance);
                $cfmReadings[] = "Fan {$fan} - {$cfmReading} cfm";
            }
            
            $actions = ['No action needed', 'Cleaned fan blades', 'Adjusted fan speed', 'Scheduled maintenance', 'Replaced fan motor'];
            
            Form::create([
                'form_type_id' => 2, // Hatcher Blower Air Speed Monitoring
                'form_inputs' => [
                    'machine_info' => [
                        'table' => 'hatcher-machines',
                        'id' => $hatcher->id,
                        'name' => $hatcher->hatcherName,
                    ],
                    'cfm_fan_reading' => implode("\n", $cfmReadings),
                    'cfm_fan_action_taken' => $actions[array_rand($actions)],
                ],
                'date_submitted' => Carbon::now()->subDays(rand(1, 90)),
                'uploaded_by' => $user->id,
            ]);
            
            if ($i % 10 == 0) {
                echo "Created {$i} hatcher blower forms...\n";
            }
        }
        
        // Create 50 incubator blower air speed forms for past 3 months
        echo "Creating 50 incubator blower air speed forms...\n";
        $usedIncubators = []; // Track used incubators to prevent repetition
        
        for ($i = 1; $i <= 50; $i++) {
            $currentDate = Carbon::now()->subDays(rand(1, 90));
            $dateKey = $currentDate->format('Y-m-d');
            
            // Get random subset of active incubators (60-100% to simulate some being inactive)
            $activeIncubators = $incubators;
            $inactiveCount = rand(0, floor(count($incubators) * 0.4)); // 0-40% may be inactive
            if ($inactiveCount > 0) {
                $randomKeys = array_rand($incubators, $inactiveCount);
                if (!is_array($randomKeys)) $randomKeys = [$randomKeys];
                foreach ($randomKeys as $key) {
                    unset($activeIncubators[$key]);
                }
                $activeIncubators = array_values($activeIncubators);
            }
            
            // Filter out already used incubators for this date
            $availableIncubators = array_filter($activeIncubators, function($incubator) use ($usedIncubators, $dateKey) {
                return !isset($usedIncubators[$dateKey][$incubator->id]);
            });
            
            // If all incubators used for this date, skip to next date
            if (empty($availableIncubators)) {
                $i--; // Retry with different date
                continue;
            }
            
            $incubator = $availableIncubators[array_rand($availableIncubators)];
            $usedIncubators[$dateKey][$incubator->id] = true;
            $user = $hatcheryUsers->random();
            
            // Generate base CFM reading with +/- 2.5% variance for each fan
            $baseCfm = rand(300, 500); // Base CFM between 300-500
            $cfmReadings = [];
            for ($fan = 1; $fan <= 4; $fan++) {
                $variance = (rand(-25, 25) / 1000) * $baseCfm; // +/- 2.5% variance
                $cfmReading = round($baseCfm + $variance);
                $cfmReadings[] = "Fan {$fan} - {$cfmReading} cfm";
            }
            
            $actions = ['No action needed', 'Cleaned fan blades', 'Adjusted fan speed', 'Scheduled maintenance', 'Replaced fan motor'];
            
            Form::create([
                'form_type_id' => 3, // Incubator Blower Air Speed Monitoring
                'form_inputs' => [
                    'machine_info' => [
                        'table' => 'incubator-machines',
                        'id' => $incubator->id,
                        'name' => $incubator->incubatorName,
                    ],
                    'cfm_fan_reading' => implode("\n", $cfmReadings),
                    'cfm_fan_action_taken' => $actions[array_rand($actions)],
                ],
                'date_submitted' => Carbon::now()->subDays(rand(1, 90)),
                'uploaded_by' => $user->id,
            ]);
            
            if ($i % 10 == 0) {
                echo "Created {$i} incubator blower forms...\n";
            }
        }
        
        // Create incubator routine checklist forms based on schedule
        echo "Creating incubator routine checklist forms...\n";
        
        // Get the schedule from config
        $schedule = \App\Livewire\Configs\IncubatorRoutineConfig::schedule();
        $shifts = ['1st Shift', '2nd Shift', '3rd Shift'];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        $formCount = 0;
        // Create forms for past 3 months (90 days)
        for ($dayOffset = 0; $dayOffset < 90; $dayOffset++) {
            $currentDate = Carbon::now()->subDays($dayOffset);
            $dayName = $currentDate->format('l');
            
            foreach ($shifts as $shift) {
                $scheduleKey = "{$dayName}-{$shift}";
                
                // Get tasks for this day and shift
                $tasks = $schedule['_daily']; // Always include daily tasks
                if (isset($schedule[$scheduleKey])) {
                    $tasks = array_merge($tasks, $schedule[$scheduleKey]);
                }
                
                if (!empty($tasks)) {
                    // Get random subset of active incubators for this shift (60-100% to simulate some being inactive)
                    $activeIncubators = $incubators;
                    $inactiveCount = rand(0, floor(count($incubators) * 0.4)); // 0-40% may be inactive
                    if ($inactiveCount > 0) {
                        $randomKeys = array_rand($incubators, $inactiveCount);
                        if (!is_array($randomKeys)) $randomKeys = [$randomKeys];
                        foreach ($randomKeys as $key) {
                            unset($activeIncubators[$key]);
                        }
                        $activeIncubators = array_values($activeIncubators);
                    }
                    
                    // Create forms for all active incubators for this shift
                    foreach ($activeIncubators as $incubator) {
                        $user = $hatcheryUsers->random();
                        
                        // Build form inputs
                        $formInputs = [
                            'shift' => $shift,
                            'incubator' => $incubator->id,
                            'machine_info' => [
                                'table' => 'incubator-machines',
                                'id' => $incubator->id,
                                'name' => $incubator->incubatorName,
                            ],
                            'hatchery_man' => $user->id,
                        ];
                        
                        // Add alarm system condition and corrective action (always required)
                        $formInputs['alarm_system_condition'] = rand(0, 1) ? 'Operational' : 'Unoperational';
                        $formInputs['corrective_action'] = $formInputs['alarm_system_condition'] === 'Unoperational' 
                            ? 'Fixed alarm system issue' 
                            : 'No corrective action needed';
                        
                        // Add scheduled tasks with random status
                        foreach ($tasks as $task) {
                            if ($task === 'shift' || $task === 'hatchery_man' || $task === 'incubator') {
                                continue; // Already handled above
                            }
                            $formInputs[$task] = rand(0, 1) ? 'Done' : 'Pending';
                        }
                        
                        Form::create([
                            'form_type_id' => 1, // Incubator Routine Checklist Per Shift
                            'form_inputs' => $formInputs,
                            'date_submitted' => $currentDate,
                            'uploaded_by' => $user->id,
                        ]);
                        
                        $formCount++;
                        
                        if ($formCount % 50 == 0) {
                            echo "Created {$formCount} incubator routine forms...\n";
                        }
                    }
                }
            }
        }
        
        echo "Test data creation completed!\n";
        echo "Created:\n";
        echo "- 10 plenums\n";
        echo "- 10 incubators\n";
        echo "- 10 hatchers\n";
        echo "- 10 hatchery users\n";
        echo "- 50 hatcher blower air speed forms\n";
        echo "- 50 incubator blower air speed forms\n";
        echo "- {$formCount} incubator routine checklist forms\n";
    }
}
