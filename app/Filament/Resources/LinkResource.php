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

//    protected static ?string $navigationIcon = 'heroicon-o-link';

//    protected static ?string $navigationLabel = null;
    protected static ?string $pluralModelLabel = 'Ссылки';
    protected static bool $shouldRegisterNavigation = false;

    protected static string|null $navigationGroup = null;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('url')
                    ->label('Оригинальный URL')
                    ->helperText('Введите свою ссылку')
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
                    ->label('Короткая ссылка')
                    ->copyable()
                    ->copyMessage('Скопировано в буфер обмена')
                    ->copyableState(fn(Link $link) => $link->getShortUrlAttribute())
                    ->formatStateUsing(fn($state) => route('link.redirect', $state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label('Оригинальная ссылка')
                    ->limit(50)
                    ->tooltip(fn(Link $record) => $record->url)
                    ->searchable(),

                TextColumn::make('clicks_count')
                    ->label('Кол-во переходов')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y')
                    ->sortable()
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Фильтровать с'),
                        DatePicker::make('created_to')
                            ->label('По')
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
                    ->label('Статистика для ссылки')
                    ->icon('heroicon-o-chart-bar')
                    ->color('primary')
                    ->modalWidth('7xl')
                    ->modalContent(fn(Link $link) => view('filament.modals.link-statistics', ['link' => $link]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрыть'),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Удалить ссылку?')
                    ->modalDescription('Вся статистика по этой ссылке так же будет удалена.')
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
            ->emptyStateHeading('Список ссылок пуст')
            ->emptyStateDescription('Создайте свою первую короткую ссылку')
            ->emptyStateIcon('heroicon-o-link')
            ->emptyStateActions([
                    CreateAction::make()
                        ->label('Создать ссылку')
                ]
            )
            ->paginationPageOptions([
                10,25,50,100
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
