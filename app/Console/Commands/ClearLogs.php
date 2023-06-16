<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearLogs extends Command
{
    protected $signature = 'clear-logs';
    protected $description = 'Clear all Laravel logs';

    public function handle()
    {
        $logPath = storage_path('logs/laravel.log');

        if (File::exists($logPath)) {
            File::put($logPath, '');
            $this->info('Logs cleared successfully.');
        } else {
            $this->error('Log file not found.');
        }
    }
}
