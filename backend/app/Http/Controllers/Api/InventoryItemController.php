<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InventoryItem::query();

        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->low_stock) {
            $query->whereColumn('current_stock', '<=', 'min_stock');
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        return response()->json($query->paginate($request->per_page ?? 50));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|alpha_dash|unique:inventory_items',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:20',
            'current_stock' => 'numeric|min:0',
            'min_stock' => 'numeric|min:0',
            'cost_per_unit' => 'numeric|min:0',
            'supplier' => 'nullable|string|max:255',
        ]);

        return response()->json(InventoryItem::create($validated), 201);
    }

    public function show(InventoryItem $inventoryItem): JsonResponse
    {
        return response()->json($inventoryItem->load('transactions.user'));
    }

    public function update(Request $request, InventoryItem $inventoryItem): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|alpha_dash|unique:inventory_items,sku,' . $inventoryItem->id,
            'category' => 'nullable|string|max:255',
            'unit' => 'sometimes|string|max:20',
            'current_stock' => 'numeric|min:0',
            'min_stock' => 'numeric|min:0',
            'cost_per_unit' => 'numeric|min:0',
            'supplier' => 'nullable|string|max:255',
        ]);

        $inventoryItem->update($validated);

        return response()->json($inventoryItem);
    }

    public function destroy(InventoryItem $inventoryItem): JsonResponse
    {
        $inventoryItem->delete();

        return response()->json(['message' => 'Inventory item deleted.']);
    }

    public function categories(): JsonResponse
    {
        return response()->json(InventoryItem::select('category')->distinct()->whereNotNull('category')->pluck('category'));
    }
}
