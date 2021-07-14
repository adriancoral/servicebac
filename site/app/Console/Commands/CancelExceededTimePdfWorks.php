<?php

namespace App\Console\Commands;

use App\Jobs\UpdateStatus;
use App\Models\PdfWork;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExceededTimePdfWorks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf-service:cancel-exceeded-time-pdfworks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search PdfWork with in_progress after 15 min and pass to failed';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $date = Carbon::now()->subMinutes(15);
        $this->info('Start time: '.$date->format('H:i:s - Y-m-d'));
        $pdfWorks = PdfWork::where('status', 'in_progress')
            ->where('updated_at', '<', $date)
            ->get();
        if ($pdfWorks->count()) {
            foreach ($pdfWorks as $work) {
                UpdateStatus::dispatch($work->code, 'fail', $this->signature)->delay(now()->addSeconds(5));
                Log::info('Cancel exceeded time jobs: '.$work->code.', Last updated_at: '.$work->updated_at->format('H:i:s - Y-m-d'));
            }
        }
        return 0;
    }
}
