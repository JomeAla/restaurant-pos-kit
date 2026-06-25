<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoginHistoryResource\Pages;
use App\Models\LoginHistory;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoginHistoryResource extends Resource
{
    protected static ?string $model = LoginHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $slug = 'login-history';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('method')->badge()->color(fn (string $state): string => match ($state) {
                    'email' => 'info',
                    'pin' => 'warning',
                    default => 'gray',
                }),
                IconColumn::make('success')->boolean()->sortable(),
                TextColumn::make('ip_address')->label('IP'),
                TextColumn::make('login_at')->dateTime('M j, Y g:i A')->sortable(),
            ])
            ->defaultSort('login_at', 'desc')
            ->actions([ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoginHistories::route('/'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Login History';
    }
}
