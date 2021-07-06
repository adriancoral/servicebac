<?php

namespace App\Console\Commands;

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
    protected $description = 'Search PdfWord with in_progress after 15 min and pass to failed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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
                $work->update(['status' => 'fail']);
                Log::info('Cancel exceeded time jobs: '.$work->code.', Last updated_at: '.$work->updated_at->format('H:i:s - Y-m-d'));
            }
        }
        return 0;
    }
}
