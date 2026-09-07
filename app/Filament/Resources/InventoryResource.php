<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use App\Services\InventoryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('manage inventory') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stock Entry')
                    ->schema([
                        Forms\Components\Select::make('store_id')
                            ->label('Store')
                            ->relationship('store', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('variant_id')
                            ->label('Product Variant')
                            ->relationship('variant', 'sku', modifyQueryUsing: fn (Builder $query) => $query->with('product')->orderBy('sku'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => '['.$record->sku.'] '.$record->product->name.' — '.($record->size ?? 'N/A').' / '.($record->color ?? 'N/A'))
                            ->searchable(['sku', 'barcode'])
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Stock Quantity')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('reserved_quantity')
                            ->label('Reserved Quantity')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('reorder_level')
                            ->label('Reorder Level')
                            ->numeric()
                            ->minValue(0)
                            ->default(5)
                            ->helperText('Stock will be flagged as low when it reaches this level.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Store')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('variant.sku')
                    ->label('SKU')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->description(fn (Inventory $record) => $record->variant?->product?->name),
                Tables\Columns\TextColumn::make('variant.size')
                    ->label('Size')
                    ->badge(),
                Tables\Columns\TextColumn::make('variant.color')
                    ->label('Color')
                    ->badge(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color(fn (Inventory $record) => $record->isOutOfStock() ? 'danger' : ($record->isLowStock() ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('reserved_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('availableQuantity')
                    ->label('Available')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reorder_level')
                    ->label('Reorder Lvl')
                    ->numeric()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('store')
                    ->relationship('store', 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('brand')
                    ->label('Brand')
                    ->relationship('variant.product.brand', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'in_stock' => 'In Stock',
                        'low_stock' => 'Low Stock',
                        'out_of_stock' => 'Out of Stock',
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value']) {
                        'in_stock' => $query->whereColumn('stock_quantity', '>', 'reorder_level'),
                        'low_stock' => $query->whereColumn('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'reorder_level'),
                        'out_of_stock' => $query->where('stock_quantity', '<=', 0),
                        default => $query,
                    }),
            ])
            ->defaultSort('stock_quantity', 'asc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('adjust')
                    ->label('Adjust Stock')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->options([
                                'adjustment' => 'Adjustment (manual correction)',
                                'receipt' => 'Receipt (stock received)',
                                'damage' => 'Damage / Loss',
                            ])
                            ->default('adjustment')
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity Change')
                            ->helperText('Use a negative number to reduce stock (e.g. -3).')
                            ->numeric()
                            ->required()
                            ->minValue(-999999)
                            ->maxValue(999999),
                        Forms\Components\TextInput::make('reason')
                            ->maxLength(255),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        app(InventoryService::class)->adjust(
                            $record,
                            (int) $data['quantity'],
                            in_array($data['type'], ['receipt', 'damage']) ? $data['type'] : 'adjustment',
                            $data['reason'],
                            null,
                            auth()->user()
                        );

                        Notification::make()
                            ->title('Stock adjusted')
                            ->body("{$record->variant->sku} now has {$record->fresh()->stock_quantity} in {$record->store->name}.")
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('Apply Adjustment'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('store.name')
                    ->label('Store')
                    ->weight('bold'),
                Infolists\Components\TextEntry::make('variant.product.name')
                    ->label('Product'),
                Infolists\Components\TextEntry::make('variant.sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray'),
                Infolists\Components\TextEntry::make('variant.size')
                    ->label('Size')
                    ->badge(),
                Infolists\Components\TextEntry::make('variant.color')
                    ->label('Color')
                    ->badge(),
                Infolists\Components\TextEntry::make('stock_quantity')
                    ->label('In Stock')
                    ->color(fn (Inventory $record) => $record->isOutOfStock() ? 'danger' : ($record->isLowStock() ? 'warning' : 'success')),
                Infolists\Components\TextEntry::make('reserved_quantity')
                    ->label('Reserved'),
                Infolists\Components\TextEntry::make('availableQuantity')
                    ->label('Available'),
                Infolists\Components\TextEntry::make('reorder_level')
                    ->label('Reorder Level'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'view' => Pages\ViewInventory::route('/{record}'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}