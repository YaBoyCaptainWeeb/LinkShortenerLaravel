<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_links_table_only_shows_the_authenticated_users_links(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownLink = Link::create([
            'user_id' => $user->id,
            'code' => 'users-own-link',
            'url' => 'https://example.com/users-own-link',
        ]);
        $otherLink = Link::create([
            'user_id' => $otherUser->id,
            'code' => 'another-users-link',
            'url' => 'https://example.com/another-users-link',
        ]);

        $this->actingAs($user)
            ->get('/panel/links')
            ->assertOk()
            ->assertSee($ownLink->code)
            ->assertDontSee($otherLink->code);
    }
}
