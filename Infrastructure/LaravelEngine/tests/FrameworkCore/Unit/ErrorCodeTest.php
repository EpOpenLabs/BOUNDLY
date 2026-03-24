<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Enums\ErrorCode;
use PHPUnit\Framework\TestCase;

class ErrorCodeTest extends TestCase
{
    public function test_resource_not_found_http_status(): void
    {
        $this->assertEquals(404, ErrorCode::RESOURCE_NOT_FOUND->httpStatus());
        $this->assertEquals(404, ErrorCode::RESOURCE_NOT_DEFINED->httpStatus());
    }

    public function test_validation_failed_http_status(): void
    {
        $this->assertEquals(422, ErrorCode::VALIDATION_FAILED->httpStatus());
    }

    public function test_unauthorized_http_status(): void
    {
        $this->assertEquals(401, ErrorCode::UNAUTHORIZED->httpStatus());
        $this->assertEquals(401, ErrorCode::API_KEY_MISSING->httpStatus());
        $this->assertEquals(401, ErrorCode::API_KEY_INVALID->httpStatus());
    }

    public function test_forbidden_http_status(): void
    {
        $this->assertEquals(403, ErrorCode::FORBIDDEN->httpStatus());
        $this->assertEquals(403, ErrorCode::API_KEY_INSUFFICIENT_SCOPES->httpStatus());
        $this->assertEquals(403, ErrorCode::IP_RESTRICTED->httpStatus());
    }

    public function test_rate_limited_http_status(): void
    {
        $this->assertEquals(429, ErrorCode::RATE_LIMITED->httpStatus());
        $this->assertEquals(429, ErrorCode::BRUTE_FORCE_BLOCKED->httpStatus());
        $this->assertEquals(429, ErrorCode::TIER_LIMIT_EXCEEDED->httpStatus());
    }

    public function test_method_not_allowed_http_status(): void
    {
        $this->assertEquals(405, ErrorCode::METHOD_NOT_ALLOWED->httpStatus());
    }

    public function test_invalid_content_type_http_status(): void
    {
        $this->assertEquals(415, ErrorCode::INVALID_CONTENT_TYPE->httpStatus());
        $this->assertEquals(415, ErrorCode::REQUEST_TOO_LARGE->httpStatus());
    }

    public function test_server_error_http_status(): void
    {
        $this->assertEquals(500, ErrorCode::SERVER_ERROR->httpStatus());
        $this->assertEquals(500, ErrorCode::INTERNAL_ERROR->httpStatus());
        $this->assertEquals(500, ErrorCode::SUSPICIOUS_INPUT->httpStatus());
        $this->assertEquals(500, ErrorCode::CORS_VIOLATION->httpStatus());
        $this->assertEquals(500, ErrorCode::SIGNATURE_INVALID->httpStatus());
    }

    public function test_service_unavailable_http_status(): void
    {
        $this->assertEquals(503, ErrorCode::SERVICE_UNAVAILABLE->httpStatus());
    }

    public function test_all_codes_have_string_values(): void
    {
        foreach (ErrorCode::cases() as $code) {
            $this->assertIsString($code->value);
            $this->assertStringStartsWith('ERR_', $code->value);
        }
    }

    public function test_all_codes_have_labels(): void
    {
        foreach (ErrorCode::cases() as $code) {
            $label = $code->label();
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }
}
