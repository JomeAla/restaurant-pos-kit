<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function sales(Request $request): JsonResponse
    {
        $start = $request->date_from ?? today()->subMonth();
        $end = $request->date_to ?? today();
        $groupBy = $request->group_by ?? 'day';

        $selectDate = match ($groupBy) {
            'week' => DB::raw("strftime('%Y-%W', paid_at) as period"),
            'month' => DB::raw("strftime('%Y-%m', paid_at) as period"),
            default => DB::raw("date(paid_at) as period"),
        };

        $sales = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy('period')
            ->orderBy('period')
            ->get([
                $selectDate,
                DB::raw('sum(amount) as total'),
                DB::raw('count(*) as count'),
            ]);

        return response()->json($sales);
    }

    public function popularItems(Request $request): JsonResponse
    {
        $start = $request->date_from ?? today()->subMonth();
        $end = $request->date_to ?? today();

        $items = OrderItem::select([
            'menu_item_id',
            DB::raw('sum(quantity) as total_qty'),
            DB::raw('sum(total_price) as total_revenue'),
        ])
            ->whereHas('order', fn($q) => $q->whereIn('status', ['paid', 'closed'])->whereBetween('ordered_at', [$start, $end]))
            ->where('status', '!=', 'voided')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->with('menuItem')
            ->get();

        return response()->json($items);
    }

    public function profitMargins(): JsonResponse
    {
        $items = MenuItem::withCount(['items as total_sold' => fn($q) => $q->whereHas('order', fn($o) => $o->whereIn('status', ['paid', 'closed']))])
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'cost' => $item->cost,
                'profit' => $item->price - $item->cost,
                'margin_percent' => $item->price > 0 ? round(($item->price - $item->cost) / $item->price * 100, 1) : 0,
                'total_sold' => $item->total_sold,
            ])
            ->sortByDesc('margin_percent')
            ->values();

        return response()->json($items);
    }

    public function staffPerformance(Request $request): JsonResponse
    {
        $start = $request->date_from ?? today()->subMonth();
        $end = $request->date_to ?? today();

        $staff = User::withCount(['orders as orders_taken' => fn($q) => $q->whereBetween('ordered_at', [$start, $end])])
            ->withSum(['orders as total_sales' => fn($q) => $q->whereIn('status', ['paid', 'closed'])->whereBetween('ordered_at', [$start, $end])], 'total')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'orders_taken' => $user->orders_taken,
                'total_sales' => $user->total_sales ?? 0,
                'avg_order_value' => $user->orders_taken > 0 ? round(($user->total_sales ?? 0) / $user->orders_taken, 2) : 0,
            ])
            ->sortByDesc('total_sales')
            ->values();

        return response()->json($staff);
    }

    public function paymentMethods(Request $request): JsonResponse
    {
        $start = $request->date_from ?? today()->subMonth();
        $end = $request->date_to ?? today();

        $methods = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy('method')
            ->orderByDesc('total')
            ->get([
                'method',
                DB::raw('sum(amount) as total'),
                DB::raw('count(*) as count'),
            ]);

        return response()->json($methods);
    }

    public function peakHours(Request $request): JsonResponse
    {
        $start = $request->date_from ?? today()->subMonth();
        $end = $request->date_to ?? today();

        $hours = Order::whereIn('status', ['paid', 'closed'])
            ->whereBetween('ordered_at', [$start, $end])
            ->selectRaw("strftime('%H', ordered_at) as hour")
            ->selectRaw('count(*) as order_count')
            ->selectRaw('sum(total) as revenue')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return response()->json($hours);
    }
}
