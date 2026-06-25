<?php

namespace App\Filament\Widgets;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\Setting;
use App\Models\SupportTicket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $ordersToday = Order::whereDate('created_at', today())->count();
        $revenueToday = Payment::whereDate('paid_at', today())->where('status', 'completed')->sum('amount');
        $activeTables = RestaurantTable::where('status', 'occupied')->count();
        $lowStockItems = InventoryItem::whereColumn('current_stock', '<=', 'min_stock')->where('min_stock', '>', 0)->count();
        $openTickets = SupportTicket::whereIn('status', ['open', 'assigned', 'in_progress'])->count();

        return [
            Stat::make('Orders Today', $ordersToday)
                ->description('Total orders placed')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'NGN' => '₦'];
            $currency = Setting::getValue('currency', 'USD');
            $currencySymbol = $symbols[$currency] ?? '$';
            Stat::make('Revenue Today', $currencySymbol . number_format($revenueToday, 2))
                ->description('Completed payments')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Active Tables', $activeTables)
                ->description('Occupied tables')
                ->descriptionIcon('heroicon-m-table-cells')
                ->color('warning'),

            Stat::make('Low Stock Items', $lowStockItems)
                ->description('Items needing restock')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Open Tickets', $openTickets)
                ->description('Support tickets')
                ->descriptionIcon('heroicon-m-lifebuoy')
                ->color('info'),
        ];
    }
}
