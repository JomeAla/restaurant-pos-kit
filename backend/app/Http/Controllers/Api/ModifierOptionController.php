<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModifierOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModifierOptionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ModifierOption::with('modifier')->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'modifier_id' => 'required|exists:modifiers,id',
            'name' => 'required|string|max:255',
            'price_adjustment' => 'numeric|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        return response()->json(ModifierOption::create($validated), 201);
    }

    public function show(ModifierOption $modifierOption): JsonResponse
    {
        return response()->json($modifierOption->load('modifier'));
    }

    public function update(Request $request, ModifierOption $modifierOption): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price_adjustment' => 'numeric|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $modifierOption->update($validated);

        return response()->json($modifierOption->load('modifier'));
    }

    public function destroy(ModifierOption $modifierOption): JsonResponse
    {
        $modifierOption->delete();

        return response()->json(['message' => 'Modifier option deleted.']);
    }
}
