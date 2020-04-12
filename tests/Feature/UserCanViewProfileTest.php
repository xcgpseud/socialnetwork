<?php

namespace Tests\Feature;

use App\Post;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserCanViewProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function aUserCanViewUserProfile(): void
    {
        $this->actingAs($user = factory(User::class)->create(), 'api');
        $post = factory(Post::class)->create();

        $response = $this->get(sprintf('/api/users/%d', $user->id));

        $response->assertStatus(200)->assertJson([
            'data' => [
                'type' => 'users',
                'user_id' => $user->id,
                'attributes' => [
                    'name' => $user->name
                ],
            ],
            'links' => [
                'self' => url(sprintf('/users/%d', $user->id)),
            ],
        ]);
    }

    /** @test */
    public function aUserCanFetchPostsForAProfile(): void
    {
        $this->actingAs($user = factory(User::class)->create(), 'api');
        $post = factory(Post::class)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get(sprintf('/api/users/%d/posts', $user->id));

        $response->assertStatus(200)->assertJson([
            'data' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'post_id' => $post->id,
                        'attributes' => [
                            'body' => $post->body,
                            'image' => $post->image,
                            'posted_at' => $post->created_at->diffForHumans(),
                            'posted_by' => [
                                'data' => [
                                    'attributes' => [
                                        'name' => $user->name,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'links' => [
                        'self' => url(sprintf('/posts/%d', $post->id)),
                    ],
                ],
            ],
        ]);
    }
}
