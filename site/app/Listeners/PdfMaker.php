<?php

namespace App\Listeners;

use App\Events\DownloadedFinishedFile;
use App\Jobs\CreatePdfFromTemplate;
use App\Traits\PdfWorkManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class PdfMaker implements ShouldQueue
{
    use PdfWorkManager;

    /**
     * @param DownloadedFinishedFile $event
     */
    public function handle(DownloadedFinishedFile $event)
    {
        Log::info('Exec PdfMaker Listener: '.$event->workCode);
        $PdfWork = $this->getWork($event->workCode);
        $payload = json_decode($PdfWork->payload, true);

        if (isset($payload['local-templates'])) {
            CreatePdfFromTemplate::dispatch($event->workCode)->delay(2);
        }
    }

    /**
     * Determine whether the listener should be queued.
     *
     * @param DownloadedFinishedFile $event
     * @return bool
     */
    public function shouldQueue(DownloadedFinishedFile $event): bool
    {
        $PdfWork = $this->getWork($event->workCode);
        $payload = json_decode($PdfWork->payload, true);
        if ($this->hasFinishedDownloadingTemplates($payload) && $this->hasFinishedDownloadingAttachments($payload)) {
            return true;
        }
        return false;
    }
}
