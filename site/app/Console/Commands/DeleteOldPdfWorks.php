<?php

namespace App\Console\Commands;

use App\Models\PdfWork;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteOldPdfWorks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf-service:delete-oldworks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete jobs older than 24 hours';

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
        $date = Carbon::now()->subHours(24);
        $pdfWorks = PdfWork::whereIn('status', ['done', 'fail'])
            ->where('created_at', '<', $date)
            ->get();
        if ($pdfWorks->count()) {
            foreach ($pdfWorks as $work){
                $work->delete();
                Log::info('Deleted old work:'.$work->code);
            }
        }
        return 0;
    }
}
