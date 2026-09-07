<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransferResource\Pages;
use App\Filament\Resources\StockTransferResource\RelationManagers;
use App\Models\StockTransfer;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('manage inventory') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transfer Details')
                    ->schema([
                        Forms\Components\Select::make('from_store_id')
                            ->label('From Store')
                            ->relationship('fromStore', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('to_store_id')
                            ->label('To Store')
                            ->relationship('toStore', 'name')
                            ->options(fn (Forms\Get $get): array => \App\Models\Store::where('id', '!=', $get('from_store_id'))->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Source and destination must be different.'),
                        Forms\Components\Select::make('variant_id')
                            ->label('Product Variant')
                            ->relationship('variant', 'sku', modifyQueryUsing: fn (Builder $query) => $query->with('product')->orderBy('sku'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => '['.$record->sku.'] '.$record->product->name.' — '.($record->size ?? 'N/A').' / '.($record->color ?? 'N/A'))
                            ->searchable(['sku', 'barcode'])
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fromStore.name')
                    ->label('From')
                    ->sortable()
                    ->description(fn (StockTransfer $record) => $record->fromStore->city)
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('status')
                    ->label('')
                    ->icon(fn (string $state): string => match ($state) {
                        'received' => 'heroicon-o-check-circle',
                        'cancelled' => 'heroicon-o-x-circle',
                        'pending', 'approved', 'in_transit' => 'heroicon-o-arrow-right',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('toStore.name')
                    ->label('To')
                    ->sortable()
                    ->description(fn (StockTransfer $record) => $record->toStore->city),
                Tables\Columns\TextColumn::make('variant.sku')
                    ->label('SKU')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->description(fn (StockTransfer $record) => $record->variant?->product?->name),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'in_transit' => 'primary',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(StockTransfer::STATUSES),
                Tables\Filters\SelectFilter::make('from_store')
                    ->label('From Store')
                    ->relationship('fromStore', 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('to_store')
                    ->label('To Store')
                    ->relationship('toStore', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve & Ship')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (StockTransfer $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (StockTransfer $record): void {
                        app(InventoryService::class)->approveTransfer($record, auth()->user());

                        Notification::make()
                            ->title('Transfer approved')
                            ->body("{$record->quantity}x {$record->variant->sku} shipped from {$record->fromStore->name}.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('receive')
                    ->label('Receive Stock')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (StockTransfer $record): bool => in_array($record->status, ['approved', 'in_transit']))
                    ->requiresConfirmation()
                    ->action(function (StockTransfer $record): void {
                        app(InventoryService::class)->receiveTransfer($record, auth()->user());

                        Notification::make()
                            ->title('Transfer received')
                            ->body("{$record->quantity}x {$record->variant->sku} received at {$record->toStore->name}.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (StockTransfer $record): bool => ! in_array($record->status, ['received', 'cancelled']))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Cancellation Reason')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (StockTransfer $record, array $data): void {
                        app(InventoryService::class)->cancelTransfer($record, $data['reason'], auth()->user());

                        Notification::make()
                            ->title('Transfer cancelled')
                            ->body("Transfer #{$record->id} has been cancelled.")
                            ->danger()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('fromStore.name')
                    ->label('From Store')
                    ->weight('bold'),
                Infolists\Components\TextEntry::make('toStore.name')
                    ->label('To Store')
                    ->weight('bold'),
                Infolists\Components\TextEntry::make('variant.sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray'),
                Infolists\Components\TextEntry::make('variant.product.name')
                    ->label('Product'),
                Infolists\Components\TextEntry::make('quantity')
                    ->badge()
                    ->color('info'),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'in_transit' => 'primary',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('requester.name')
                    ->label('Requested By')
                    ->placeholder('—'),
                Infolists\Components\TextEntry::make('approver.name')
                    ->label('Approved By')
                    ->placeholder('—'),
                Infolists\Components\TextEntry::make('receiver.name')
                    ->label('Received By')
                    ->placeholder('—'),
                Infolists\Components\TextEntry::make('notes')
                    ->columnSpanFull()
                    ->placeholder('No notes'),
            ])
            ->columns(3);
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
            'index' => Pages\ListStockTransfers::route('/'),
            'create' => Pages\CreateStockTransfer::route('/create'),
            'view' => Pages\ViewStockTransfer::route('/{record}'),
        ];
    }
}