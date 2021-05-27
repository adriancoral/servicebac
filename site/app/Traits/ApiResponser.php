<?php
namespace App\Traits;


use Illuminate\Http\JsonResponse;

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
        return response()->json(['success' => false, 'message' => $message], $code);
    }

}
