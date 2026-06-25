<?php

namespace App\Filament\Widgets;

use App\Models\InventoryItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlert extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(InventoryItem::whereColumn('current_stock', '<=', 'min_stock')->where('min_stock', '>', 0)->latest()->limit(20))
            ->columns([
                TextColumn::make('name')->searchable()->weight('bold'),
                TextColumn::make('sku'),
                TextColumn::make('current_stock')->numeric()->color('danger')->weight('bold'),
                TextColumn::make('min_stock')->numeric(),
                TextColumn::make('unit'),
            ])
            ->paginated(false);
    }
}
