<?php

namespace App\Jobs;

use App\Events\DownloadedFinishedFile;
use App\Models\PdfWork;
use App\Traits\PdfWorkManager;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadFilesToLocal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, PdfWorkManager;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 360;

    public $workCode;
    public $fileUrl;
    public $payloadKey;
    public $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($workCode, $order, $fileUrl, $payloadKey)
    {
        Log::info('Exec DownloadFilesToLocal Job: '.$workCode);
        $this->workCode = $workCode;
        $this->fileUrl = $fileUrl;
        $this->order = $order;
        $this->payloadKey = $payloadKey;
    }

    /**
     * Execute the job.
     *
     * @return false|void
     * @throws Exception
     */
    public function handle()
    {
        try {

            if (PdfWork::findOrFail($this->workCode)->status != 'in_progress'){
                return false;
            }

            if ($this->payloadKey == 'local-templates'){
                $this->getHtmlFile();
            }

            if ($this->payloadKey == 'local-attachments'){
                $this->getPdfFile();
            }
            DownloadedFinishedFile::dispatch($this->workCode);
        } catch (Exception $exception) {
            CallBackResponse::dispatch($this->workCode, 'fail', $exception->getMessage())->delay(5);
            return true;
        }
    }

    /**
     * @throws Exception
     */
    private function getHtmlFile()
    {
        $filename = $this->randomFileName().'.blade.php';
        $dest = config('filesystems.local_pdf_path') . '/' . $this->workCode . '/' . $filename;

        $file = @file_get_contents($this->fileUrl);
        if ($file) {
            Storage::disk('localdisk')->put($dest, $file);
            $this->updatePayload(storage_path().'/app'.$dest);
        } else {
            throw new Exception('archivo no encontrado o sin permisos');
        }
    }

    /**
     * @throws Exception
     */
    private function getPdfFile()
    {
        $filename = $this->randomFileName().'.pdf';
        $dest = config('filesystems.local_pdf_path').'/'.$this->workCode.'/'.$filename;

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:71.0) Gecko/20100101 Firefox/71.0\r\n"
                    . "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n"
                    . "Accept-Language: en-US,en;q=0.5\r\n"
                    . "Accept-Encoding: gzip, deflate, br\r\n"
            ],
        ];

        $context = stream_context_create($opts);
        $data = file_get_contents( $this->fileUrl, false, $context);

        if ($data) {
            Storage::disk('localdisk')->put($dest, $data);
            $this->updatePayload(storage_path().'/app'.$dest);
        } else {
            throw new Exception('archivo no encontrado o sin permisos');
        }
    }

    /**
     * @param $src
     */
    private function updatePayload($src)
    {
        DB::beginTransaction();

        $pdfWork = PdfWork::findOrFail($this->workCode);
        $payload = json_decode($pdfWork->payload, true);
        $payload[$this->payloadKey][$this->order] = $src;

        $pdfWork->payload = json_encode($payload);
        $pdfWork->save();

        DB::commit();
    }
}
