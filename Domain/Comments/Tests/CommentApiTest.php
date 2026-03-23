<?php

namespace Domain\Comments\Tests;

use Infrastructure\FrameworkCore\Testing\BoundlyTestCase;
use Domain\Users\Entities\User;
use Domain\Posts\Entities\Post;

class CommentApiTest extends BoundlyTestCase
{
    public function test_can_create_polymorphic_comments()
    {
        // 1. Create a user
        $userResponse = $this->postJson('/api/users', [
            'name'     => 'Commenter User',
            'email'    => 'commenter@example.com',
            'password' => 'secret123'
        ]);
        $userId = $userResponse->json('data.original.data.id') ?? $userResponse->json('data.id');

        // 2. Create a post
        $postResponse = $this->postJson('/api/posts', [
            'title' => 'My first post'
        ]);
        $postId = $postResponse->json('data.id');

        $respUserComment = $this->postJson('/api/comments', [
            'content'          => 'Nice profile!',
            'commentable_id'   => $userId,
            'commentable_type' => User::class
        ]);
        $respUserComment->assertStatus(201);

        // 4. Comment on Post
        $respPostComment = $this->postJson('/api/comments', [
            'content'          => 'Interesting article.',
            'commentable_id'   => $postId,
            'commentable_type' => Post::class
        ]);
        $respPostComment->assertStatus(201);

        // 5. Verify Database
        $this->assertDatabaseHas('comments', [
            'content'          => 'Nice profile!',
            'commentable_id'   => $userId,
            'commentable_type' => User::class
        ]);

        $this->assertDatabaseHas('comments', [
            'content'          => 'Interesting article.',
            'commentable_id'   => $postId,
            'commentable_type' => Post::class
        ]);
    }

    public function test_can_load_morph_many_relations()
    {
        // Setup
        $post = $this->postJson('/api/posts', ['title' => 'Polymorphic Post'])->json('data');
        
        $this->postJson('/api/comments', [
            'content'          => 'First comment',
            'commentable_id'   => $post['id'],
            'commentable_type' => Post::class
        ]);

        $this->postJson('/api/comments', [
            'content'          => 'Second comment',
            'commentable_id'   => $post['id'],
            'commentable_type' => Post::class
        ]);

        // Fetch post with comments
        $response = $this->getJson('/api/posts/' . $post['id'] . '?include=comments');
        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data.comments')
                 ->assertJsonFragment(['content' => 'First comment'])
                 ->assertJsonFragment(['content' => 'Second comment']);
    }

    public function test_can_load_morph_to_relation()
    {
        $post = $this->postJson('/api/posts', ['title' => 'Parent Post'])->json('data');
        
        $comment = $this->postJson('/api/comments', [
            'content'          => 'Individual comment',
            'commentable_id'   => $post['id'],
            'commentable_type' => Post::class
        ])->json('data');

        // Fetch comment with its parent (morphTo)
        $response = $this->getJson('/api/comments/' . $comment['id'] . '?include=commentable');
        $response->assertStatus(200)
                 ->assertJsonPath('data.commentable.title', 'Parent Post');
    }
}