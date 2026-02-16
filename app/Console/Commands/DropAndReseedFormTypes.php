<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropAndReseedFormTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'form:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop form_types table and reseed it';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dropping form_types table...');
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Drop table if exists
        Schema::dropIfExists('form_types');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->info('Running migration to recreate form_types table...');
        
        // Create the table directly using Schema
        Schema::create('form_types', function (Blueprint $table) {
            $table->id();
            $table->string('form_name')->unique();
            $table->timestamps();
        });
        
        $this->info('Running FormTypeSeeder...');
        
        // Run the seeder
        $this->call('db:seed', [
            '--class' => 'FormTypeSeeder'
        ]);
        
        $this->info('Form types table has been reset successfully!');
        
        return 0;
    }
}
