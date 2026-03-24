<?php

namespace Tests\FrameworkCore\Unit;

use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Services\Security\RequestSigningService;
use Tests\FrameworkCore\FrameworkCoreTestCase;

class RequestSigningServiceTest extends FrameworkCoreTestCase
{
    public function test_can_be_instantiated(): void
    {
        $service = new RequestSigningService();
        $this->assertInstanceOf(RequestSigningService::class, $service);
    }

    public function test_is_enabled_returns_bool(): void
    {
        $service = new RequestSigningService();
        $this->assertIsBool($service->isEnabled());
    }

    public function test_get_algorithm_returns_string(): void
    {
        $service = new RequestSigningService();
        $this->assertIsString($service->getAlgorithm());
    }

    public function test_get_timestamp_tolerance_returns_int(): void
    {
        $service = new RequestSigningService();
        $this->assertIsInt($service->getTimestampTolerance());
    }

    public function test_sign_request_returns_string(): void
    {
        $service = new RequestSigningService();
        $request = Request::create('/api/users', 'GET');

        $signature = $service->signRequest($request);

        $this->assertIsString($signature);
        $this->assertNotEmpty($signature);
    }

    public function test_verify_signature_returns_bool(): void
    {
        $service = new RequestSigningService();
        $request = Request::create('/api/users', 'GET');

        $result = $service->verifySignature($request);

        $this->assertIsBool($result);
    }

    public function test_generate_headers_returns_array(): void
    {
        $service = new RequestSigningService();
        $request = Request::create('/api/users', 'POST');

        $headers = $service->generateHeaders($request);

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('X-Timestamp', $headers);
        $this->assertArrayHasKey('X-Signature', $headers);
    }

    public function test_same_request_produces_same_signature(): void
    {
        $service = new RequestSigningService();
        $request = Request::create('/api/users', 'GET', [], [], [], [
            'HTTP_X_TIMESTAMP' => '1234567890',
        ]);

        $signature1 = $service->signRequest($request);
        $signature2 = $service->signRequest($request);

        $this->assertEquals($signature1, $signature2);
    }
}
