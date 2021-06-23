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
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CallBackResponse implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use PdfWorkManager;

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
                ['status' => $this->status, 'message' => Str::of($this->message)->limit(200)]
            );
            Log::info('Status: '.$this->status.', message: '.$this->message);
            $this->callBack();
        } catch (ConnectionException $exception) {
            Log::error('CallBack response fail: '.$exception->getMessage());
            return true;
        } catch (QueryException $exception) {
            Log::error('Query Exception: '.$exception->getMessage());
        } catch (Exception $exception) {
            Log::error('General Exception: last update work fail: '.$exception->getMessage());
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

            if (!is_null($pdfWork->callback)) {
                Log::info('Callback URL: '.$pdfWork->callback);
                $postUrl = $pdfWork->callback;

                $data = PdfWorkResource::responseData();
                foreach ($data as $field) {
                    $body[$field] = $pdfWork->$field;
                }
                Log::info('BODY: '.json_encode($body));

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($postUrl, [
                    'data' => $body,
                ])->throw()->json();

                Log::info('Response OK: '.json_encode($response));
            } else {
                Log::info('Response NO CALLBACK');
            }
            return true;
        } catch (Exception $exception) {
            Log::error('Exception FAIL: '.$exception->getMessage());
            throw new ConnectionException($exception->getMessage());
        }
    }
}
