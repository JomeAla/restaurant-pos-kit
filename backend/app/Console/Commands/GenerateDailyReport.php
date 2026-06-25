<?php

namespace App\Console\Commands;

use App\Models\DailyReport;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDailyReport extends Command
{
    protected $signature = 'pos:generate-daily-report {date?}';
    protected $description = 'Generate daily report snapshot for the given date (default: yesterday)';

    public function handle(): int
    {
        $date = $this->argument('date') ? date_create($this->argument('date')) : now()->subDay();
        $dateStr = $date->format('Y-m-d');

        $orders = Order::whereDate('ordered_at', $dateStr);
        $paidOrders = (clone $orders)->whereIn('status', ['paid', 'closed']);
        $payments = Payment::whereDate('paid_at', $dateStr)->where('status', 'completed');

        $paymentBreakdown = $payments->groupBy('method')
            ->selectRaw('method, sum(amount) as total, count(*) as count')
            ->get()
            ->toArray();

        $peakHours = Order::whereIn('status', ['paid', 'closed'])
            ->whereDate('ordered_at', $dateStr)
            ->selectRaw("strftime('%H', ordered_at) as hour, count(*) as order_count, sum(total) as revenue")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();

        DailyReport::updateOrCreate(
            ['report_date' => $dateStr],
            [
                'total_orders' => (clone $orders)->count(),
                'total_revenue' => (clone $paidOrders)->sum('total'),
                'total_tax' => (clone $paidOrders)->sum('tax_total'),
                'total_discounts' => (clone $paidOrders)->sum('discount_total'),
                'total_cancelled' => (clone $orders)->where('status', 'voided')->count(),
                'payment_breakdown' => $paymentBreakdown,
                'peak_hours' => $peakHours,
            ]
        );

        $this->info("Daily report generated for {$dateStr}.");

        return Command::SUCCESS;
    }
}
