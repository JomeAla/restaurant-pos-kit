<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Reservation::with('table', 'createdBy');

        if ($request->date) {
            $query->where('date', $request->date);
        }
        if ($request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest('date')->latest('time_slot')->paginate($request->per_page ?? 50));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'party_size' => 'required|integer|min:1',
            'table_id' => 'nullable|exists:restaurant_tables,id',
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string|max:20',
            'notes' => 'nullable|string',
            'walk_in' => 'boolean',
        ]);

        if ($validated['table_id'] && !$this->isTableAvailable($validated['table_id'], $validated['date'], $validated['time_slot'])) {
            return response()->json(['message' => 'Table is not available at this time slot.'], 409);
        }

        $reservation = Reservation::create([
            ...$validated,
            'walk_in' => $validated['walk_in'] ?? false,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($reservation->load('table', 'createdBy'), 201);
    }

    public function show(Reservation $reservation): JsonResponse
    {
        return response()->json($reservation->load('table', 'createdBy'));
    }

    public function update(Request $request, Reservation $reservation): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'sometimes|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'party_size' => 'sometimes|integer|min:1',
            'table_id' => 'nullable|exists:restaurant_tables,id',
            'date' => 'sometimes|date',
            'time_slot' => 'sometimes|string|max:20',
            'status' => 'sometimes|in:' . implode(',', Reservation::$statuses),
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['table_id']) && $validated['table_id'] != $reservation->table_id) {
            $checkDate = $validated['date'] ?? $reservation->date;
            $checkSlot = $validated['time_slot'] ?? $reservation->time_slot;
            if (!$this->isTableAvailable($validated['table_id'], $checkDate, $checkSlot, $reservation->id)) {
                return response()->json(['message' => 'Table is not available at this time slot.'], 409);
            }
        }

        $reservation->update($validated);

        return response()->json($reservation->load('table', 'createdBy'));
    }

    public function destroy(Reservation $reservation): JsonResponse
    {
        $reservation->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Reservation cancelled.']);
    }

    public function availableSlots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'party_size' => 'nullable|integer|min:1',
            'time_slot' => 'nullable|string',
        ]);

        $tables = RestaurantTable::where('status', '!=', 'occupied');

        if ($validated['party_size'] ?? null) {
            $tables->where('capacity', '>=', $validated['party_size']);
        }

        $tables = $tables->get();

        $unavailableTableIds = Reservation::where('date', $validated['date'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($validated['time_slot'] ?? null, fn($q) => $q->where('time_slot', $validated['time_slot']))
            ->pluck('table_id')
            ->filter()
            ->values()
            ->toArray();

        $available = $tables->reject(fn($t) => in_array($t->id, $unavailableTableIds))->values();

        return response()->json(['available' => $available, 'unavailable_table_ids' => $unavailableTableIds]);
    }

    private function isTableAvailable(int $tableId, string $date, string $timeSlot, ?int $excludeId = null): bool
    {
        $query = Reservation::where('table_id', $tableId)
            ->where('date', $date)
            ->where('time_slot', $timeSlot)
            ->whereIn('status', ['pending', 'confirmed']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }
}
