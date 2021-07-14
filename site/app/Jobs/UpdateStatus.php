<?php

namespace App\Jobs;

use App\Traits\PdfWorkManager;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UpdateStatus implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use PdfWorkManager;

    private $workCode;

    private $message;

    private $status;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($workCode, $status, $message = null)
    {
        Log::info('Exec UpdateStatus Job: '.$workCode);
        $this->workCode = $workCode;
        $this->status = $status;
        $this->message = $message;
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
            $pdfWork->update([
                'status' => $this->status,
                'message' => Str::of($this->message)->limit(200),

            ]);
            Log::info('Status: '.$this->status.', message: '.$this->message);
            CallBackResponse::dispatch($this->workCode)->delay(now()->addSeconds(5));
        } catch (QueryException $exception) {
            Log::error('Query Exception: '.$exception->getMessage());
        } catch (Exception $exception) {
            Log::error('General Exception: last update work fail: '.$exception->getMessage());
        }
        return true;
    }
}
