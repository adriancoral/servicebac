<?php

namespace App\Jobs;

use App\Traits\PdfWorkManager;
use App\Traits\S3Manager;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PdfFileDelivery implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use PdfWorkManager;
    use S3Manager;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 360;

    public $tries = 1;

    public $maxExceptions = 1;

    public $pdfWork;

    public $payload;

    public $workCode;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($workCode)
    {
        Log::info('Exec PdfFileDelivery Job: '.$workCode);
        $this->workCode = $workCode;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        try {
            $pdfWork = $this->getWork($this->workCode);
            $payload = json_decode($pdfWork->payload, true);

            if ($this->hasTemplatesProcessed($payload)) {
                $pdfFromTemplates = $payload['local-templates-pdf'];
                $pdfFromTemplatesOrdered = collect($pdfFromTemplates)->sortKeys();

                $filesToMerge = $pdfFromTemplatesOrdered->toArray();

                if ($this->hasLocalAttachments($payload)) {
                    $pdfAttachments = $payload['local-attachments'];
                    $pdfAttachmentsOrdered = collect($pdfAttachments)->sortKeys();

                    $filesToMerge = array_merge($pdfFromTemplatesOrdered->toArray(), $pdfAttachmentsOrdered->toArray());
                }

                $finalFile = $this->mergePdf($filesToMerge, $this->workCode);

                $this->uploadFile($finalFile, $pdfWork->file_name);

                CallBackResponse::dispatch($this->workCode, 'done')->delay(5);
                return true;
            }
        } catch (Exception $exception) {
            CallBackResponse::dispatch($this->workCode, 'fail', $exception->getMessage())->delay(5);
            return true;
        }
    }
}
