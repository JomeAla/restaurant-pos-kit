<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Finance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')->relationship('order', 'order_number')->required(),
                Forms\Components\TextInput::make('amount')->numeric()->required()->minValue(0),
                Forms\Components\Select::make('method')->options(['cash' => 'Cash', 'card' => 'Card', 'pos' => 'POS', 'transfer' => 'Transfer'])->required(),
                Forms\Components\TextInput::make('reference')->maxLength(255),
                Forms\Components\Select::make('status')->options(['completed' => 'Completed', 'refunded' => 'Refunded', 'failed' => 'Failed'])->default('completed'),
                Forms\Components\Textarea::make('notes')->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->label('Order')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('method')->badge()->color(fn (string $state): string => match ($state) {
                    'cash' => 'success',
                    'card' => 'info',
                    'pos' => 'warning',
                    'transfer' => 'gray',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('reference')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'completed' => 'success',
                    'refunded' => 'danger',
                    'failed' => 'gray',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('processedBy.name')->label('Processed By')->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime('M j, Y g:i A')->sortable(),
            ])
            ->defaultSort('paid_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('method')->options(['cash' => 'Cash', 'card' => 'Card', 'pos' => 'POS', 'transfer' => 'Transfer']),
                Tables\Filters\SelectFilter::make('status')->options(['completed' => 'Completed', 'refunded' => 'Refunded', 'failed' => 'Failed']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('refund_quick')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Refund Payment')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')->label('Reason')->required(),
                    ])
                    ->action(function (Payment $record, array $data) {
                        if ($record->status !== 'completed') return;
                        $record->update([
                            'status' => 'refunded',
                            'notes' => ($record->notes ? $record->notes . ' | ' : '') . 'Refunded: ' . $data['reason'],
                        ]);
                        Notification::make()->title('Payment refunded.')->success()->send();
                    })
                    ->visible(fn (Payment $record) => $record->status === 'completed'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
