<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InventoryTransaction::with('item', 'user');

        if ($request->item_id) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->latest()->paginate($request->per_page ?? 50));
    }

    public function stockIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $item = InventoryItem::findOrFail($validated['item_id']);
            $item->adjustStock($validated['quantity'], 'in');

            $transaction = InventoryTransaction::create([
                'item_id' => $item->id,
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'reason' => $validated['reason'] ?? 'Stock adjustment',
                'user_id' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json($transaction->load('item', 'user'), 201);
        });
    }

    public function stockOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $item = InventoryItem::findOrFail($validated['item_id']);

            if ($item->current_stock < $validated['quantity']) {
                return response()->json(['message' => 'Insufficient stock. Available: ' . $item->current_stock], 422);
            }

            $item->adjustStock($validated['quantity'], 'out');

            $transaction = InventoryTransaction::create([
                'item_id' => $item->id,
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'reason' => $validated['reason'] ?? 'Stock removal',
                'user_id' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json($transaction->load('item', 'user'), 201);
        });
    }

    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'new_stock' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $item = InventoryItem::findOrFail($validated['item_id']);
            $diff = $validated['new_stock'] - $item->current_stock;
            $type = $diff >= 0 ? 'in' : 'out';

            $item->update(['current_stock' => $validated['new_stock']]);

            $transaction = InventoryTransaction::create([
                'item_id' => $item->id,
                'type' => $type,
                'quantity' => abs($diff),
                'reason' => 'Manual adjustment',
                'user_id' => $request->user()->id,
                'notes' => $validated['notes'] ?? 'Adjusted to ' . $validated['new_stock'],
            ]);

            return response()->json($transaction->load('item', 'user'), 201);
        });
    }
}
