<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LinkResource\Pages;
use App\Filament\Resources\LinkResource\RelationManagers;
use App\Models\Link;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class LinkResource extends Resource
{
    protected static ?string $model = Link::class;

    public static function getPluralModelLabel(): string
    {
        return __('links.resource.plural_label');
    }
    public static function getModelLabel(): string
    {
        return __('links.resource.label');
    }

    protected
    static bool $shouldRegisterNavigation = false;

    protected static string|null $navigationGroup = null;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('url')
                    ->label(__('links.form.original_url'))
                    ->helperText(__('links.form.original_url_hint'))
                    ->url()
                    ->required()
                    ->maxLength(2048)
                    ->columnSpanFull()
                    ->placeholder('https://example.com/example/long')
                    ->live(onBlur: true)
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('user_id', auth()->id());
            })
            ->columns([
                TextColumn::make('code')
                    ->label(__('links.table.short_url'))
                    ->copyable()
                    ->tooltip(fn(Link $link) => $link->getShortUrlAttribute())
                    ->copyMessage(__('links.table.copy_success'))
                    ->copyableState(fn(Link $link) => $link->getShortUrlAttribute())
                    ->formatStateUsing(fn($state) => route('link.redirect', $state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label(__('links.table.original_url'))
                    ->limit(50)
                    ->tooltip(fn(Link $record) => $record->url)
                    ->searchable(),

                TextColumn::make('clicks_count')
                    ->label(__('links.table.clicks_count'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label(__('links.table.created_at'))
                    ->dateTime('d.m.Y')
                    ->sortable()
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('links.filters.from')),
                        DatePicker::make('created_to')
                            ->label(__('links.filters.until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    })
            ])
            ->actions([
                Action::make('statistics')
                    ->label(__('links.actions.statistics'))
                    ->icon('heroicon-o-chart-bar')
                    ->color('primary')
                    ->modalWidth('7xl')
                    ->modalContent(fn(Link $link) => view('filament.modals.link-statistics', ['link' => $link]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('links.actions.close')),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading(__('links.delete.heading'))
                    ->modalDescription(__('links.delete.description'))
                    ->color('warning')
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->color('danger'),
                ]),
            ])
            ->headerActions([])
            ->emptyStateHeading(__('links.empty.heading'))
            ->emptyStateDescription(__('links.empty.description'))
            ->emptyStateIcon('heroicon-o-link')
            ->emptyStateActions([
                    CreateAction::make()
                        ->label(__('links.actions.create'))
                ]
            )
            ->paginationPageOptions([
                10, 25, 50, 100
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLinks::route('/'),
            'create' => Pages\CreateLink::route('/create')
        ];
    }
}
