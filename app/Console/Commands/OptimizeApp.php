<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the application by clearing compiled views, routes, and configuration caches ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('route:clear');
        $this->call('config:clear');
        $this->call('optimize:clear');
        $this->call('optimize');
        $this->info('Application optimized successfully!');
    }
}
