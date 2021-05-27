<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DownloadedFinishedFile
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $workCode;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($workCode)
    {
        Log::info('Exec DownloadedFinishedFile Event: '.$workCode);
        $this->workCode = $workCode;
    }
}
