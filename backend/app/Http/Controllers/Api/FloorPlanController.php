<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FloorPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FloorPlanController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(FloorPlan::with('tables')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'width' => 'required|integer|min:100',
            'height' => 'required|integer|min:100',
            'is_active' => 'boolean',
        ]);

        return response()->json(FloorPlan::create($validated), 201);
    }

    public function show(FloorPlan $floorPlan): JsonResponse
    {
        return response()->json($floorPlan->load('tables'));
    }

    public function update(Request $request, FloorPlan $floorPlan): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'width' => 'sometimes|integer|min:100',
            'height' => 'sometimes|integer|min:100',
            'is_active' => 'boolean',
        ]);

        $floorPlan->update($validated);

        return response()->json($floorPlan->load('tables'));
    }

    public function destroy(FloorPlan $floorPlan): JsonResponse
    {
        $floorPlan->delete();

        return response()->json(['message' => 'Floor plan deleted.']);
    }
}
