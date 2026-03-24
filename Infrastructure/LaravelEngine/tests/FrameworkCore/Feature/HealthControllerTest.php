<?php

namespace Tests\FrameworkCore\Feature;

use Illuminate\Http\JsonResponse;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckInterface;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckResult;
use Infrastructure\FrameworkCore\Http\Controllers\HealthController;
use Infrastructure\FrameworkCore\Services\Health\HealthCheckService;
use Mockery;
use Tests\FrameworkCore\FrameworkCoreTestCase;

class HealthControllerTest extends FrameworkCoreTestCase
{
    protected $healthService;

    protected HealthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->healthService = Mockery::mock(HealthCheckService::class);
        $this->controller = new HealthController($this->healthService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_check_returns_healthy_when_all_services_pass(): void
    {
        $this->healthService
            ->shouldReceive('isHealthy')
            ->once()
            ->andReturn(true);

        $response = $this->controller->check();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('healthy', $data['data']['status']);
        $this->assertArrayHasKey('timestamp', $data['data']);
    }

    public function test_check_returns_unhealthy_when_services_fail(): void
    {
        $this->healthService
            ->shouldReceive('isHealthy')
            ->once()
            ->andReturn(false);

        $response = $this->controller->check();

        $this->assertEquals(503, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function test_detailed_check_returns_all_checks(): void
    {
        $this->healthService
            ->shouldReceive('runAll')
            ->once()
            ->andReturn([
                'database' => HealthCheckResult::healthy('OK', null, 5.2),
                'cache' => HealthCheckResult::healthy('OK', null, 1.1),
            ]);

        $this->healthService
            ->shouldReceive('isHealthy')
            ->once()
            ->andReturn(true);

        request()->merge(['detailed' => true]);

        $response = $this->controller->check();

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('checks', $data);
        $this->assertArrayHasKey('database', $data['checks']);
        $this->assertArrayHasKey('cache', $data['checks']);
        $this->assertEquals('healthy', $data['checks']['database']['status']);
    }

    public function test_detailed_check_returns_503_when_unhealthy(): void
    {
        $this->healthService
            ->shouldReceive('runAll')
            ->once()
            ->andReturn([
                'database' => HealthCheckResult::unhealthy('Connection refused'),
            ]);

        $this->healthService
            ->shouldReceive('isHealthy')
            ->once()
            ->andReturn(false);

        request()->merge(['detailed' => true]);

        $response = $this->controller->check();

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function test_liveness_returns_alive(): void
    {
        $response = $this->controller->liveness();

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('alive', $data['data']['status']);
    }

    public function test_readiness_returns_ready_when_critical_healthy(): void
    {
        $this->healthService
            ->shouldReceive('isCriticalHealthy')
            ->once()
            ->andReturn(true);

        $response = $this->controller->readiness();

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('ready', $data['data']['status']);
    }

    public function test_readiness_returns_503_when_critical_unhealthy(): void
    {
        $this->healthService
            ->shouldReceive('isCriticalHealthy')
            ->once()
            ->andReturn(false);

        $response = $this->controller->readiness();

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function test_critical_only_checks(): void
    {
        $criticalCheck = Mockery::mock(HealthCheckInterface::class);

        $this->healthService
            ->shouldReceive('runAll')
            ->once()
            ->andReturn([
                'database' => HealthCheckResult::healthy('OK', null, 5.0),
                'cache' => HealthCheckResult::unhealthy('Redis down'),
            ]);

        $this->healthService
            ->shouldReceive('isHealthy')
            ->once()
            ->andReturn(false);

        $this->healthService
            ->shouldReceive('isCriticalHealthy')
            ->once()
            ->andReturn(true);

        $this->healthService
            ->shouldReceive('getCriticalChecks')
            ->once()
            ->andReturn(['database' => $criticalCheck]);

        request()->merge(['detailed' => true, 'critical_only' => true]);

        $response = $this->controller->check();

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('database', $data['checks']);
        $this->assertArrayNotHasKey('cache', $data['checks']);
    }
}
