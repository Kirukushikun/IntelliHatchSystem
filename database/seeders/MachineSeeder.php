<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plenum;
use App\Models\Incubator;
use App\Models\Hatcher;
use Carbon\Carbon;

class MachineSeeder extends Seeder
{
    public function run(): void
    {
        echo "Creating machines...\n";

        // Create 10 plenums
        echo "Creating 10 plenums...\n";
        for ($i = 1; $i <= 10; $i++) {
            Plenum::create([
                'plenumName' => 'Plenum-' . $i,
                'isActive' => true,
                'creationDate' => Carbon::now()->subDays(rand(1, 90)),
            ]);
        }

        // Create 10 incubators
        echo "Creating 10 incubators...\n";
        for ($i = 1; $i <= 10; $i++) {
            Incubator::create([
                'incubatorName' => 'Incubator-' . $i,
                'isActive' => true,
                'creationDate' => Carbon::now()->subDays(rand(1, 90)),
            ]);
        }

        // Create 10 hatchers
        echo "Creating 10 hatchers...\n";
        for ($i = 1; $i <= 10; $i++) {
            Hatcher::create([
                'hatcherName' => 'Hatcher-' . $i,
                'isActive' => true,
                'creationDate' => Carbon::now()->subDays(rand(1, 90)),
            ]);
        }

        echo "Machines created!\n";
        echo "- 10 plenums\n";
        echo "- 10 incubators\n";
        echo "- 10 hatchers\n";
    }
}
