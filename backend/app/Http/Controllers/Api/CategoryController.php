<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Category::withCount('menuItems')->orderBy('sort_order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|alpha_dash|unique:categories',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        return response()->json(Category::create($validated), 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json($category->load('menuItems'));
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|alpha_dash|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}
