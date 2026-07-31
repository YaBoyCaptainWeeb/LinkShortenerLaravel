<?php

namespace App\Livewire;

use App\Models\Link;
use App\Support\TablePagination;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Component;

class LinkClicksTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Link $link;

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn(): HasMany => $this->link->clicks())
            ->defaultSort('clicked_at', 'desc')
            ->columns([
                ViewColumn::make('mobile_card')
                    ->label(__('links.statistics.history'))
                    ->view('filament.tables.columns.link-click-mobile')
                    ->extraCellAttributes([
                        'class' => 'w-full whitespace-normal',
                        'style' => 'width: 100%; max-width: 0; white-space: normal;',
                    ])
                    ->hiddenFrom('md'),

                TextColumn::make('ip_address')
                    ->label(__('links.statistics.ip_address'))
                    ->fontFamily('mono')
                    ->wrap()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('user_agent')
                    ->label(__('links.statistics.user_agent'))
                    ->wrap()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('clicked_at')
                    ->label(__('links.statistics.clicked_at'))
                    ->formatStateUsing(
                        fn($state): string => $state
                            ->locale(app()->getLocale())
                            ->translatedFormat(__('links.date_formats.date_time')),
                    )
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->header(view('filament.tables.link-click-mobile-sort'))
            ->paginated(TablePagination::OPTIONS)
            ->defaultPaginationPageOption(TablePagination::DEFAULT)
            ->emptyStateHeading(__('links.statistics.no_clicks'))
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }

    public function render(): View
    {
        return view('livewire.link-clicks-table');
    }
}
