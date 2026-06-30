<?php

namespace App\Filament\Pages;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.reports';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    public string $tab = 'sales';

    public string $dateFrom = '';

    public string $dateTo = '';

    public array $reportData = [];

    public function mount(): void
    {
        $this->dateFrom = now()->subMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->reportData = match ($this->tab) {
            'sales' => $this->getSales(),
            'popular-items' => $this->getPopularItems(),
            'profit-margins' => $this->getProfitMargins(),
            'staff-performance' => $this->getStaffPerformance(),
            'payment-methods' => $this->getPaymentMethods(),
            'peak-hours' => $this->getPeakHours(),
            default => [],
        };
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->loadData();
    }

    protected function getSales(): array
    {
        return Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('period')
            ->orderBy('period')
            ->get([
                DB::raw('date(paid_at) as period'),
                DB::raw('sum(amount) as total'),
                DB::raw('count(*) as count'),
            ])
            ->toArray();
    }

    protected function getPopularItems(): array
    {
        return OrderItem::select([
            'menu_item_id',
            DB::raw('sum(quantity) as total_qty'),
            DB::raw('sum(total_price) as total_revenue'),
        ])
            ->whereHas('order', fn($q) => $q->whereIn('status', ['paid', 'closed'])->whereBetween('ordered_at', [$this->dateFrom, $this->dateTo]))
            ->where('status', '!=', 'voided')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->with('menuItem')
            ->get()
            ->toArray();
    }

    protected function getProfitMargins(): array
    {
        return MenuItem::withCount(['items as total_sold' => fn($q) => $q->whereHas('order', fn($o) => $o->whereIn('status', ['paid', 'closed']))])
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => (float) $item->price,
                'cost' => (float) $item->cost,
                'profit' => (float) $item->price - (float) $item->cost,
                'margin_percent' => $item->price > 0 ? round(((float) $item->price - (float) $item->cost) / (float) $item->price * 100, 1) : 0,
                'total_sold' => $item->total_sold,
            ])
            ->sortByDesc('margin_percent')
            ->values()
            ->toArray();
    }

    protected function getStaffPerformance(): array
    {
        return User::withCount(['orders as orders_taken' => fn($q) => $q->whereBetween('ordered_at', [$this->dateFrom, $this->dateTo])])
            ->withSum(['orders as total_sales' => fn($q) => $q->whereIn('status', ['paid', 'closed'])->whereBetween('ordered_at', [$this->dateFrom, $this->dateTo])], 'total')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'orders_taken' => $user->orders_taken,
                'total_sales' => (float) ($user->total_sales ?? 0),
                'avg_order_value' => $user->orders_taken > 0 ? round(($user->total_sales ?? 0) / $user->orders_taken, 2) : 0,
            ])
            ->sortByDesc('total_sales')
            ->values()
            ->toArray();
    }

    protected function getPaymentMethods(): array
    {
        return Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('method')
            ->orderByDesc('total')
            ->get([
                'method',
                DB::raw('sum(amount) as total'),
                DB::raw('count(*) as count'),
            ])
            ->toArray();
    }

    protected function getPeakHours(): array
    {
        return Order::whereIn('status', ['paid', 'closed'])
            ->whereBetween('ordered_at', [$this->dateFrom, $this->dateTo])
            ->selectRaw("strftime('%H', ordered_at) as hour")
            ->selectRaw('count(*) as order_count')
            ->selectRaw('sum(total) as revenue')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();
    }
}
