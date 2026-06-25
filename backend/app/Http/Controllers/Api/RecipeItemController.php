<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecipeItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RecipeItem::with('menuItem', 'inventoryItem');

        if ($request->menu_item_id) {
            $query->where('menu_item_id', $request->menu_item_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $exists = RecipeItem::where('menu_item_id', $validated['menu_item_id'])
            ->where('inventory_item_id', $validated['inventory_item_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'This ingredient is already linked to this menu item.'], 409);
        }

        return response()->json(RecipeItem::create($validated), 201);
    }

    public function update(Request $request, RecipeItem $recipeItem): JsonResponse
    {
        $validated = $request->validate(['quantity' => 'required|numeric|min:0.01']);

        $recipeItem->update($validated);

        return response()->json($recipeItem->load('menuItem', 'inventoryItem'));
    }

    public function destroy(RecipeItem $recipeItem): JsonResponse
    {
        $recipeItem->delete();

        return response()->json(['message' => 'Recipe item removed.']);
    }
}
