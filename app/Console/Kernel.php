<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\GenerateExcelFile;  // Add this line
use App\Http\Controllers\DatabaseBackupController;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ═══════════════════════════════════════════════════════════════
        // AUTOMATED DATABASE BACKUP — reads backup_schedule.json
        // ═══════════════════════════════════════════════════════════════
        $schedule->call(function () {
            $controller = new DatabaseBackupController();
            $controller->runScheduledBackup();
        })->name('scheduled-backup')->dailyAt('02:00')->withoutOverlapping()->appendOutputTo(storage_path('logs/backup.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
