<?php

namespace App\Listeners;

use Exception;
use App\Events\PdfWorkCreated;
use App\Jobs\DownloadFilesToLocal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class PdfWorkGetSources implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param PdfWorkCreated $event
     * @return void
     * @throws Exception
     */
    public function handle(PdfWorkCreated $event)
    {
        Log::info('Exec PdfWorkGetSources Listeners: '.$event->pdfWork->code);
        try {
            $pdfWork = $event->pdfWork;
            $payload = json_decode($pdfWork->payload, true);

            $templates = $payload['templates'];
            foreach ($templates as $order => $url) {
                DownloadFilesToLocal::dispatch($pdfWork->code, $order, $url, 'local-templates');
            }

            if (isset($payload['attachments'])){
                $attachments = $payload['attachments'];
                foreach ($attachments as $order => $url) {
                    DownloadFilesToLocal::dispatch($pdfWork->code, $order, $url, 'local-attachments');
                }
            }
            return;
        } catch (Exception $exception){
            throw new Exception('Error'.$exception->getMessage());
        }
    }
}
