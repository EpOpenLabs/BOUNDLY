<?php

namespace Domain\Posts\Tests;

use Infrastructure\FrameworkCore\Testing\BoundlyTestCase;

class PostApiTest extends BoundlyTestCase
{
    /**
     * Test mapping to GET /api/posts
     */
    public function test_can_list_items()
    {
        // $this->withoutMiddleware(\Infrastructure\FrameworkCore\Http\Middleware\ResourceAuthorize::class);

        $response = $this->getJson('/api/posts');
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    /**
     * Test mapping to POST /api/posts
     */
    public function test_can_create_item()
    {
        // $this->withoutMiddleware(\Infrastructure\FrameworkCore\Http\Middleware\ResourceAuthorize::class);

        $payload = [
'title' => 'Test String',
            'content' => 'Test String',
        ];

        $response = $this->postJson('/api/posts', $payload);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('posts', $payload);
    }
}