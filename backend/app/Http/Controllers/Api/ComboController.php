<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComboController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Combo::with('items.menuItem')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'items' => 'array',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'integer|min:1',
        ]);

        $combo = Combo::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'is_active' => $validated['is_active'] ?? true,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        if (isset($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $combo->items()->create($item);
            }
        }

        return response()->json($combo->load('items.menuItem'), 201);
    }

    public function show(Combo $combo): JsonResponse
    {
        return response()->json($combo->load('items.menuItem'));
    }

    public function update(Request $request, Combo $combo): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'items' => 'array',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'integer|min:1',
        ]);

        $combo->update($validated);

        if (isset($validated['items'])) {
            $combo->items()->delete();
            foreach ($validated['items'] as $item) {
                $combo->items()->create($item);
            }
        }

        return response()->json($combo->fresh()->load('items.menuItem'));
    }

    public function destroy(Combo $combo): JsonResponse
    {
        $combo->delete();

        return response()->json(['message' => 'Combo deleted.']);
    }
}
