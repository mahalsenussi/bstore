<?php

namespace App\Filament\Resources\InventoryResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $recordTitleAttribute = 'type';

    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'receipt', 'transfer_in', 'sale_return' => 'success',
                        'sale', 'transfer_out', 'release' => 'info',
                        'adjustment' => 'warning',
                        'reserve' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->formatStateUsing(fn (string $state): string => $state > 0 ? '+'.$state : $state)
                    ->color(fn (string $state): string => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('stock_after')
                    ->label('Balance')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(40)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('By')
                    ->placeholder('System'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}