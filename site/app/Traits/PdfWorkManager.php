<?php

namespace App\Traits;

use App\Models\PdfWork;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LynX39\LaraPdfMerger\Facades\PdfMerger;

trait PdfWorkManager
{
    /**
     * @return string
     */
    protected function getWorkCode(): string
    {
        return  Str::lower(Str::random(10));
    }

    /**
     * @return string
     */
    protected function randomFileName(): string
    {
        return  Str::lower(Str::random(5));
    }

    /**
     * @param $workCode
     * @return string
     */
    protected function getFileName($workCode): string
    {
        return Str::lower($workCode.'-'.Carbon::now()->format('YMd-His').'.pdf');
    }

    /**
     * @param array $pdfFiles
     * @param $workCode
     * @return string
     */
    protected function mergePdf(array $pdfFiles, $workCode): string
    {
        $pdfMerger = PDFMerger::init();
        foreach ($pdfFiles as $fileFullPath) {
            $pdfMerger->addPDF($fileFullPath, 'all');
        }
        $pdfMerger->merge();

        $filename = $this->randomFileName().'.pdf';
        $pdfFileMergedPath = storage_path().'/app/pdf/'.$workCode.'/'.$filename;

        $pdfMerger->save($pdfFileMergedPath, 'file');

        return $pdfFileMergedPath;
    }

    /**
     * @param $workCode
     * @param $key
     * @param $pdfFileMergedPath
     */
    protected function appendToPayload($workCode, $key, $pdfFileMergedPath)
    {
        DB::beginTransaction();

        $pdfWork = $this->getWork($workCode);
        $payload = json_decode($pdfWork->payload, true);

        $payload[$key] = $pdfFileMergedPath;

        $pdfWork->payload = json_encode($payload);
        $pdfWork->save();

        DB::commit();
    }

    /**
     * @param $workCode
     * @return mixed
     */
    protected function getPayload($workCode)
    {
        return json_decode($this->getWork($workCode)->payload, true);
    }

    /**
     * @param $workCode
     * @return mixed
     * @todo verificar estado
     */
    protected function getWork($workCode)
    {
        return PdfWork::findOrFail($workCode);
    }

    /**
     * @param array $payload
     * @return bool
     */
    protected function hasTemplatesProcessed(array $payload): bool
    {
        if (isset($payload['local-templates']) && count($payload['local-templates']) > 0) {
            if (isset($payload['local-templates-pdf']) && count($payload['local-templates-pdf']) > 0) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $payload
     * @return bool
     */
    protected function hasLocalAttachments(array $payload): bool
    {
        if (isset($payload['local-attachments']) && count($payload['local-attachments']) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param $payload
     * @return bool
     */
    private function hasFinishedDownloadingAttachments($payload): bool
    {
        if (isset($payload['attachments'])) {
            if (count($payload['attachments']) == count($payload['local-attachments'])) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $payload
     * @return bool
     */
    private function hasFinishedDownloadingTemplates($payload): bool
    {
        return count($payload['templates']) == count($payload['local-templates']);
    }
}
