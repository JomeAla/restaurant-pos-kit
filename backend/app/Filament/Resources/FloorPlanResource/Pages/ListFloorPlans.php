<?php

namespace App\Filament\Resources\FloorPlanResource\Pages;

use App\Filament\Resources\FloorPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFloorPlans extends ListRecords
{
    protected static string $resource = FloorPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
