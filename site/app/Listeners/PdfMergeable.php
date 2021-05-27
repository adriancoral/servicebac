<?php

namespace App\Listeners;

use App\Events\FinishedPdfFile;
use App\Jobs\PdfFileDelivery;
use App\Traits\PdfWorkManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PdfMergeable implements ShouldQueue
{
    use PdfWorkManager;

    /**
     * Handle the event.
     *
     * @param  FinishedPdfFile  $event
     * @return void
     */
    public function handle(FinishedPdfFile $event)
    {
        Log::info('Exec PdfMergeable Listeners: '.$event->workCode);
        PdfFileDelivery::dispatch($event->workCode)->delay(5);
    }

    /**
     * Determine whether the listener should be queued.
     *
     * @param FinishedPdfFile $event
     * @return bool
     */
    public function shouldQueue(FinishedPdfFile $event): bool
    {
        $payload = $this->getPayload($event->workCode);
        if ($this->hasTemplatesProcessed($payload)) { //&& $this->hasAttachmentsProcessed($payload)
            return true;
        }
        return false;
    }
}
