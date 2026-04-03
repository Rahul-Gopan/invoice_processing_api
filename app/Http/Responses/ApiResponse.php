<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse 
{

    public static function success($data = null, string $message = 'Success', int $code = 200): JsonResponse 
    {
        return response()->json([
            'success'   => true,
            'status'    => $code,
            'message'   => $message,
            'data'      => $data
        ], $code);
    }

    public static function failure(string $message = 'Failed', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success'   => false,
            'status'    => $code,
            'message'   => $message,
            'errors'    => $errors
        ], $code);
    }

    public static function paginated($data, string $messag =  'Success'): JsonResponse
    {
        return response()->json([
            'success'       => true,
            'status'        => 200,
            'message'       => $messag,
            'data'          => $data->items(),
            'pagination'    => [
                'total'         => $data->total(),
                'per_page'      => $data->perPage(),
                'current_page'  => $data->currentPage(),
                'last_page'     => $data->lastPage(),
                'from'          => $data->firstItem(),
                'to'            => $data->lastItem(),
            ]
        ]);
    }
}