<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formTypes = [
            'Incubator Routine Checklist Per Shift',
            'Hatcher Blower Air Speed Monitoring',
            'Incubator Blower Air Speed Monitoring',
            'Hatchery Sullair Air Compressor Weekly PMS Checklist'
        ];

        foreach ($formTypes as $formName) {
            DB::table('form_types')->insert([
                'form_name' => $formName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
