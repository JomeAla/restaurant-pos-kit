<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Operations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('customer_phone')->tel()->maxLength(20),
                Forms\Components\TextInput::make('customer_email')->email()->maxLength(255),
                Forms\Components\TextInput::make('party_size')->numeric()->required()->minValue(1),
                Forms\Components\Select::make('table_id')->relationship('table', 'table_number')->label('Table'),
                Forms\Components\DatePicker::make('date')->required(),
                Forms\Components\TextInput::make('time_slot')->required()->maxLength(20),
                Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'seated' => 'Seated', 'cancelled' => 'Cancelled'])->default('pending'),
                Forms\Components\Textarea::make('notes')->maxLength(65535),
                Forms\Components\Toggle::make('walk_in')->label('Walk-in'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer_phone')->searchable(),
                Tables\Columns\TextColumn::make('party_size')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('table.table_number')->label('Table')->sortable(),
                Tables\Columns\TextColumn::make('date')->date('M j, Y')->sortable(),
                Tables\Columns\TextColumn::make('time_slot')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'confirmed' => 'info',
                    'seated' => 'success',
                    'cancelled' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\IconColumn::make('walk_in')->boolean()->label('Walk-in'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'seated' => 'Seated', 'cancelled' => 'Cancelled']),
                Tables\Filters\Filter::make('date')->form([Forms\Components\DatePicker::make('date')])->query(fn ($query, $state) => $state['date'] ? $query->whereDate('date', $state['date']) : $query),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageReservations::route('/'),
        ];
    }
}
