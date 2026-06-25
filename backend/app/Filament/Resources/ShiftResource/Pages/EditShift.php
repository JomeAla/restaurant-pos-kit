<?php

namespace App\Filament\Resources\ShiftResource\Pages;

use App\Filament\Resources\ShiftResource;
use Filament\Resources\Pages\EditRecord;

class EditShift extends EditRecord
{
    protected static string $resource = ShiftResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['closing_cash'])) {
            $record = $this->record;
            $data['difference'] = $data['closing_cash'] - $record->opening_cash;
        }
        return $data;
    }
}
