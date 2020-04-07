<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetAuthUserTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthenticatedUserCanBeFetched(): void
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = factory(User::class)->create(), 'api');

        $response = $this->get('/api/auth-user');

        $response->assertStatus(200)->assertJson([
            'data' => [
                'attributes' => [
                    'name' => $user->name,
                ],
                'user_id' => $user->id,
            ],
            'links' => [
                'self' => url(sprintf('users/%d', $user->id)),
            ],
        ]);
    }
}
