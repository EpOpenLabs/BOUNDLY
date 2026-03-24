<?php

namespace Tests\FrameworkCore\Unit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Infrastructure\FrameworkCore\Http\Middleware\RequestLoggerMiddleware;
use Tests\FrameworkCore\FrameworkCoreTestCase;

class RequestLoggerMiddlewareTest extends FrameworkCoreTestCase
{
    public function test_middleware_can_be_instantiated(): void
    {
        $middleware = new RequestLoggerMiddleware;
        $this->assertInstanceOf(RequestLoggerMiddleware::class, $middleware);
    }

    public function test_middleware_passes_request_through(): void
    {
        $middleware = new RequestLoggerMiddleware;
        $request = Request::create('/api/users', 'GET');
        $response = new Response('OK', 200);

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->handle($request, $next);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function test_middleware_adds_request_id_header(): void
    {
        $middleware = new RequestLoggerMiddleware;
        $request = Request::create('/api/users', 'GET');
        $response = new Response('OK', 200);

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->handle($request, $next);

        $this->assertTrue($result->headers->has('X-Request-ID'));
        $this->assertTrue($result->headers->has('X-Response-Time'));
    }

    public function test_middleware_preserves_existing_request_id(): void
    {
        $middleware = new RequestLoggerMiddleware;
        $request = Request::create('/api/users', 'GET');
        $request->headers->set('X-Request-ID', 'custom-request-id');
        $response = new Response('OK', 200);

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->handle($request, $next);

        $this->assertEquals('custom-request-id', $result->headers->get('X-Request-ID'));
    }

    public function test_middleware_excludes_health_endpoint(): void
    {
        $middleware = new RequestLoggerMiddleware;
        $request = Request::create('/api/health', 'GET');
        $response = new Response('OK', 200);

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->handle($request, $next);

        $this->assertFalse($result->headers->has('X-Request-ID'));
    }
}
