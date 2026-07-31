<?php

namespace App\Filament\Resources\LinkResource\Pages;

use App\Filament\Resources\LinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListLinks extends ListRecords
{
    protected static string $resource = LinkResource::class;

    protected ?string $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('links.actions.create'))
                ->icon('heroicon-o-plus')
        ];
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('links.pages.list.heading');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
