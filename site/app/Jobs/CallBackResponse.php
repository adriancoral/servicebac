<?php

namespace App\Jobs;

use App\Traits\PdfWorkManager;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CallBackResponse implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use PdfWorkManager;

    private $pdfWork;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($workCode)
    {
        Log::info('Exec CallbackResponse Job: '.$workCode);
        $this->pdfWork = $this->getWork($workCode);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->availableTries()) {
                $this->updateResponse($this->callBack($this->pdfWork));
                $this->checkResponse();
            }
        } catch (ConnectionException $exception) {
            Log::error('CallBack response fail: '.$exception->getMessage());
        } catch (QueryException $exception) {
            Log::error('Query Exception: '.$exception->getMessage());
        } catch (Exception $exception) {
            Log::error('General Exception: callback work fail: '.$exception->getMessage());
        }
    }

    /**
     * @param $response
     * @return void
     */
    private function updateResponse($response)
    {
        if ($response) {
            $this->pdfWork->update([
                'callback_response' => ['response' => $response, 'tries' => $this->addTries()],
            ]);
        }
    }

    /**
     * @return void
     */
    private function checkResponse()
    {
        if (!isset($this->pdfWork->callback_response['response']['payload']) && $this->pdfWork->callback) {
            Log::info('CallBack resend after 5 min');
            CallBackResponse::dispatch($this->pdfWork->code)->delay(now()->addMinutes(5));
        }
    }

    /**
     * @return bool
     */
    private function availableTries(): bool
    {
        if (isset($this->pdfWork->callback_response['tries']) && count($this->pdfWork->callback_response['tries']) === 3) {
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    private function addTries(): array
    {
        $tries = [Carbon::now()];
        if (isset($this->pdfWork->callback_response['tries'])) {
            $tries = collect($this->pdfWork->callback_response['tries'])->push(Carbon::now())->toArray();
        }
        return $tries;
    }
}
