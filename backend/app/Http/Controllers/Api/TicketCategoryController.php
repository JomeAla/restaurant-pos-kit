<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(TicketCategory::orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:ticket_categories,slug',
            'is_active' => 'boolean',
        ]);

        return response()->json(TicketCategory::create($validated), 201);
    }

    public function show(TicketCategory $ticketCategory): JsonResponse
    {
        return response()->json($ticketCategory);
    }

    public function update(Request $request, TicketCategory $ticketCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:ticket_categories,slug,' . $ticketCategory->id,
            'is_active' => 'boolean',
        ]);

        $ticketCategory->update($validated);

        return response()->json($ticketCategory);
    }

    public function destroy(TicketCategory $ticketCategory): JsonResponse
    {
        $ticketCategory->delete();

        return response()->json(['message' => 'Ticket category deleted.']);
    }
}
