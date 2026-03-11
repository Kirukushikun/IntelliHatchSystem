<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class HatcheryUserSeeder extends Seeder
{
    public function run(): void
    {
        echo "Creating 10 hatchery users...\n";

        User::factory()->count(10)->create([
            'user_type' => 1,
        ]);

        echo "Hatchery users created!\n";
        echo "- 10 hatchery users\n";
    }
}
