<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(RestaurantTable::with('floorPlan')->orderBy('table_number')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_number' => 'required|integer|unique:restaurant_tables',
            'capacity' => 'required|integer|min:1',
            'section' => 'nullable|string|max:255',
            'status' => 'string|in:free,occupied,reserved,dirty',
            'pos_x' => 'nullable|numeric',
            'pos_y' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'shape' => 'string|in:rectangle,circle',
            'floor_plan_id' => 'nullable|exists:floor_plans,id',
        ]);

        return response()->json(RestaurantTable::create($validated), 201);
    }

    public function show(RestaurantTable $restaurantTable): JsonResponse
    {
        return response()->json($restaurantTable->load('floorPlan'));
    }

    public function update(Request $request, RestaurantTable $restaurantTable): JsonResponse
    {
        $validated = $request->validate([
            'table_number' => 'sometimes|integer|unique:restaurant_tables,table_number,' . $restaurantTable->id,
            'capacity' => 'sometimes|integer|min:1',
            'section' => 'nullable|string|max:255',
            'status' => 'string|in:free,occupied,reserved,dirty',
            'pos_x' => 'nullable|numeric',
            'pos_y' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'shape' => 'string|in:rectangle,circle',
            'floor_plan_id' => 'nullable|exists:floor_plans,id',
        ]);

        $restaurantTable->update($validated);

        return response()->json($restaurantTable->load('floorPlan'));
    }

    public function destroy(RestaurantTable $restaurantTable): JsonResponse
    {
        $restaurantTable->delete();

        return response()->json(['message' => 'Table deleted.']);
    }

    public function updateStatus(Request $request, RestaurantTable $restaurantTable): JsonResponse
    {
        $validated = $request->validate(['status' => 'required|in:free,occupied,reserved,dirty']);

        $restaurantTable->update($validated);

        return response()->json($restaurantTable);
    }
}
