<?php

namespace App\Traits;

/**
 * API Response Trait
 *
 * Provides consistent JSON response formatting for all API endpoints.
 * Ensures all responses follow the standard format:
 * {
 *   "success": bool,
 *   "message": "string",
 *   "data": mixed,
 *   "errors": array,
 *   "meta": array
 * }
 */
trait ApiResponse
{
    /**
     * Success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @param array $meta
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data = null, $message = 'Success', $code = 200, $meta = null)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Error response
     *
     * @param string $message
     * @param int $code
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($message = 'Error', $code = 400, $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];

        return response()->json($response, $code);
    }

    /**
     * Validation error response
     *
     * @param array $errors
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function validationErrorResponse($errors, $message = 'Validation failed')
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Not found response
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function notFoundResponse($message = 'Resource not found')
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Unauthorized response
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function unauthorizedResponse($message = 'Unauthorized')
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function forbiddenResponse($message = 'Forbidden')
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Server error response
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function serverErrorResponse($message = 'Internal server error')
    {
        return $this->errorResponse($message, 500);
    }

    /**
     * Paginated response
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginated
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginatedResponse($paginated, $message = 'Success')
    {
        return $this->successResponse(
            $paginated->items(),
            $message,
            200,
            [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ]
        );
    }
}
