<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_types', function (Blueprint $table) {
            $table->string('usage_frequency')->nullable()->after('impact_level');
        });

        Schema::create('form_type_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_type_id')->constrained('form_types')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['form_type_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_type_tag');

        Schema::table('form_types', function (Blueprint $table) {
            $table->dropColumn('usage_frequency');
        });
    }
};
