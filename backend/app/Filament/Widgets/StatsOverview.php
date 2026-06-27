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

        $symbols = [
            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'CAD' => 'C$', 'AUD' => 'A$', 'CHF' => 'Fr', 'CNY' => '¥', 'INR' => '₹', 'BRL' => 'R$', 'MXN' => 'Mex$',
            'NGN' => '₦', 'ZAR' => 'R', 'EGP' => 'E£', 'KES' => 'KSh', 'GHS' => 'GH₵', 'TZS' => 'TSh', 'UGX' => 'USh', 'MAD' => 'DH', 'DZD' => 'DA',
            'XAF' => 'FCFA', 'XOF' => 'CFA', 'ETB' => 'Br', 'AOA' => 'Kz', 'MZN' => 'MT', 'ZMW' => 'ZK', 'RWF' => 'FRw', 'TND' => 'DT',
            'SDG' => 'SDG', 'LYD' => 'LD', 'BWP' => 'P', 'NAD' => 'N$', 'MWK' => 'MK', 'MUR' => 'Rs', 'GMD' => 'D', 'CDF' => 'FC',
            'MGA' => 'Ar', 'GNF' => 'FG', 'SOS' => 'Sh', 'BIF' => 'FBu', 'SCR' => 'SR', 'SZL' => 'E', 'LSL' => 'L', 'CVE' => 'Esc',
            'MRU' => 'UM', 'DJF' => 'Fdj', 'KMF' => 'CF', 'SSP' => '£', 'SLE' => 'Le', 'STN' => 'Db', 'ERN' => 'Nfk',
        ];
        $currency = Setting::getValue('currency', 'USD');
        $currencySymbol = $symbols[$currency] ?? '$';

        return [
            Stat::make('Orders Today', $ordersToday)
                ->description('Total orders placed')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

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
