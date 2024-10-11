<?php

namespace Fenox\ApiBase\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ResponseHelper
{
    /**
     * @param array|Model|LengthAwarePaginator $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function success(array|Model|LengthAwarePaginator $data = [], string $message = 'Success', int $statusCode = 200): JsonResponse
    {

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ],  $statusCode);
    }

    /**
     * @param string $message
     * @param int $statusCode
     * @param array $data
     * @return JsonResponse
     */
    public static function error(string $message = 'An error occurred', int $statusCode = 500, array $data = []): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
