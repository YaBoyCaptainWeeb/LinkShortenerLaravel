<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\User;
use App\Services\LinkShortenerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Random\RandomException;
use Tests\TestCase;

class LinkShortenerServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws RandomException
     */
    public function test_create_a_short_link_successfully(): void
    {
        $user = User::factory()->create();
        $url = 'http://127.0.0.1:1/example';
        $service = new LinkShortenerService();

        $link = $service->createShortLink($user, $url);

        $this->assertSame($url, $link->url);
        $this->assertSame(6, strlen($link->code));

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'url' => $url,
            'code' => $link->code,
            'user_id' => $user->id,
        ]);
    }
}
