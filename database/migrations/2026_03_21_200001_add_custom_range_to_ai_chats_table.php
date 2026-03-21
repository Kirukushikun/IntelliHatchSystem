<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `ai_chats` MODIFY COLUMN `context_period` ENUM('week','month','all','custom') NOT NULL DEFAULT 'week'");

        Schema::table('ai_chats', function (Blueprint $table) {
            $table->date('context_date_from')->nullable()->after('context_period');
            $table->date('context_date_to')->nullable()->after('context_date_from');
        });
    }

    public function down(): void
    {
        Schema::table('ai_chats', function (Blueprint $table) {
            $table->dropColumn(['context_date_from', 'context_date_to']);
        });

        DB::statement("ALTER TABLE `ai_chats` MODIFY COLUMN `context_period` ENUM('week','month','all') NOT NULL DEFAULT 'week'");
    }
};
