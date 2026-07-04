<?php

namespace Tests\Unit;

use App\Exceptions\LinkGenerationException;
use App\Models\Link;
use App\Models\User;
use App\Services\LinkShortenerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Random\RandomException;

class LinkShortenerServiceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Тест 1: Проверяем успешное создание короткой ссылки в обычном режиме.
     *
     * @throws RandomException
     * @throws LinkGenerationException
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

    /**
     * Тест 2: Проверяем, как система реагирует на вызов исключения.
     *
     * @throws RandomException
     * @throws LinkGenerationException
     */
    public function test_expects_link_generation_exception(): void
    {
        $user = User::factory()->create();
        $service = new LinkShortenerService();

        $this->expectException(LinkGenerationException::class);

        $service->createShortLink($user, 'https://example.com');
    }
}
