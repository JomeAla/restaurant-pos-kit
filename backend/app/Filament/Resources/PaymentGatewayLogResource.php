<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewayLogResource\Pages;
use App\Models\PaymentGatewayLog;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentGatewayLogResource extends Resource
{
    protected static ?string $model = PaymentGatewayLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form->schema([
            KeyValue::make('request_payload')->label('Request Payload'),
            KeyValue::make('response_payload')->label('Response Payload'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('gateway')->badge()->color(fn (string $state): string => match ($state) {
                    'paystack' => 'info',
                    'stripe' => 'success',
                    default => 'gray',
                }),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'success' => 'success',
                    'failed' => 'danger',
                    default => 'gray',
                }),
                TextColumn::make('reference')->searchable()->toggleable(),
                TextColumn::make('amount')->money('USD')->sortable()->toggleable(),
                TextColumn::make('created_at')->dateTime('M j, Y g:i A')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('gateway')->options(['paystack' => 'Paystack', 'stripe' => 'Stripe']),
                Tables\Filters\SelectFilter::make('status')->options(['success' => 'Success', 'failed' => 'Failed']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->form([
                    KeyValue::make('request_payload')->label('Request Payload'),
                    KeyValue::make('response_payload')->label('Response Payload'),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentGatewayLogs::route('/'),
        ];
    }
}
