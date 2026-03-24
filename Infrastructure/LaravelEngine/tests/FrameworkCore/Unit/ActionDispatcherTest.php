<?php

namespace Tests\FrameworkCore\Unit;

use PHPUnit\Framework\TestCase;
use Infrastructure\FrameworkCore\Registry\ActionRegistry;
use Infrastructure\FrameworkCore\Dispatchers\ActionDispatcher;

class ActionDispatcherTest extends TestCase
{
    private ActionRegistry $registry;
    private ActionDispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new ActionRegistry();
        $this->dispatcher = new ActionDispatcher($this->registry);
    }

    public function test_dispatch_returns_null_when_no_action_registered(): void
    {
        $request = new \Illuminate\Http\Request();
        
        $result = $this->dispatcher->dispatch('nonexistent', 'GET', $request);
        
        $this->assertNull($result);
    }

    public function test_dispatch_returns_null_for_undefined_http_method(): void
    {
        $request = new \Illuminate\Http\Request();
        
        $result = $this->dispatcher->dispatch('users', 'CUSTOM', $request);
        
        $this->assertNull($result);
    }
}
