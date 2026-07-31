<?php

namespace Tests\Feature;

use App\Livewire\LinkClicksTable;
use App\Models\Link;
use App\Models\LinkClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LinkStatisticsModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistics_modal_renders_the_selected_link_and_its_click_table(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->for($user)->create([
            'code' => 'modal-smoke-test',
        ]);
        $click = LinkClick::factory()->for($link)->create();

        $this->actingAs($user)
            ->view('filament.modals.link-statistics', ['link' => $link])
            ->assertSee(route('link.redirect', ['code' => $link->code]))
            ->assertSee($click->ip_address);
    }

    public function test_click_table_only_shows_clicks_for_the_selected_link_in_latest_first_order(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->for($user)->create();
        $otherLink = Link::factory()->for($user)->create();

        $olderClick = LinkClick::factory()->for($link)->create([
            'clicked_at' => now()->subMinute(),
        ]);
        $newerClick = LinkClick::factory()->for($link)->create([
            'clicked_at' => now(),
        ]);
        $otherLinkClick = LinkClick::factory()->for($otherLink)->create();

        Livewire::actingAs($user)
            ->test(LinkClicksTable::class, ['link' => $link])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$newerClick, $olderClick], inOrder: true)
            ->assertCanNotSeeTableRecords([$otherLinkClick]);
    }

    public function test_click_table_switches_between_mobile_and_desktop_columns_at_the_md_breakpoint(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->for($user)->create();

        $table = Livewire::actingAs($user)
            ->test(LinkClicksTable::class, ['link' => $link])
            ->instance()
            ->getTable();

        $this->assertSame('md', $table->getColumn('mobile_card')->getHiddenFrom());
        $this->assertSame('md', $table->getColumn('ip_address')->getVisibleFrom());
        $this->assertSame('md', $table->getColumn('user_agent')->getVisibleFrom());
        $this->assertSame('md', $table->getColumn('clicked_at')->getVisibleFrom());
    }
}
