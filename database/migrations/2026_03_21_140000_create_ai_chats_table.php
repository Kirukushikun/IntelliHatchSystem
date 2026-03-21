<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('prompt');
            $table->text('system_prompt_snapshot')->nullable();
            $table->longText('context_data')->nullable();
            $table->foreignId('form_type_id')->nullable()->constrained('form_types')->onDelete('set null');
            $table->enum('context_period', ['week', 'month', 'all'])->default('week');
            $table->enum('status', ['pending', 'analyzing', 'done', 'failed'])->default('pending');
            $table->longText('response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chats');
    }
};
