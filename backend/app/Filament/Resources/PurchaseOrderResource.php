<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('supplier')->required()->maxLength(255),
                Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'received' => 'Received', 'cancelled' => 'Cancelled'])->default('pending'),
                Forms\Components\TextInput::make('total_cost')->numeric()->prefix('$'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('PO #')->sortable(),
                Tables\Columns\TextColumn::make('supplier')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('total_cost')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'received' => 'success',
                    'cancelled' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('ordered_at')->dateTime('M j, Y g:i A')->sortable(),
                Tables\Columns\TextColumn::make('received_at')->dateTime('M j, Y g:i A')->placeholder('-')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'received' => 'Received', 'cancelled' => 'Cancelled']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePurchaseOrders::route('/'),
        ];
    }
}
