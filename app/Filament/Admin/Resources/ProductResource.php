<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Application\Catalog\Commands\BulkChangeProductStatusCommand;
use App\Application\Catalog\Commands\BulkChangeProductStatusCommandHandler;
use App\Application\Catalog\Commands\ChangeProductStatusCommand;
use App\Application\Catalog\Commands\ChangeProductStatusCommandHandler;
use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\ProductModel;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = ProductModel::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $modelLabel = 'Produkt';

    protected static ?string $pluralModelLabel = 'Produkte';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('ProductTabs')
                    ->tabs([
                        Tab::make('Stammdaten')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Produktname')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('sku')
                                            ->label('Basis-SKU')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(100)
                                            ->regex('/^[A-Za-z0-9\-_.]+$/')
                                            ->validationMessages([
                                                'regex' => 'Die SKU darf nur Buchstaben, Zahlen, Bindestriche, Unterstriche und Punkte enthalten (keine Leerzeichen).',
                                            ]),

                                        Forms\Components\Select::make('category_id')
                                            ->label('Kategorie')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),

                                        Forms\Components\Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'draft' => 'Entwurf',
                                                'active' => 'Aktiv',
                                                'archived' => 'Archiviert',
                                            ])
                                            ->default('draft')
                                            ->required(),

                                        Forms\Components\TextInput::make('base_price_cents')
                                            ->label('Basispreis (€)')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0)
                                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : 0.0)
                                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),

                                        Forms\Components\TextInput::make('currency')
                                            ->label('Währung')
                                            ->default('EUR')
                                            ->disabled(),
                                    ]),
                            ]),

                        Tab::make('Varianten')
                            ->icon('heroicon-o-swatch')
                            ->badge(fn ($record) => $record?->variants()->count() ?: null)
                            ->schema([
                                Forms\Components\Repeater::make('variants')
                                    ->relationship('variants')
                                    ->label('Produktvarianten')
                                    ->addActionLabel('Variante hinzufügen')
                                    ->columns(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('sku')
                                            ->label('Varianten-SKU')
                                            ->required()
                                            ->maxLength(100)
                                            ->regex('/^[A-Za-z0-9\-_.]+$/')
                                            ->validationMessages([
                                                'regex' => 'Die SKU darf nur Buchstaben, Zahlen, Bindestriche, Unterstriche und Punkte enthalten.',
                                            ]),

                                        Forms\Components\TextInput::make('size')
                                            ->label('Größe (z.B. S, M, L, 42)')
                                            ->required(),

                                        Forms\Components\TextInput::make('color_name')
                                            ->label('Farbe (z.B. Schwarz, Rot)')
                                            ->required(),

                                        Forms\Components\ColorPicker::make('color_hex')
                                            ->label('Farbcode (Hex)')
                                            ->regex('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'),

                                        Forms\Components\TextInput::make('price_cents')
                                            ->label('Abweichender Preis (€)')
                                            ->numeric()
                                            ->nullable()
                                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                                            ->dehydrateStateUsing(fn ($state) => $state !== null ? (int) round(((float) $state) * 100) : null),
                                    ]),
                            ]),

                        Tab::make('Medien')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\Repeater::make('media')
                                    ->relationship('media')
                                    ->label('Produktbilder')
                                    ->addActionLabel('Bild hinzufügen')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('url')
                                            ->label('Bild-URL / Pfad')
                                            ->required(),

                                        Forms\Components\TextInput::make('alt_text')
                                            ->label('Alt-Text'),

                                        Forms\Components\Toggle::make('is_primary')
                                            ->label('Hauptbild')
                                            ->default(false),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Produktname')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategorie')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('Keine Kategorie'),

                // Inline Editing for Status
                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Entwurf',
                        'active' => 'Aktiv',
                        'archived' => 'Archiviert',
                    ])
                    ->afterStateUpdated(function ($record, $state) {
                        try {
                            /** @var ChangeProductStatusCommandHandler $handler */
                            $handler = app(ChangeProductStatusCommandHandler::class);
                            $handler->handle(new ChangeProductStatusCommand($record->id, $state));

                            Notification::make()
                                ->title('Status erfolgreich aktualisiert')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Statusänderung fehlgeschlagen')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->sortable(),

                // Inline Editing for Base Price
                Tables\Columns\TextInputColumn::make('base_price_cents')
                    ->label('Basispreis (€)')
                    ->type('number')
                    ->sortable()
                    ->afterStateUpdated(function ($record, $state) {
                        Notification::make()
                            ->title('Basispreis aktualisiert')
                            ->success()
                            ->send();
                    }),

                Tables\Columns\TextColumn::make('variants_count')
                    ->label('Varianten')
                    ->counts('variants')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategorie')
                    ->relationship('category', 'name')
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Entwurf',
                        'active' => 'Aktiv',
                        'archived' => 'Archiviert',
                    ]),
            ])
            ->actions([
                Action::make('activate')
                    ->label('Aktivieren')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ProductModel $record): bool => $record->status?->value !== 'active')
                    ->action(function (ProductModel $record, ChangeProductStatusCommandHandler $handler) {
                        try {
                            $handler->handle(new ChangeProductStatusCommand($record->id, 'active'));

                            Notification::make()
                                ->title('Produkt aktiviert')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Aktivierung fehlgeschlagen')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('archive')
                    ->label('Archivieren')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (ProductModel $record): bool => $record->status?->value !== 'archived')
                    ->action(function (ProductModel $record, ChangeProductStatusCommandHandler $handler) {
                        try {
                            $handler->handle(new ChangeProductStatusCommand($record->id, 'archived'));

                            Notification::make()
                                ->title('Produkt archiviert')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Archivierung fehlgeschlagen')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkActivate')
                        ->label('Ausgewählte aktivieren')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records, BulkChangeProductStatusCommandHandler $handler) {
                            $ids = $records->pluck('id')->all();
                            $result = $handler->handle(new BulkChangeProductStatusCommand($ids, 'active'));

                            if ($result['successCount'] > 0) {
                                Notification::make()
                                    ->title("{$result['successCount']} Produkt(e) erfolgreich aktiviert")
                                    ->success()
                                    ->send();
                            }

                            if (! empty($result['errors'])) {
                                $errorMsg = implode('; ', $result['errors']);
                                Notification::make()
                                    ->title('Einige Produkte konnten nicht aktiviert werden')
                                    ->body($errorMsg)
                                    ->warning()
                                    ->send();
                            }
                        }),

                    BulkAction::make('bulkArchive')
                        ->label('Ausgewählte archivieren')
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records, BulkChangeProductStatusCommandHandler $handler) {
                            $ids = $records->pluck('id')->all();
                            $result = $handler->handle(new BulkChangeProductStatusCommand($ids, 'archived'));

                            Notification::make()
                                ->title("{$result['successCount']} Produkt(e) archiviert")
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'variants.sku'];
    }
}
