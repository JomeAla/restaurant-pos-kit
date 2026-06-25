<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest('ordered_at')->paginate($request->per_page ?? 50));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $totalCost = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_cost']);

        $order = PurchaseOrder::create([
            'supplier' => $validated['supplier'],
            'items' => $validated['items'],
            'total_cost' => $totalCost,
            'ordered_at' => now(),
            'status' => 'pending',
        ]);

        return response()->json($order, 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        return response()->json($purchaseOrder);
    }

    public function receive(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== 'pending') {
            return response()->json(['message' => 'Order already ' . $purchaseOrder->status . '.'], 422);
        }

        return DB::transaction(function () use ($purchaseOrder) {
            foreach ($purchaseOrder->items as $item) {
                $invItem = InventoryItem::find($item['inventory_item_id']);
                if ($invItem) {
                    $invItem->adjustStock($item['quantity'], 'in');

                    InventoryTransaction::create([
                        'item_id' => $invItem->id,
                        'type' => 'in',
                        'quantity' => $item['quantity'],
                        'reason' => 'Purchase order received',
                        'reference_type' => 'purchase_order',
                        'reference_id' => $purchaseOrder->id,
                        'user_id' => request()->user()->id,
                        'notes' => 'PO #' . $purchaseOrder->id . ' from ' . $purchaseOrder->supplier,
                    ]);
                }
            }

            $purchaseOrder->update(['status' => 'received', 'received_at' => now()]);

            return response()->json(['message' => 'Purchase order received.', 'purchase_order' => $purchaseOrder->fresh()]);
        });
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status === 'received') {
            return response()->json(['message' => 'Cannot cancel a received order.'], 422);
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Purchase order cancelled.']);
    }
}
