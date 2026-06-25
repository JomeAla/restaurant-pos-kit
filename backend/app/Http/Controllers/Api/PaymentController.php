<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayLog;
use App\Models\RecipeItem;
use App\Models\RestaurantTable;
use App\Models\SplitBill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $gateway = PaymentGateway::where('gateway', 'stripe')->where('is_active', true)->first();

        if (!$gateway || !$gateway->credentials) {
            return response()->json(['message' => 'Stripe gateway not configured.'], 422);
        }

        $creds = $gateway->decrypted_credentials;
        \Stripe\Stripe::setApiKey($creds['secret_key']);

        try {
            $intent = \Stripe\PaymentIntent::create([
                'amount' => (int)($validated['amount'] * 100),
                'currency' => 'usd',
                'metadata' => ['order_id' => (string)$order->id, 'order_number' => $order->order_number],
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            PaymentGatewayLog::create([
                'gateway' => 'stripe',
                'request_payload' => ['order_id' => $order->id, 'amount' => $validated['amount']],
                'response_payload' => ['id' => $intent->id, 'status' => $intent->status],
                'status' => 'success',
                'reference' => $intent->id,
                'amount' => $validated['amount'],
            ]);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'publishable_key' => $creds['publishable_key'],
                'intent_id' => $intent->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,card,pos,transfer',
            'amount_tendered' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        if (!in_array($order->status, ['sent', 'ready', 'served'])) {
            return response()->json(['message' => 'Order must be sent, ready, or served before payment.'], 422);
        }

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Order is already paid.'], 422);
        }

        return DB::transaction(function () use ($validated, $order, $request) {
            $changeDue = 0;
            $amountTendered = null;

            if ($validated['method'] === 'cash') {
                $amountTendered = $validated['amount_tendered'] ?? $validated['amount'];
                $changeDue = max(0, $amountTendered - $validated['amount']);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'reference' => 'PAY-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
                'change_due' => $changeDue,
                'amount_tendered' => $amountTendered,
                'processed_by' => $request->user()->id,
                'paid_at' => now(),
            ]);

            $paidTotal = Payment::where('order_id', $order->id)->where('status', 'completed')->sum('amount');

            if ($paidTotal >= $order->total) {
                $order->update(['status' => 'paid']);

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => $order->getOriginal('status'),
                    'to_status' => 'paid',
                    'changed_by' => $request->user()->id,
                    'notes' => 'Payment processed: ' . $validated['method'],
                ]);

                $this->deductInventory($order);

                if ($order->table_id) {
                    RestaurantTable::where('id', $order->table_id)->update(['status' => 'dirty']);
                }
            }

            return response()->json([
                'payment' => $payment->load('processedBy'),
                'order_status' => $order->fresh()->status,
                'change_due' => $changeDue,
                'remaining' => max(0, $order->total - $paidTotal),
            ], 201);
        });
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load('order.table', 'processedBy'));
    }

    public function split(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'split_type' => 'required|in:by_item,by_person,by_percentage',
            'splits' => 'required|array|min:2',
            'splits.*.label' => 'required|string|max:255',
            'splits.*.amount' => 'required|numeric|min:0.01',
            'splits.*.method' => 'required|in:cash,card,pos,transfer',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $totalSplit = collect($validated['splits'])->sum('amount');

        if (abs($totalSplit - $order->total) > 0.01) {
            return response()->json(['message' => 'Split amounts must equal the order total ($' . number_format($order->total, 2) . ').'], 422);
        }

        return DB::transaction(function () use ($validated, $order, $request) {
            foreach ($validated['splits'] as $split) {
                $changeDue = $split['method'] === 'cash' ? max(0, $split['amount'] - $split['amount']) : 0;

                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $split['amount'],
                    'method' => $split['method'],
                    'reference' => 'SPLIT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                    'status' => 'completed',
                    'change_due' => $changeDue,
                    'processed_by' => $request->user()->id,
                    'paid_at' => now(),
                    'notes' => 'Split: ' . $split['label'],
                ]);
            }

            SplitBill::create([
                'order_id' => $order->id,
                'split_type' => $validated['split_type'],
                'splits' => $validated['splits'],
                'processed_by' => $request->user()->id,
            ]);

            $order->update(['status' => 'paid']);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $order->getOriginal('status'),
                'to_status' => 'paid',
                'changed_by' => $request->user()->id,
                'notes' => 'Split bill (' . $validated['split_type'] . ')',
            ]);

            if ($order->table_id) {
                RestaurantTable::where('id', $order->table_id)->update(['status' => 'dirty']);
            }

            return response()->json(['message' => 'Split bill processed.', 'payments' => $order->fresh()->payments], 201);
        });
    }

    public function refund(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'approver_pin' => 'required|string|digits:4',
        ]);

        $approver = $request->user();
        if ($approver->pin !== $validated['approver_pin']) {
            return response()->json(['message' => 'Invalid approver PIN.'], 403);
        }

        if (!in_array('superadmin', $approver->permissions ?? []) && !$approver->hasAnyRole(['Admin'])) {
            return response()->json(['message' => 'Only admins can approve refunds.'], 403);
        }

        if ($payment->status !== 'completed') {
            return response()->json(['message' => 'Payment is already refunded.'], 422);
        }

        return DB::transaction(function () use ($payment, $validated, $request) {
            $payment->update(['status' => 'refunded', 'notes' => ($payment->notes ? $payment->notes . ' | ' : '') . 'Refunded: ' . $validated['reason']]);

            OrderStatusLog::create([
                'order_id' => $payment->order_id,
                'from_status' => $payment->order->status,
                'to_status' => $payment->order->status,
                'changed_by' => $request->user()->id,
                'notes' => 'Refund: ' . $validated['reason'],
            ]);

            return response()->json(['message' => 'Payment refunded.']);
        });
    }

    public function closeOrder(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== 'paid') {
            return response()->json(['message' => 'Only paid orders can be closed.'], 422);
        }

        return DB::transaction(function () use ($order, $request) {
            $order->update(['status' => 'closed']);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => 'paid',
                'to_status' => 'closed',
                'changed_by' => $request->user()->id,
            ]);

            return response()->json(['message' => 'Order closed.']);
        });
    }

    private function deductInventory(Order $order): void
    {
        foreach ($order->items as $orderItem) {
            $recipeItems = RecipeItem::where('menu_item_id', $orderItem->menu_item_id)->get();

            foreach ($recipeItems as $recipe) {
                $inventoryItem = InventoryItem::find($recipe->inventory_item_id);
                if (!$inventoryItem || $inventoryItem->current_stock <= 0) continue;

                $deductQty = $recipe->quantity * $orderItem->quantity;
                $actualDeduct = min($deductQty, $inventoryItem->current_stock);

                $inventoryItem->adjustStock($actualDeduct, 'out');

                InventoryTransaction::create([
                    'item_id' => $inventoryItem->id,
                    'type' => 'out',
                    'quantity' => $actualDeduct,
                    'reason' => 'Auto-deduct from order',
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'user_id' => 1,
                    'notes' => "Order {$order->order_number}: {$orderItem->menuItem->name} x{$orderItem->quantity}",
                ]);
            }
        }
    }
}
