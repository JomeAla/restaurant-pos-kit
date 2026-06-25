<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentSummary extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Payment::whereDate('paid_at', today())->where('status', 'completed');
        $yesterday = Payment::whereDate('paid_at', today()->subDay())->where('status', 'completed');

        $todayTotal = $today->sum('amount');
        $yesterdayTotal = $yesterday->sum('amount');

        $methods = Payment::whereDate('paid_at', today())->where('status', 'completed')
            ->selectRaw('method, sum(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();

        return [
            Stat::make('Today\'s Takings', '$' . number_format($todayTotal, 2))
                ->description($yesterdayTotal > 0 ? vsprintf('%s vs yesterday', [(($todayTotal - $yesterdayTotal) / $yesterdayTotal * 100 >= 0 ? '+' : '') . number_format(($todayTotal - $yesterdayTotal) / $yesterdayTotal * 100, 1) . '%']) : 'No yesterday data')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Cash', '$' . number_format($methods['cash'] ?? 0, 2))
                ->color('success'),

            Stat::make('Card', '$' . number_format($methods['card'] ?? 0, 2))
                ->color('info'),

            Stat::make('POS / Transfer', '$' . number_format(($methods['pos'] ?? 0) + ($methods['transfer'] ?? 0), 2))
                ->color('warning'),
        ];
    }
}
