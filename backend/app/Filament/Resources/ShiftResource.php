<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'System';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')->relationship('user', 'name')->required(),
            DateTimePicker::make('opened_at')->required(),
            DateTimePicker::make('closed_at'),
            TextInput::make('opening_cash')->numeric()->default(0),
            TextInput::make('closing_cash')->numeric(),
            Textarea::make('notes')->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('opened_at')->dateTime('M j, Y H:i'),
                TextColumn::make('closed_at')->dateTime('M j, Y H:i'),
                TextColumn::make('opening_cash')->money('USD'),
                TextColumn::make('closing_cash')->money('USD'),
                TextColumn::make('difference')->money('USD')->color(fn ($record) => $record->difference && $record->difference != 0 ? 'danger' : 'success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()->label('Open Shift'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
