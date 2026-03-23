<?php

namespace Domain\Users\Tests;

use Infrastructure\FrameworkCore\Testing\BoundlyTestCase;

class UserApiTest extends BoundlyTestCase
{
    /**
     * Test mapping to GET /api/users
     */
    public function test_can_list_items()
    {
        // $this->withoutMiddleware(\Infrastructure\FrameworkCore\Http\Middleware\ResourceAuthorize::class);

        $response = $this->getJson('/api/users');
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    /**
     * Test mapping to POST /api/users
     */
    public function test_can_create_item()
    {
        // $this->withoutMiddleware(\Infrastructure\FrameworkCore\Http\Middleware\ResourceAuthorize::class);

        $payload = [
'name' => 'Test String',
            'email' => 'test@example.com',
            'phone' => 'Test String',
            'addres' => 'Test String',
            'password' => 'password',
        ];

        $response = $this->postJson('/api/users', $payload);
        
        $response->assertStatus(201);
    }
}