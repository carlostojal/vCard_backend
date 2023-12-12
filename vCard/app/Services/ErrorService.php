<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;

class ErrorService
{
    public function sendStandardError(int $httpStatusCode, string $message): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $httpStatusCode);
    }

    public function sendValidatorError(int $httpStatusCode, string $message, MessageBag $validatorErrors): JsonResponse
    {
        // return response()->json([
        //     'status' => 'error',
        //     'message' => $message,
        //     'errors' => $validatorErrors
        // ], $httpStatusCode);


        $response = [
            'status' => 'error',
            'errors' => $validatorErrors,
        ];

        if ($message !== null && $message !== '') {
            $response['message'] = $message;
        }

        return response()->json($response, $httpStatusCode);
    }
}
