<?php

namespace Tests\FrameworkCore\Unit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Infrastructure\FrameworkCore\Http\Middleware\ResponseCacheMiddleware;
use Tests\FrameworkCore\FrameworkCoreTestCase;

class ResponseCacheMiddlewareTest extends FrameworkCoreTestCase
{
    public function test_middleware_can_be_instantiated(): void
    {
        $middleware = new ResponseCacheMiddleware();
        $this->assertInstanceOf(ResponseCacheMiddleware::class, $middleware);
    }

    public function test_middleware_passes_non_get_requests_through(): void
    {
        $middleware = new ResponseCacheMiddleware();
        $request = Request::create('/api/users', 'POST');
        $response = new Response('Created', 201);

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->handle($request, $next);

        $this->assertEquals(201, $result->getStatusCode());
    }

    public function test_middleware_adds_cache_headers_on_success(): void
    {
        $middleware = new ResponseCacheMiddleware();
        $request = Request::create('/api/users', 'GET');
        $response = new Response('OK', 200);

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->handle($request, $next);

        $this->assertTrue($result->headers->has('X-Cache'));
    }

    public function test_build_cache_key_generates_consistent_keys(): void
    {
        $middleware = new ResponseCacheMiddleware();
        $request = Request::create('/api/users?page=1&per_page=10', 'GET');

        $reflection = new \ReflectionClass($middleware);
        $method = $reflection->getMethod('buildCacheKey');
        $method->setAccessible(true);

        $key1 = $method->invoke($middleware, $request);
        $key2 = $method->invoke($middleware, $request);

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('response:', $key1);
    }
}
