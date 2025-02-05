<?php

namespace Tests\Feature;

use App\Friend;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FriendsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function aUserCanSendAFriendRequest(): void
    {
        $this->actingAs($user = factory(User::class)->create(), 'api');
        $targetUser = factory(User::class)->create();

        $response = $this->post('/api/friend-request', [
            'friend_id' => $targetUser->id,
        ]);

        $response->assertStatus(200);

        $friendRequest = Friend::first();

        $this->assertNotNull($friendRequest);
        $this->assertEquals($targetUser->id, $friendRequest->friend_id);
        $this->assertEquals($user->id, $friendRequest->user_id);

        $response->assertJson([
            'data' => [
                'type' => 'friend-request',
                'friend_request_id' => $friendRequest->id,
                'attributes' => [
                    'confirmed_at' => null,
                ],
            ],
            'links' => [
                'self' => url(sprintf('/users/%d', $targetUser->id)),
            ],
        ]);
    }

    /** @test */
    public function aUserCanSendAFriendRequestOnlyOnce(): void
    {
        $this->actingAs($user = factory(User::class)->create(), 'api');
        $targetUser = factory(User::class)->create();

        $this->post('/api/friend-request', [
            'friend_id' => $targetUser->id,
        ])->assertStatus(200);

        $this->post('/api/friend-request', [
            'friend_id' => $targetUser->id,
        ])->assertStatus(200);

        $friendRequests = Friend::all();
        $this->assertCount(1, $friendRequests);
    }

    /** @test */
    public function onlyValidUsersCanBeFriendRequested(): void
    {
        $this->actingAs($user = factory(User::class)->create(), 'api');

        $response = $this->post('/api/friend-request', [
            'friend_id' => 1337,
        ]);

        $response->assertStatus(404);
        $this->assertNull(Friend::first());
        $response->assertJson([
            'errors' => [
                'code' => 404,
                'title' => 'User Not Found',
                'detail' => 'Unable to locate the user with the given information.',
            ],
        ]);
    }

    /** @test */
    public function friendRequestsCanBeAccepted(): void
    {
        $this->actingAs($user = factory(User::class)->create(), 'api');
        $targetUser = factory(User::class)->create();

        $this->post('/api/friend-request', [
            'friend_id' => $targetUser->id,
        ])->assertStatus(200);

        $response = $this->actingAs($targetUser, 'api')
            ->post('/api/friend-request-response', [
                'user_id' => $user->id,
                'status' => 1,
            ]);

        $response->assertStatus(200);

        $friendRequest = Friend::first();

        $this->assertNotNull($friendRequest->confirmed_at);
        $this->assertInstanceOf(Carbon::class, $friendRequest->confirmed_at);
        $this->assertEquals(now()->startOfSecond(), $friendRequest->confirmed_at);
        $this->assertEquals(1, $friendRequest->status);

        $response->assertJson([
            'data' => [
                'type' => 'friend-request',
                'friend_request_id' => $friendRequest->id,
                'attributes' => [
                    'confirmed_at' => $friendRequest->confirmed_at->diffForHumans(),
                    'friend_id' => $friendRequest->friend_id,
                    'user_id' => $friendRequest->user_id,
                ],
            ],
            'links' => [
                'self' => url(sprintf('/users/%d', $targetUser->id)),
            ],
        ]);
    }

    /** @test */
    public function onlyValidFriendRequestsCanBeAccepted(): void
    {
        $targetUser = factory(User::class)->create();

        $response = $this->actingAs($targetUser, 'api')
            ->post('/api/friend-request-response', [
                'user_id' => 1337,
                'status' => 1,
            ]);

        $this->assertNull(Friend::first());

        $response->assertStatus(404)->assertJson([
            'errors' => [
                'code' => 404,
                'title' => 'Friend Request Not Found',
                'detail' => 'Unable to locate the friend request with the given information.',
            ],
        ]);
    }

    /** @test */
    public function onlyTheRecipientCanAcceptAFriendRequest(): void
    {
        $this->actingAs($user = factory(User::class)->create(), 'api');
        $targetUser = factory(User::class)->create();
        $strangerUser = factory(User::class)->create();

        $this->post('/api/friend-request', [
            'friend_id' => $targetUser->id,
        ])->assertStatus(200);

        $response = $this->actingAs($strangerUser, 'api')
            ->post('/api/friend-request-response', [
                'user_id' => $user->id,
                'status' => 1,
            ]);

        $response->assertStatus(404);

        $friendRequest = Friend::first();

        $this->assertNull($friendRequest->confirmed_at);
        $this->assertNull($friendRequest->status);
        $response->assertJson([
            'errors' => [
                'code' => 404,
                'title' => 'Friend Request Not Found',
                'detail' => 'Unable to locate the friend request with the given information.',
            ],
        ]);
    }

    /** @test */
    public function aFriendIdIsRequiredForFriendRequests(): void
    {
        $response = $this->actingAs($user = factory(User::class)->create(), 'api')
            ->post('/api/friend-request', [
                'friend_id' => '',
            ])->assertStatus(422);

        $responseArray = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('friend_id', $responseArray['errors']['meta']);
    }

    /** @test */
    public function aUserIdAndStatusIsRequiredForFriendRequestResponses(): void
    {
        $response = $this->actingAs($user = Factory(User::class)->create(), 'api')
            ->post('/api/friend-request-response', [
                'user_id' => '',
                'status' => '',
            ])->assertStatus(422);

        $responseArray = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('user_id', $responseArray['errors']['meta']);
        $this->assertArrayHasKey('status', $responseArray['errors']['meta']);
    }

    /** @test */
    public function aUserIdIsRequiredForIgnoringAFriendRequestResponses(): void
    {
        $response = $this->actingAs($user = Factory(User::class)->create(), 'api')
            ->delete('/api/friend-request-response/delete', [
                'user_id' => '',
            ])->assertStatus(422);

        $responseArray = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('user_id', $responseArray['errors']['meta']);
    }

    /** @test */
    public function aFriendshipIsRetrievedWhenFetchingTheProfile(): void
    {
        $this->actingAs($user = factory(User::class)->create(), 'api');
        $targetUser = factory(User::class)->create();
        $friendRequest = Friend::create([
            'user_id' => $user->id,
            'friend_id' => $targetUser->id,
            'confirmed_at' => now()->subDay(),
            'status' => 1,
        ]);

        $this->get(sprintf('/api/users/%d', $targetUser->id))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'attributes' => [
                        'friendship' => [
                            'data' => [
                                'friend_request_id' => $friendRequest->id,
                                'attributes' => [
                                    'confirmed_at' => '1 day ago',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function anInverseFriendshipIsRetrievedWhenFetchingTheProfile(): void
    {
        $this->actingAs($user = factory(User::class)->create(), 'api');
        $targetUser = factory(User::class)->create();
        $friendRequest = Friend::create([
            'user_id' => $targetUser->id,
            'friend_id' => $user->id,
            'confirmed_at' => now()->subDay(),
            'status' => 1,
        ]);

        $this->get(sprintf('/api/users/%d', $targetUser->id))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'attributes' => [
                        'friendship' => [
                            'data' => [
                                'friend_request_id' => $friendRequest->id,
                                'attributes' => [
                                    'confirmed_at' => '1 day ago',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function friendRequestsCanBeIgnored(): void
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = factory(User::class)->create(), 'api');
        $targetUser = factory(User::class)->create();

        $this->post('/api/friend-request', [
            'friend_id' => $targetUser->id,
        ])->assertStatus(200);

        $response = $this->actingAs($targetUser, 'api')
            ->delete('/api/friend-request-response/delete', [
                'user_id' => $user->id,
            ])->assertStatus(204);

        $friendRequest = Friend::first();

        $this->assertNull($friendRequest);
        $response->assertNoContent();
    }

    /** @test */
    public function onlyTheRecipientCanIgnoreAFriendRequest(): void
    {
        $this->actingAs($user = factory(User::class)->create(), 'api');
        $targetUser = factory(User::class)->create();
        $strangerUser = factory(User::class)->create();

        $this->post('/api/friend-request', [
            'friend_id' => $targetUser->id,
        ])->assertStatus(200);

        $response = $this->actingAs($strangerUser, 'api')
            ->delete('/api/friend-request-response/delete', [
                'user_id' => $user->id,
            ]);

        $response->assertStatus(404);

        $friendRequest = Friend::first();

        $this->assertNull($friendRequest->confirmed_at);
        $this->assertNull($friendRequest->status);
        $response->assertJson([
            'errors' => [
                'code' => 404,
                'title' => 'Friend Request Not Found',
                'detail' => 'Unable to locate the friend request with the given information.',
            ],
        ]);
    }
}
