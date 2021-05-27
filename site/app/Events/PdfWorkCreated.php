<?php

namespace App\Events;

use App\Models\PdfWork;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PdfWorkCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pdfWork;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PdfWork $pdfWork)
    {
        Log::info('Exec PdfWorkCreated Event: '.$pdfWork->code);
        $this->pdfWork = $pdfWork;
    }
}
