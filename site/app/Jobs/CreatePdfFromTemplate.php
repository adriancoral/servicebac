<?php

namespace App\Jobs;

use App\Events\FinishedPdfFile;
use App\Traits\PdfWorkManager;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;
use ZanySoft\LaravelPDF\PDF;

class CreatePdfFromTemplate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use PdfWorkManager;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 360;

    public $workCode;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($workCode)
    {
        Log::info('Exec CreatePdfFromTemplate Job: '.$workCode);
        $this->workCode = $workCode;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        try {
            $pdfFiles = $this->createPdf();

            $this->appendToPayload($this->workCode, 'local-templates-pdf', $pdfFiles);
            FinishedPdfFile::dispatch($this->workCode);
        } catch (InvalidArgumentException | MpdfException | Exception $exception) {
            CallBackResponse::dispatch($this->workCode, 'fail', $exception->getMessage())->delay(5);
            return true;
        }
    }

    /**
     * @return array
     * @throws MpdfException
     */
    private function createPdf(): array
    {
        $pdfFiles = [];
        $pdfWork = $this->getWork($this->workCode);
        $payload = json_decode($pdfWork->payload, true);

        $localTemplates = $payload['local-templates'];
        $content = $payload['content'];
        config()->set(['view.paths' => [storage_path().'/app/pdf/'.$pdfWork->code]]);

        foreach ($localTemplates as $order => $template) {
            $bladeTemplate = Str::of(Str::afterLast($template, '/'))->before('.');

            $pdf = new PDF();
            $pdf->loadView($bladeTemplate, $content);

            $filename = $this->randomFileName().'.pdf';
            $destTemplateToPdf = storage_path().'/app/pdf/'.$pdfWork->code.'/'.$filename;
            $pdf->Output($destTemplateToPdf, Destination::FILE);

            $pdfFiles[$order] = $destTemplateToPdf;
        }
        return $pdfFiles;
    }
}
