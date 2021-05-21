<?php
namespace App\Traits;


use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait ApiResponser
{

    /**
     * @param $data
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse($data, $code = 200): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data], $code);
    }

    /**
     * @param $message
     * @param $code
     * @return JsonResponse
     */
    protected function errorResponse($message, $code): JsonResponse
    {
        //Log::warning("HTTP ERROR $code", ['message' => $message]);
        return response()->json(['success' => false, 'message' => $message], $code);
    }

}
