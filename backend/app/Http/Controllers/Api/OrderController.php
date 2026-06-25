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

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('user', 'table', 'items.menuItem');

        if ($request->date) {
            $query->whereDate('ordered_at', $request->date);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->table_id) {
            $query->where('table_id', $request->table_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->latest('ordered_at')->paginate($request->per_page ?? 50));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_id' => 'nullable|exists:restaurant_tables,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'type' => 'required|in:dine-in,takeaway,delivery',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.modifier_summary' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
            'items.*.course' => 'nullable|string',
        ]);

        if ($validated['type'] === 'dine-in' && $validated['table_id']) {
            $active = Order::where('table_id', $validated['table_id'])
                ->whereIn('status', ['pending', 'sent', 'preparing', 'ready', 'served'])
                ->exists();
            if ($active) {
                return response()->json(['message' => 'This table already has an active order.'], 409);
            }
        }

        return DB::transaction(function () use ($validated, $request) {
            $orderNumber = $this->generateOrderNumber();

            $subtotal = 0;
            $orderItems = [];
            foreach ($validated['items'] as $item) {
                $lineTotal = $item['unit_price'] * $item['quantity'];
                $subtotal += $lineTotal;
                $orderItems[] = [
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $lineTotal,
                    'modifier_summary' => isset($item['modifier_summary']) ? json_encode($item['modifier_summary']) : null,
                    'notes' => $item['notes'] ?? null,
                    'course' => $item['course'] ?? null,
                ];
            }

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $request->user()->id,
                'table_id' => $validated['table_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'type' => $validated['type'],
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_total' => 0,
                'discount_total' => 0,
                'total' => $subtotal,
                'notes' => $validated['notes'] ?? null,
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
            ]);

            if ($order->table_id) {
                RestaurantTable::where('id', $order->table_id)->update(['status' => 'occupied']);
            }

            return response()->json($order->load('items.menuItem', 'user', 'table'), 201);
        });
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json($order->load('items.menuItem', 'user', 'table', 'statusLogs.changedBy'));
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);

        return response()->json($order->load('items.menuItem', 'user', 'table'));
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate(['void_reason' => 'required|string|max:255']);

        if (!in_array($order->status, ['pending', 'sent'])) {
            return response()->json(['message' => 'Only pending or sent orders can be voided.'], 422);
        }

        return DB::transaction(function () use ($order, $validated, $request) {
            $order->update(['status' => 'voided', 'void_reason' => $validated['void_reason']]);
            $order->items()->update(['status' => 'voided', 'void_reason' => $validated['void_reason']]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $order->getOriginal('status'),
                'to_status' => 'voided',
                'changed_by' => $request->user()->id,
                'notes' => $validated['void_reason'],
            ]);

            if ($order->table_id) {
                RestaurantTable::where('id', $order->table_id)->update(['status' => 'free']);
            }

            return response()->json(['message' => 'Order voided.']);
        });
    }

    public function addItem(Request $request, Order $order): JsonResponse
    {
        if (in_array($order->status, ['paid', 'closed', 'voided'])) {
            return response()->json(['message' => 'Cannot add items to a ' . $order->status . ' order.'], 422);
        }

        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'modifier_summary' => 'nullable|array',
            'notes' => 'nullable|string',
            'course' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($order, $validated) {
            $totalPrice = $validated['unit_price'] * $validated['quantity'];

            $item = $order->items()->create([
                'menu_item_id' => $validated['menu_item_id'],
                'quantity' => $validated['quantity'],
                'unit_price' => $validated['unit_price'],
                'total_price' => $totalPrice,
                'modifier_summary' => $validated['modifier_summary'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'course' => $validated['course'] ?? null,
            ]);

            $order->recalculateTotals();

            return response()->json($item->load('menuItem'), 201);
        });
    }

    public function updateItem(Request $request, Order $order, OrderItem $item): JsonResponse
    {
        if ($item->order_id !== $order->id) {
            return response()->json(['message' => 'Item not found on this order.'], 404);
        }

        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'notes' => 'nullable|string',
            'course' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($order, $item, $validated) {
            if (isset($validated['quantity']) && $validated['quantity'] !== $item->quantity) {
                $item->update([
                    'quantity' => $validated['quantity'],
                    'total_price' => $item->unit_price * $validated['quantity'],
                ]);
            }

            $item->update(collect($validated)->except('quantity')->toArray());
            $order->recalculateTotals();

            return response()->json($item->fresh()->load('menuItem'));
        });
    }

    public function removeItem(Request $request, Order $order, OrderItem $item): JsonResponse
    {
        if ($item->order_id !== $order->id) {
            return response()->json(['message' => 'Item not found on this order.'], 404);
        }

        $validated = $request->validate(['void_reason' => 'required|string|max:255']);

        return DB::transaction(function () use ($order, $item, $validated, $request) {
            $item->update([
                'status' => 'voided',
                'void_reason' => $validated['void_reason'],
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $order->status,
                'to_status' => $order->status,
                'changed_by' => $request->user()->id,
                'notes' => 'Item voided: ' . $item->menuItem->name . ' - ' . $validated['void_reason'],
            ]);

            $order->recalculateTotals();

            return response()->json(['message' => 'Item voided.']);
        });
    }

    public function hold(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be held.'], 422);
        }

        $order->update(['status' => 'pending']);

        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'pending',
            'changed_by' => $request->user()->id,
            'notes' => 'Order held',
        ]);

        return response()->json(['message' => 'Order held.']);
    }

    public function release(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only held/unsent orders can be released.'], 422);
        }

        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'pending',
            'changed_by' => $request->user()->id,
            'notes' => 'Order released',
        ]);

        return response()->json(['message' => 'Order released.']);
    }

    public function sendToKitchen(Request $request, Order $order): JsonResponse
    {
        if (!$order->canTransitionTo('sent')) {
            return response()->json(['message' => 'Order cannot be sent to kitchen from current status.'], 422);
        }

        return DB::transaction(function () use ($order, $request) {
            $order->update(['status' => 'sent']);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => 'pending',
                'to_status' => 'sent',
                'changed_by' => $request->user()->id,
                'notes' => 'Sent to kitchen',
            ]);

            return response()->json(['message' => 'Order sent to kitchen.', 'order' => $order->fresh()->load('items.menuItem')]);
        });
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
