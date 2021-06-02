<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function healthcheck(): JsonResponse
    {
        try {
            if (DB::connection()->getPdo()) {
                return response()->json(['message' => 'ok'], 200);
            }
        } catch (Exception $exception) {
            return response()->json(['message' => 'fail'], 500);
        }
    }
}
