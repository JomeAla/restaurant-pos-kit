<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketMessageController extends Controller
{
    public function index(SupportTicket $ticket): JsonResponse
    {
        if (!$this->canAccess($ticket)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($ticket->messages()->with('user')->orderBy('created_at', 'asc')->get());
    }

    public function store(Request $request, SupportTicket $ticket): JsonResponse
    {
        if (!$this->canAccess($ticket)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string',
        ]);

        $message = $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'attachments' => $validated['attachments'] ?? null,
            'is_staff_reply' => $request->user()->hasRole(['admin', 'manager']),
        ]);

        return response()->json($message->load('user'), 201);
    }

    private function canAccess(SupportTicket $ticket): bool
    {
        $user = request()->user();
        return $user->hasRole(['admin', 'manager']) || $ticket->user_id === $user->id;
    }
}
