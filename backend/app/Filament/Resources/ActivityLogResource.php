<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'System';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('action')->badge()->searchable(),
                TextColumn::make('description')->limit(60)->searchable(),
                TextColumn::make('ip_address')->label('IP')->toggleable(),
                TextColumn::make('created_at')->dateTime('M j, Y g:i A')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name'),
                Tables\Filters\SelectFilter::make('action')
                    ->options(fn () => ActivityLog::distinct()->pluck('action', 'action')->toArray()),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date_from')->label('From'),
                        \Filament\Forms\Components\DatePicker::make('date_to')->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['date_to'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->form([
                    \Filament\Forms\Components\TextInput::make('action'),
                    \Filament\Forms\Components\Textarea::make('description'),
                    \Filament\Forms\Components\KeyValue::make('properties'),
                    \Filament\Forms\Components\TextInput::make('ip_address')->label('IP'),
                    \Filament\Forms\Components\TextInput::make('user_agent'),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
