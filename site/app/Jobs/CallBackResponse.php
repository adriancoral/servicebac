<?php

namespace App\Jobs;

use App\Http\Resources\PdfWorkResource;
use App\Models\PdfWork;
use App\Traits\PdfWorkManager;
use Exception;
use HttpException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CallBackResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, PdfWorkManager;

    public $workCode;
    public $message;
    public $status;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($workCode, $status, $message = null)
    {
        Log::info('Exec CallBackResponse Job: '.$workCode);
        $this->workCode = $workCode;
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        try {
            $work = PdfWork::updateOrCreate(
                ['code' => $this->workCode],
                ['status' => $this->status, 'message' => $this->message]
            );
            $this->callBack();
        } catch (ConnectionException $exception) {
            Log::error('CallBack response fail: '.$exception->getMessage());
            return true;
        } catch (Exception $exception) {
            Log::error('Last update work fail: '.$exception->getMessage());
            return true;
        }
    }

    /**
     * @return bool
     * @throws ConnectionException
     */
    private function callBack(): bool
    {
        try {
            $body = [];
            $pdfWork = $this->getWork($this->workCode);
            $payload = json_decode($pdfWork->payload, true);

            if (isset($payload['callback'])){
                $postUrl = $payload['callback'];

                $data = PdfWorkResource::responseData();
                foreach ($data as $field){
                    $body[$field] = $pdfWork->$field;
                }
                Http::post($postUrl, [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'body' =>  $body,
                ]);
            }
            return true;
        } catch (Exception $exception) {
            throw new ConnectionException($exception->getMessage());
        }
    }
}
