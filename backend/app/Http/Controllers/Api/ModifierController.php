<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModifierController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Modifier::with('options')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:single,multi',
            'is_required' => 'boolean',
            'min_selection' => 'integer|min:0',
            'max_selection' => 'integer|min:0',
        ]);

        return response()->json(Modifier::create($validated), 201);
    }

    public function show(Modifier $modifier): JsonResponse
    {
        return response()->json($modifier->load('options'));
    }

    public function update(Request $request, Modifier $modifier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:single,multi',
            'is_required' => 'boolean',
            'min_selection' => 'integer|min:0',
            'max_selection' => 'integer|min:0',
        ]);

        $modifier->update($validated);

        return response()->json($modifier->load('options'));
    }

    public function destroy(Modifier $modifier): JsonResponse
    {
        $modifier->delete();

        return response()->json(['message' => 'Modifier deleted.']);
    }

    public function groups(): JsonResponse
    {
        return response()->json(Modifier::with('options')->where('type', 'single')->get());
    }
}
