<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Infrastructure\FrameworkCore\Enums\ErrorCode;

trait ApiResponse
{
    protected bool $useNewFormat = false;

    public function setUseNewFormat(bool $useNewFormat): void
    {
        $this->useNewFormat = $useNewFormat;
    }

    protected function isFormatEnabled(): bool
    {
        return $this->useNewFormat || config('boundly.api.response_format.enabled', false);
    }

    protected function success(mixed $data = null, int $status = 200, ?string $message = null): JsonResponse
    {
        if ($this->isFormatEnabled()) {
            return $this->newSuccessResponse($data, $status, $message);
        }

        return $this->legacySuccessResponse($data, $status, $message);
    }

    protected function created(mixed $data = null, ?string $message = null): JsonResponse
    {
        return $this->success($data, 201, $message ?? __('core::messages.resource_created_magic'));
    }

    protected function deleted(?string $message = null): JsonResponse
    {
        return $this->success(null, 204, $message ?? __('core::messages.resource_deleted_magic'));
    }

    protected function error(
        string $message,
        ErrorCode|string $code = ErrorCode::INTERNAL_ERROR,
        int $status = 500,
        array $details = []
    ): JsonResponse {
        if ($this->isFormatEnabled()) {
            return $this->newErrorResponse($message, $code, $status, $details);
        }

        return $this->legacyErrorResponse($message, $status, $details);
    }

    protected function notFound(string $message, ErrorCode|string $code = ErrorCode::RESOURCE_NOT_FOUND): JsonResponse
    {
        return $this->error($message, $code, 404);
    }

    protected function validationError(array $errors): JsonResponse
    {
        $message = __('core::messages.validation_failed');

        if ($this->isFormatEnabled()) {
            return $this->newErrorResponse(
                $message,
                ErrorCode::VALIDATION_FAILED,
                422,
                $errors
            );
        }

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    protected function unauthorized(?string $message = null): JsonResponse
    {
        return $this->error(
            $message ?? __('core::messages.unauthenticated'),
            ErrorCode::UNAUTHORIZED,
            401
        );
    }

    protected function forbidden(?string $message = null): JsonResponse
    {
        return $this->error(
            $message ?? __('core::messages.unauthorized'),
            ErrorCode::FORBIDDEN,
            403
        );
    }

    protected function rateLimited(int $retryAfter = 60): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => __('core::messages.rate_limit_exceeded'),
            'error' => [
                'code' => 'RATE_LIMITED',
                'retry_after' => $retryAfter,
            ],
        ], 429, [
            'Retry-After' => $retryAfter,
        ]);
    }

    protected function methodNotAllowed(string $method): JsonResponse
    {
        return $this->error(
            __('core::messages.unsupported_method', ['method' => $method]),
            ErrorCode::METHOD_NOT_ALLOWED,
            405
        );
    }

    protected function getRequestId(?Request $request = null): string
    {
        $request = $request ?? request();

        return $request->header('X-Request-ID', (string) Str::uuid());
    }

    protected function getResponseTimeMs(): float
    {
        if (! defined('LARAVEL_START')) {
            return 0;
        }

        return round((microtime(true) - LARAVEL_START) * 1000, 2);
    }

    protected function newSuccessResponse(mixed $data, int $status, ?string $message): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
            'meta' => $this->buildMeta($message),
            'error' => null,
        ];

        if ($status === 204) {
            unset($response['data']);
            $response['data'] = null;
        }

        return response()->json($response, $status);
    }

    protected function legacySuccessResponse(mixed $data, int $status, ?string $message): JsonResponse
    {
        $response = [
            'status' => 'success',
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $status);
    }

    protected function newErrorResponse(
        string $message,
        ErrorCode|string $code,
        int $status,
        array $details = []
    ): JsonResponse {
        $errorCode = $code instanceof ErrorCode ? $code->value : $code;

        $response = [
            'success' => false,
            'data' => null,
            'meta' => $this->buildMeta(),
            'error' => [
                'code' => $errorCode,
                'message' => $message,
            ],
        ];

        if (! empty($details)) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $status);
    }

    protected function legacyErrorResponse(string $message, int $status, array $details = []): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if (! empty($details)) {
            $response['error'] = $details;
        }

        return response()->json($response, $status);
    }

    protected function buildMeta(?string $message = null): array
    {
        $config = config('boundly.api.response_format', []);

        $meta = [];

        if ($config['include_timestamp'] ?? true) {
            $meta['timestamp'] = now()->toIso8601String();
        }

        if ($config['include_request_id'] ?? true) {
            $meta['request_id'] = $this->getRequestId();
        }

        $meta['response_time_ms'] = $this->getResponseTimeMs();

        if ($message) {
            $meta['message'] = $message;
        }

        return $meta;
    }
}
