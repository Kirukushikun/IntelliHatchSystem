<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('form_types')->insertOrIgnore([
            'form_name'  => 'Hatchery Diesel Generator Weekly Maintenance Checklist',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('form_types')
            ->where('form_name', 'Hatchery Diesel Generator Weekly Maintenance Checklist')
            ->delete();
    }
};
