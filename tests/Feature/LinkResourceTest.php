<?php

namespace Tests\Feature;

use App\Filament\Resources\LinkResource\Pages\ListLinks;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_mobile_sort_control_sorts_the_same_records_as_the_desktop_columns(): void
    {
        $user = User::factory()->create();

        $lessPopularLink = Link::create([
            'user_id' => $user->id,
            'code' => 'less-popular',
            'url' => 'https://example.com/less-popular',
            'clicks_count' => 5,
        ]);
        $morePopularLink = Link::create([
            'user_id' => $user->id,
            'code' => 'more-popular',
            'url' => 'https://example.com/more-popular',
            'clicks_count' => 50,
        ]);

        Livewire::actingAs($user)
            ->test(ListLinks::class)
            ->assertSee(__('links.sorting.label'))
            ->assertSeeHtml('<option value="code">')
            ->assertSeeHtml('<option value="clicks_count">')
            ->assertSeeHtml('<option value="created_at">')
            ->call('sortTable', 'clicks_count', 'desc')
            ->assertCanSeeTableRecords(
                [$morePopularLink, $lessPopularLink],
                inOrder: true,
            );
    }

    public function test_statistics_modal_does_not_force_mobile_scroll_constraints(): void
    {
        $user = User::factory()->create();

        $statisticsAction = Livewire::actingAs($user)
            ->test(ListLinks::class)
            ->instance()
            ->getTable()
            ->getAction('statistics');

        $this->assertFalse($statisticsAction->isModalHeaderSticky());
        $this->assertFalse($statisticsAction->isModalFooterSticky());
        $this->assertSame(
            ['class' => 'link-statistics-modal-window min-w-0'],
            $statisticsAction->getExtraModalWindowAttributes(),
        );
    }
}
