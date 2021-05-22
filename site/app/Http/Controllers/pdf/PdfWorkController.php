<?php

namespace App\Http\Controllers\pdf;

use App\Http\Controllers\Controller;
use App\Http\Requests\PdfCreatorRequest;
use App\Models\PdfWork;
use App\Traits\S3Manager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PdfWorkController extends Controller
{
    use S3Manager;

    private $workCode = null;
    private $fileName = null;

    public function index(Request $request)
    {
        return response()->json(['hola' => 'mundo']);
    }

    public function help()
    {
        return $this->successResponse([
            'link' => '',
            'callback' => '',
            'status' => ''
        ]);
    }

    public function creator(PdfCreatorRequest $request)
    {
        $validatedData = $request->validated();
        $pdfProcess = PdfWork::create([
            'code' => $this->getWorkCode(),
            'payload' => serialize($validatedData),
            'file_name' => $this->getFileName(),
            'status' => 'in_progress',
            'link' => $this->getUri($this->fileName),
            'callback' => $validatedData['callback']  ?? null
        ]);
        return $this->makeResponse($pdfProcess);

    }

    private function makeResponse(PdfWork $pdfProcess)
    {
        return $this->successResponse([
            'link' => $pdfProcess->link,
            'status' => $pdfProcess->status,
            'message' => $pdfProcess->message,
            'callback' => $pdfProcess->callback,
        ]);
    }

    /**
     * @return string
     */
    private function getWorkCode(): string
    {
        if (is_null($this->workCode)) {
            $this->workCode = Str::random(10);
        }
        return $this->workCode;
    }

    /**
     * @return string
     */
    private function getFileName(): string
    {
        if (is_null($this->fileName)){
            $this->fileName = Str::lower($this->workCode.'-'.Carbon::now()->format('YMd-His').'.pdf');
        }
        return $this->fileName;
    }
}
