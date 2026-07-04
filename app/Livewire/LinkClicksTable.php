<?php

namespace App\Livewire;

use App\Models\Link;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class LinkClicksTable extends Component
{
    use WithPagination;

    public Link $link;

    public function render(): View
    {
        return view('livewire.link-clicks-table', [
            'clicks' => $this->link->clicks()
                ->orderByDesc('clicked_at')
                ->paginate(10)
        ]);
    }
}
