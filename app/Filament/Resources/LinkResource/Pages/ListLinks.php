<?php

namespace App\Filament\Resources\LinkResource\Pages;

use App\Filament\Resources\LinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListLinks extends ListRecords
{
    protected static string $resource = LinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Создать ссылку')
                ->icon('heroicon-o-plus')
        ];
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return "Мои ссылки";
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
