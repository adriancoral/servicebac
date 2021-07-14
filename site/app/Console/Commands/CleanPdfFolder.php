<?php

namespace App\Console\Commands;

use App\Models\PdfWork;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanPdfFolder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf-service:clean-folder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove temp files in storage/app/pdf';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $pdfWorks = PdfWork::whereIn('status', ['done', 'fail'])->get();
        $filtered = $pdfWorks->filter(function ($work, $key) {
            if (! isset($work->internal_status['temp-folder'])) {
                return $work;
            }
        });
        $filtered->each(function ($work, $key) {
            $work->update(['internal_status->temp-folder' => ['clean' => Carbon::now()]]);
            $directory = config('filesystems.local_pdf_path').'/'.$work->code;
            Storage::disk('localdisk')->deleteDirectory($directory);
            Log::info('Deleted working folder:'.$work->code);
        });
        Log::info('Clean folders:'.$filtered->count());
        $this->info('clean-folder End');
        return 0;
    }
}
