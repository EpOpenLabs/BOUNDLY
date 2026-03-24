<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Services\Logging\ContextLogBuilder;
use Infrastructure\FrameworkCore\Services\Logging\RequestLogBuilder;
use Infrastructure\FrameworkCore\Services\Logging\StructuredLogger;
use Infrastructure\FrameworkCore\Services\Logging\UserLogBuilder;
use Tests\FrameworkCore\FrameworkCoreTestCase;

class StructuredLoggerTest extends FrameworkCoreTestCase
{
    public function test_logger_can_be_instantiated(): void
    {
        $logger = new StructuredLogger;
        $this->assertInstanceOf(StructuredLogger::class, $logger);
    }

    public function test_with_request_returns_builder(): void
    {
        $logger = new StructuredLogger;
        $request = request();

        $builder = $logger->withRequest($request);

        $this->assertInstanceOf(RequestLogBuilder::class, $builder);
    }

    public function test_with_user_returns_builder(): void
    {
        $logger = new StructuredLogger;

        $builder = $logger->withUser('user_123');

        $this->assertInstanceOf(UserLogBuilder::class, $builder);
    }

    public function test_with_context_returns_builder(): void
    {
        $logger = new StructuredLogger;

        $builder = $logger->withContext(['key' => 'value']);

        $this->assertInstanceOf(ContextLogBuilder::class, $builder);
    }

    public function test_channel_returns_new_instance(): void
    {
        $logger = new StructuredLogger;

        $newLogger = $logger->channel('daily');

        $this->assertInstanceOf(StructuredLogger::class, $newLogger);
        $this->assertNotSame($logger, $newLogger);
    }

    public function test_user_builder_accepts_null_user_id(): void
    {
        $logger = new StructuredLogger;

        $builder = $logger->withUser(null);

        $this->assertInstanceOf(UserLogBuilder::class, $builder);
    }
}
