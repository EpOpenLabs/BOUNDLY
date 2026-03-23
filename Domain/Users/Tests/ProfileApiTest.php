<?php

namespace Domain\Users\Tests;

use Infrastructure\FrameworkCore\Testing\BoundlyTestCase;

class ProfileApiTest extends BoundlyTestCase
{
    /**
     * Test mapping to GET /api/profiles
     */
    public function test_can_list_items()
    {
        // $this->withoutMiddleware(\Infrastructure\FrameworkCore\Http\Middleware\ResourceAuthorize::class);

        $response = $this->getJson('/api/profiles');
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    /**
     * Test mapping to POST /api/profiles
     */
    public function test_can_create_item()
    {
        // $this->withoutMiddleware(\Infrastructure\FrameworkCore\Http\Middleware\ResourceAuthorize::class);

        $payload = [
'avatar_url' => 'Test String',
            'bio' => 'Test String',
        ];

        $response = $this->postJson('/api/profiles', $payload);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('profiles', $payload);
    }
}