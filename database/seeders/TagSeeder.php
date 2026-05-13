<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = ['Hatcheryman', 'Maintenance', 'QA/QC', 'Supervisor'];

        foreach ($tags as $tag) {
            DB::table('tags')->insertOrIgnore([
                'name' => $tag,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
