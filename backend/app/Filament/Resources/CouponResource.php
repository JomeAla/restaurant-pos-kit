<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('code')
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->hint('Leave empty for auto-generate'),
                    Select::make('type')
                        ->required()
                        ->options([
                            'percentage' => 'Percentage',
                            'fixed' => 'Fixed Amount',
                        ]),
                    TextInput::make('value')
                        ->required()
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('min_order_amount')
                        ->numeric()
                        ->minValue(0)
                        ->nullable(),
                    TextInput::make('max_usage_count')
                        ->integer()
                        ->minValue(1)
                        ->nullable(),
                    TextInput::make('per_customer_limit')
                        ->integer()
                        ->minValue(1)
                        ->nullable(),
                    Toggle::make('is_active')
                        ->default(true),
                    DateTimePicker::make('starts_at')
                        ->nullable(),
                    DateTimePicker::make('ends_at')
                        ->nullable(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'percentage' ? 'success' : 'info'),
                TextColumn::make('value')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('usages_count')
                    ->counts('usages')
                    ->label('Usages'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
