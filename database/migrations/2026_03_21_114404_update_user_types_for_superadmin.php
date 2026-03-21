<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Shift user_type values up to make room for superadmin (0).
     * Old: 0=admin, 1=user
     * New: 0=superadmin, 1=admin, 2=user
     */
    public function up(): void
    {
        // Order matters: shift users first, then admins, to avoid collisions
        DB::statement('UPDATE users SET user_type = 2 WHERE user_type = 1');
        DB::statement('UPDATE users SET user_type = 1 WHERE user_type = 0');
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        DB::statement('UPDATE users SET user_type = 0 WHERE user_type = 1');
        DB::statement('UPDATE users SET user_type = 1 WHERE user_type = 2');
    }
};
