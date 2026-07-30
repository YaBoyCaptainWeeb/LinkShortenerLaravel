<?php

namespace Tests\Feature;

use App\Enums\AppLocale;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_links_table_renders_the_responsive_layout_in_both_locales(): void
    {
        foreach (AppLocale::cases() as $locale) {
            $user = User::factory()->create([
                'locale' => $locale,
            ]);
            $link = Link::create([
                'user_id' => $user->id,
                'code' => "code-{$locale->value}",
                'url' => "https://example.com/{$locale->value}",
            ]);

            $response = $this->actingAs($user)
                ->get('/panel/links');

            $response
                ->assertOk()
                ->assertSee($link->code)
                ->assertSee('link-mobile-card', escape: false)
                ->assertSee('md:hidden', escape: false)
                ->assertSee('hidden md:table-cell', escape: false)
                ->assertSee('max-width: 0; white-space: normal;', escape: false)
                ->assertSee('overflow-wrap: anywhere; word-break: break-word;', escape: false)
                ->assertSee('data-short-url', escape: false)
                ->assertDontSee('@js($shortUrl)', escape: false)
                ->assertSee(__('links.actions.statistics'))
                ->assertSee(__('links.actions.delete'))
                ->assertDontSee('statistics_mobile', escape: false)
                ->assertDontSee('delete_mobile', escape: false)
                ->assertDontSee('hidden md:inline-flex', escape: false)
                ->assertDontSee('links.table.link')
                ->assertDontSee('links.actions.copy')
                ->assertDontSee('links.actions.delete')
                ->assertDontSee('fi-ta-split', escape: false);
        }
    }
}
