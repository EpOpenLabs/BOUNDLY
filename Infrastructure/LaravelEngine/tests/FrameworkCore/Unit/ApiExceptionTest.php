<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Enums\ErrorCode;
use Infrastructure\FrameworkCore\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

class ApiExceptionTest extends TestCase
{
    public function test_exception_has_error_code(): void
    {
        $exception = new ApiException(
            'Test error',
            ErrorCode::RESOURCE_NOT_FOUND,
            404
        );

        $this->assertEquals(ErrorCode::RESOURCE_NOT_FOUND, $exception->getErrorCode());
        $this->assertEquals(404, $exception->getStatusCode());
        $this->assertEquals('Test error', $exception->getMessage());
    }

    public function test_exception_with_details(): void
    {
        $details = ['field' => 'email', 'reason' => 'required'];
        
        $exception = new ApiException(
            'Validation failed',
            ErrorCode::VALIDATION_FAILED,
            422,
            $details
        );

        $this->assertEquals($details, $exception->getDetails());
    }

    public function test_to_array(): void
    {
        $exception = new ApiException(
            'Not found',
            ErrorCode::RESOURCE_NOT_FOUND,
            404,
            ['id' => 123]
        );

        $array = $exception->toArray();

        $this->assertEquals('ERR_RESOURCE_NOT_FOUND', $array['code']);
        $this->assertEquals('Not found', $array['message']);
        $this->assertEquals(['id' => 123], $array['details']);
    }

    public function test_accepts_string_error_code(): void
    {
        $exception = new ApiException(
            'Custom error',
            ErrorCode::SERVER_ERROR->value,
            500
        );

        $this->assertEquals('ERR_SERVER_ERROR', $exception->getErrorCode()->value);
    }
}
