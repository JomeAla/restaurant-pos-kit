<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(MenuItem::with('category', 'modifiers.options')->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|alpha_dash|unique:menu_items',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'numeric|min:0',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'tax_rate' => 'numeric|min:0|max:100',
        ]);

        return response()->json(MenuItem::create($validated), 201);
    }

    public function show(MenuItem $menuItem): JsonResponse
    {
        return response()->json($menuItem->load('category', 'modifiers.options'));
    }

    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|alpha_dash|unique:menu_items,slug,' . $menuItem->id,
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'cost' => 'numeric|min:0',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'tax_rate' => 'numeric|min:0|max:100',
        ]);

        $menuItem->update($validated);

        return response()->json($menuItem->load('category', 'modifiers.options'));
    }

    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $menuItem->delete();

        return response()->json(['message' => 'Menu item deleted.']);
    }

    public function toggleAvailability(MenuItem $menuItem): JsonResponse
    {
        $menuItem->update(['is_available' => !$menuItem->is_available]);

        return response()->json($menuItem);
    }
}
