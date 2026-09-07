<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('size')
                            ->maxLength(20)
                            ->placeholder('e.g. 42, M, S'),
                        Forms\Components\TextInput::make('color')
                            ->maxLength(50)
                            ->placeholder('e.g. Black, Blue'),
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('product_id', fn ($q) => $q->where('product_id', $this->getOwnerRecord()->id)))
                            ->placeholder('e.g. SKC-AIRTECH-42-B'),
                        Forms\Components\TextInput::make('barcode')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('EGP')
                            ->minValue(0),
                        Forms\Components\TextInput::make('compare_at_price')
                            ->label('Compare-at Price')
                            ->numeric()
                            ->prefix('EGP')
                            ->minValue(0)
                            ->helperText('Original price (shown struck-through).'),
                        Forms\Components\Toggle::make('status')
                            ->default(true)
                            ->label('Active'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('size')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('color')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('barcode')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('compare_at_price')
                    ->label('Compare-at')
                    ->money('EGP')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Status'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}