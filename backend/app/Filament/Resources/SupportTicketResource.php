<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationGroup = 'Support';

    protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                Select::make('category')
                    ->options(fn () => \App\Models\TicketCategory::where('is_active', true)->pluck('name', 'name')->toArray())
                    ->nullable(),
                Select::make('priority')
                    ->options(array_combine(SupportTicket::$priorities, SupportTicket::$priorities))
                    ->default('medium'),
                Select::make('status')
                    ->options(array_combine(SupportTicket::$statuses, SupportTicket::$statuses))
                    ->default('open'),
                Select::make('assigned_to')
                    ->options(fn () => User::where('is_active', true)->pluck('name', 'id'))
                    ->nullable()
                    ->relationship('assignedTo', 'name'),
                DateTimePicker::make('resolved_at')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                TextColumn::make('category')
                    ->badge(),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'blue',
                        'high' => 'orange',
                        'urgent' => 'red',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('messages_count')
                    ->counts('messages')
                    ->label('Messages'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'create' => Pages\CreateSupportTicket::route('/create'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
