<?php

namespace App\Http\Controllers\pdf;

use App\Http\Controllers\Controller;
use App\Http\Requests\PdfCreatorRequest;
use App\Http\Resources\PdfWorkResource;
use App\Models\PdfWork;
use App\Traits\PdfWorkManager;
use App\Traits\S3Manager;

class PdfWorkController extends Controller
{
    use S3Manager;
    use PdfWorkManager;

    /**
     * @param PdfWork $pdfWork
     * @return PdfWorkResource
     */
    public function status(PdfWork $pdfWork): PdfWorkResource
    {
        return new PdfWorkResource($pdfWork);
    }

    /**
     * @param PdfCreatorRequest $request
     * @return PdfWorkResource
     */
    public function creator(PdfCreatorRequest $request): PdfWorkResource
    {
        $validatedData = $request->validated();
        $validatedData['local-templates'] = [];
        $validatedData['local-attachments'] = [];

        $workCode = $this->getWorkCode();
        $fileName = $this->getFileName($workCode);
        $pdfProcess = PdfWork::create([
            'code' => $workCode,
            'payload' => json_encode($validatedData),
            'file_name' => $fileName,
            'status' => 'in_progress',
            'link' => $this->getUri($fileName),
            'callback' => $validatedData['callback'] ?? null,
        ]);
        return new PdfWorkResource($pdfProcess);
    }

    public function testPhpCSFixer($algo){
        if($algo)
            return true;
    }
}
