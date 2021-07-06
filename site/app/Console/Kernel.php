<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\AppMigrate::class,
        Commands\AppMigrateProduction::class,
        Commands\CleanPdfFolder::class,
        Commands\DeleteOldPdfWorks::class,
        Commands\CancelExceededTimePdfWorks::class,
        Commands\CleanFailedJobsDb::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('pdf-service:clean-folder')->everyFifteenMinutes();
        $schedule->command('pdf-service:cancel-exceeded-time-pdfworks')->everyFifteenMinutes();
        $schedule->command('pdf-service:clean-failed-jobs_db')->everyThirtyMinutes();

        $schedule->command('pdf-service:delete-old-pdfworks')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
