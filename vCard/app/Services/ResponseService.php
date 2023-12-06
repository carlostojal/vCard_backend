<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Mockery\Matcher\Any;

class ResponseService
{

    public function sendStandardResponse(int $httpStatusCode, string $message): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message'=> $message,
        ], $httpStatusCode);
    }

    public function sendWithDataResponse(int $httpStatusCode, ?string $message, $data): JsonResponse
    {
        $response = [
            'status' => 'success',
            'data' => $data,
        ];

        if ($message !== null && $message !== '') {
            $response['message'] = $message;
        }

        return response()->json($response, $httpStatusCode);
    }

}
