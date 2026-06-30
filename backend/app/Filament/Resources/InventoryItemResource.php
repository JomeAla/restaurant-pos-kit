<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryItemResource\Pages;
use App\Models\InventoryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryItemResource extends Resource
{
    protected static ?string $model = InventoryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('sku')->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\TextInput::make('category')->maxLength(255),
                Forms\Components\Select::make('unit')->options(['kg' => 'kg', 'g' => 'g', 'pcs' => 'pcs', 'ltr' => 'ltr', 'ml' => 'ml', 'oz' => 'oz', 'lb' => 'lb'])->required(),
                Forms\Components\TextInput::make('current_stock')->numeric()->required()->minValue(0),
                Forms\Components\TextInput::make('min_stock')->numeric()->default(0)->minValue(0),
                Forms\Components\TextInput::make('cost_per_unit')->numeric()->default(0)->minValue(0),
                Forms\Components\TextInput::make('supplier')->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sku')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->sortable(),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('current_stock')->numeric()->sortable()->color(fn ($record): ?string => $record->isLowStock() ? 'danger' : null)->weight(fn ($record): ?string => $record->isLowStock() ? 'bold' : null),
                Tables\Columns\TextColumn::make('min_stock')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('supplier')->toggleable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options(fn () => InventoryItem::distinct()->whereNotNull('category')->pluck('category', 'category')->toArray()),
                Tables\Filters\TernaryFilter::make('low_stock')->label('Low Stock')->queries(fn ($q) => [$q->whereColumn('current_stock', '<=', 'min_stock'), $q->whereColumn('current_stock', '>', 'min_stock')]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInventoryItems::route('/'),
        ];
    }
}
