<?php

namespace App\Filament\Resources\PaymentGatewayLogResource\Pages;

use App\Filament\Resources\PaymentGatewayLogResource;
use Filament\Resources\Pages\ListRecords;

class ListPaymentGatewayLogs extends ListRecords
{
    protected static string $resource = PaymentGatewayLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
