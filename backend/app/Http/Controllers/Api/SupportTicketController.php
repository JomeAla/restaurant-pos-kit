<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::with(['user', 'assignedTo'])->withCount('messages');

        if (!$request->user()->hasRole(['admin', 'manager'])) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|string|in:' . implode(',', SupportTicket::$priorities),
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'category' => $validated['category'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
        ]);

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return response()->json($ticket->load(['user', 'messages.user']), 201);
    }

    public function show(SupportTicket $ticket): JsonResponse
    {
        if (!$this->canAccess($ticket)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($ticket->load(['user', 'assignedTo', 'messages.user']));
    }

    public function update(Request $request, SupportTicket $ticket): JsonResponse
    {
        if (!$this->canAccess($ticket)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|string|in:' . implode(',', SupportTicket::$priorities),
        ]);

        $ticket->update($validated);

        return response()->json($ticket->load(['user', 'assignedTo']));
    }

    public function destroy(SupportTicket $ticket): JsonResponse
    {
        if (!$this->canAccess($ticket)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted.']);
    }

    public function status(Request $request, SupportTicket $ticket): JsonResponse
    {
        if (!$this->canAccess($ticket)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', SupportTicket::$statuses),
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'resolved') {
            $data['resolved_at'] = now();
        }

        $ticket->update($data);

        return response()->json($ticket->load(['user', 'assignedTo']));
    }

    private function canAccess(SupportTicket $ticket): bool
    {
        $user = request()->user();
        return $user->hasRole(['admin', 'manager']) || $ticket->user_id === $user->id;
    }
}
