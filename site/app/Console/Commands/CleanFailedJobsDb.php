<?php

namespace App\Console\Commands;

use App\Models\FailedJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanFailedJobsDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf-service:clean-failed-jobs_db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean failed_jobs table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $jobs = FailedJob::All();
        if ($jobs->count()) {
            DB::table('failed_jobs')->truncate();
            Log::warning('Delete #'.$jobs->count().' records at failed_jobs table');
        }
        return 0;
    }
}
