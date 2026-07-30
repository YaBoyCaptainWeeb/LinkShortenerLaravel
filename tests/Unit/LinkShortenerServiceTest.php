<?php

namespace Tests\Unit;

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
     * Тест 1: Проверяем успешное создание короткой ссылки в обычном режиме.
     *
     * @throws RandomException
     */
    public function test_create_a_short_link_successfully(): void
    {
        $user = User::factory()->create();
        $url = 'https://google.com';
        $service = new LinkShortenerService();

        $link = $service->createShortLink($user, $url);

        $this->assertEquals($url, $link->url);

        $this->assertEquals(6, strlen($link->code));

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'url' => $url,
            'code' => $link->code,
            'user_id' => $user->id,
        ]);
    }

}
