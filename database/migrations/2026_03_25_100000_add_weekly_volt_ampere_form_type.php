<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('form_types')->insertOrIgnore([
            'form_name'  => 'Weekly Voltage and Ampere Monitoring',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('form_types')
            ->where('form_name', 'Weekly Voltage and Ampere Monitoring')
            ->delete();
    }
};
