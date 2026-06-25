<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Http;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refund')
                ->label('Refund Payment')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Refund Payment')
                ->modalDescription('This will mark the payment as refunded. Are you sure?')
                ->modalSubmitActionLabel('Confirm Refund')
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')->label('Refund Reason')->required(),
                ])
                ->action(function (array $data) {
                    $payment = $this->record;
                    if ($payment->status !== 'completed') {
                        Notification::make()->title('Payment is not completed.')->danger()->send();
                        return;
                    }
                    $payment->update([
                        'status' => 'refunded',
                        'notes' => ($payment->notes ? $payment->notes . ' | ' : '') . 'Refunded: ' . $data['reason'],
                    ]);
                    Notification::make()->title('Payment refunded.')->success()->send();
                })
                ->visible(fn (Payment $record) => $record->status === 'completed'),
        ];
    }
}
