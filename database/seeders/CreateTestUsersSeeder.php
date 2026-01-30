<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class CreateTestUsersSeeder extends Seeder
{
    public function run(): void
    {
        echo "Creating 5000 test users...\n";
        
        for ($i = 1; $i <= 5000; $i++) {
            User::create([
                'first_name' => 'User' . $i,
                'last_name' => 'Test' . $i,
                'username' => 'user' . $i,
                'user_type' => 1,
                'password' => bcrypt('password'),
            ]);
            
            if ($i % 100 == 0) {
                echo "Created {$i} users...\n";
            }
        }
        
        echo "Successfully created 5000 users!\n";
    }
}
