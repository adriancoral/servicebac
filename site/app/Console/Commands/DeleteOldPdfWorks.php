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
    protected $signature = 'pdf-service:delete-old-pdfworks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete any pdfworks older than 24 hours';

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
        $pdfWorks->each(function ($work, $key) {
            $work->delete();
            Log::info('Deleted old pdfworks:'.$work->code);
        });
        return 0;
    }
}
