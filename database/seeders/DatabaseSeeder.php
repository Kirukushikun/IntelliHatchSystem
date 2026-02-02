<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'User',
            'last_name' => 'Name',
            'username' => 'UName',
            'user_type' => 1,
            'password' => bcrypt('brookside25'),
        ]);
        User::factory()->create([
            'first_name' => 'User',
            'last_name' => 'Admin',
            'username' => 'UAdmin',
            'user_type' => 0,
            'password' => bcrypt('brookside25'),
        ]);
    }
}
