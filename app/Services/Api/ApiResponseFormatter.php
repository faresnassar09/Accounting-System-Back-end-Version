<?php

namespace App\Services\Api;

use Illuminate\Http\JsonResponse;

class ApiResponseFormatter
{


    public function successResponse(
        $message,
        $data = [],
        $code = 200,
        $headers = [],
        $options = 0

    ): JsonResponse {

        return response()->json([

            'success' => true,
            'message' => $message,
            'data' => $data,
            'code' => $code,

        ], $code, $headers, $options);
    }

    public function failedResponse(
        $message = '',
        $data = [],
        $code = 500,
        $headers = [],
        $options = 0
        
    ): JsonResponse {

        return response()->json([

            'success' => false,
            'message' => $message,
            'data' => $data,
            'code' => $code,



        ], $code, $headers, $options,);
    }
}
