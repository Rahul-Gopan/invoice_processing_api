<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $code);
    }

    protected function failureResponse(string $message = 'Failure', int $code = 400, $errors = null): JsonResponse
    {
        return ApiResponse::failure($message, $code, $errors);
    }

    protected function paginationResponse($data, string $message = 'Success'): JsonResponse
    {
        return ApiResponse::paginated($data, $message);
    }
}
