<?php

namespace App\Filament\Widgets;

use App\Models\LoginHistory;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentLogins extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(LoginHistory::with('user')->latest('login_at')->limit(10))
            ->columns([
                TextColumn::make('user.name')->label('User')->searchable(),
                TextColumn::make('method')->badge()->color(fn (string $state): string => match ($state) {
                    'email' => 'info',
                    'pin' => 'warning',
                    default => 'gray',
                }),
                IconColumn::make('success')->boolean(),
                TextColumn::make('ip_address')->label('IP'),
                TextColumn::make('login_at')->dateTime('M j, Y g:i A')->sortable(),
            ])
            ->paginated(false);
    }
}
