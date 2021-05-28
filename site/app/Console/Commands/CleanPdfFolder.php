<?php

namespace App\Console\Commands;

use App\Models\PdfWork;
use Exception;
use Illuminate\Console\Command;
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
        try {
            $pdfWorks = PdfWork::whereIn('status', ['done', 'fail'])->get();
            if ($pdfWorks->count()) {
                foreach ($pdfWorks as $work) {
                    $directory = config('filesystems.local_pdf_path').'/'.$work->code;
                    Storage::disk('localdisk')->deleteDirectory($directory);
                    Log::info('Deleted working folder:'.$work->code);
                }
            }
            return 0;
        } catch (Exception $exception) {
            Log::error('Command pdf-service:clean-folder: '.$exception->getMessage());
            return 0;
        }
    }
}
