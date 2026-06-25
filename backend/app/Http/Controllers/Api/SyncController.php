<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function orders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.local_id' => 'required|string',
            'orders.*.type' => 'required|in:dine-in,takeaway,delivery',
            'orders.*.table_id' => 'nullable|exists:restaurant_tables,id',
            'orders.*.customer_name' => 'nullable|string|max:255',
            'orders.*.customer_phone' => 'nullable|string|max:20',
            'orders.*.notes' => 'nullable|string',
            'orders.*.items' => 'required|array|min:1',
            'orders.*.items.*.menu_item_id' => 'required|exists:menu_items,id',
            'orders.*.items.*.quantity' => 'required|integer|min:1',
            'orders.*.items.*.unit_price' => 'required|numeric|min:0',
            'orders.*.items.*.modifier_summary' => 'nullable|array',
            'orders.*.items.*.course' => 'nullable|string',
            'last_sync_at' => 'nullable|date',
        ]);

        $synced = [];

        DB::transaction(function () use ($validated, $request, &$synced) {
            foreach ($validated['orders'] as $orderData) {
                $orderNumber = $this->generateOrderNumber();
                $subtotal = 0;
                $orderItems = [];

                foreach ($orderData['items'] as $item) {
                    $lineTotal = $item['unit_price'] * $item['quantity'];
                    $subtotal += $lineTotal;
                    $orderItems[] = [
                        'menu_item_id' => $item['menu_item_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $lineTotal,
                        'modifier_summary' => isset($item['modifier_summary']) ? json_encode($item['modifier_summary']) : null,
                        'course' => $item['course'] ?? null,
                    ];
                }

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'user_id' => $request->user()->id,
                    'table_id' => $orderData['table_id'] ?? null,
                    'customer_name' => $orderData['customer_name'] ?? null,
                    'customer_phone' => $orderData['customer_phone'] ?? null,
                    'type' => $orderData['type'],
                    'status' => 'pending',
                    'subtotal' => $subtotal,
                    'tax_total' => 0,
                    'discount_total' => 0,
                    'total' => $subtotal,
                    'notes' => $orderData['notes'] ?? null,
                    'ordered_at' => now(),
                ]);

                foreach ($orderItems as &$item) {
                    $item['order_id'] = $order->id;
                }
                OrderItem::insert($orderItems);

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => null,
                    'to_status' => 'pending',
                    'changed_by' => $request->user()->id,
                    'notes' => 'Synced from offline',
                ]);

                if ($order->table_id) {
                    RestaurantTable::where('id', $order->table_id)->update(['status' => 'occupied']);
                }

                $synced[] = [
                    'local_id' => $orderData['local_id'],
                    'server_id' => $order->id,
                    'order_number' => $orderNumber,
                ];
            }
        });

        $changes = [];
        if ($request->last_sync_at) {
            $changes = Order::where('updated_at', '>', $request->last_sync_at)
                ->orWhere('created_at', '>', $request->last_sync_at)
                ->with('items')
                ->get();
        }

        return response()->json([
            'synced' => $synced,
            'changes' => $changes,
            'server_time' => now(),
        ]);
    }

    private function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $last = Order::whereDate('created_at', today())
            ->where('order_number', 'like', "POS-{$date}-%")
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = $last ? (int)substr($last, -3) + 1 : 1;

        return "POS-{$date}-" . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
