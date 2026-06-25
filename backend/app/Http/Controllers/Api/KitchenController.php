<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KitchenTicket;
use App\Models\KitchenTicketItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KitchenController extends Controller
{
    public function index(): JsonResponse
    {
        $tickets = KitchenTicket::with(['order.table', 'items.orderItem.menuItem'])
            ->where('status', '!=', 'bumped')
            ->latest('sent_at')
            ->get();

        return response()->json($tickets);
    }

    public function sendToKitchen(Request $request, Order $order): JsonResponse
    {
        if (!$order->canTransitionTo('sent')) {
            return response()->json(['message' => 'Order cannot be sent to kitchen from current status.'], 422);
        }

        return DB::transaction(function () use ($order, $request) {
            $order->update(['status' => 'sent']);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => 'pending',
                'to_status' => 'sent',
                'changed_by' => $request->user()->id,
                'notes' => 'Sent to kitchen',
            ]);

            $pendingItems = $order->items()->where('status', 'pending')->get();

            if ($pendingItems->isEmpty()) {
                return response()->json(['message' => 'No pending items to send.'], 422);
            }

            $grouped = $pendingItems->groupBy(fn($item) => $item->course ?? 'all');

            foreach ($grouped as $course => $items) {
                $ticket = KitchenTicket::create([
                    'order_id' => $order->id,
                    'course' => $course === 'all' ? null : $course,
                    'status' => 'pending',
                    'sent_at' => now(),
                ]);

                foreach ($items as $item) {
                    KitchenTicketItem::create([
                        'ticket_id' => $ticket->id,
                        'order_item_id' => $item->id,
                        'status' => 'pending',
                    ]);
                }
            }

            return response()->json([
                'message' => 'Order sent to kitchen.',
                'tickets' => KitchenTicket::with(['order.table', 'items.orderItem.menuItem'])->where('order_id', $order->id)->get(),
            ]);
        });
    }

    public function updateItemStatus(Request $request, KitchenTicket $ticket, KitchenTicketItem $item): JsonResponse
    {
        if ($item->ticket_id !== $ticket->id) {
            return response()->json(['message' => 'Item not found on this ticket.'], 404);
        }

        $validated = $request->validate(['status' => 'required|in:pending,preparing,ready']);
        $item->update(['status' => $validated['status']]);

        $this->syncTicketStatus($ticket);

        return response()->json($item->load('orderItem.menuItem'));
    }

    public function bump(Request $request, KitchenTicket $ticket): JsonResponse
    {
        return DB::transaction(function () use ($ticket, $request) {
            $ticket->update(['status' => 'bumped', 'bumped_at' => now()]);

            $order = $ticket->order;
            $allBumped = $order->kitchenTickets()->where('status', '!=', 'bumped')->count() === 0;

            if ($allBumped && $order->status === 'sent') {
                $order->update(['status' => 'ready']);

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => 'sent',
                    'to_status' => 'ready',
                    'changed_by' => $request->user()->id,
                    'notes' => 'All tickets bumped - order ready',
                ]);
            }

            return response()->json(['message' => 'Ticket bumped.']);
        });
    }

    private function syncTicketStatus(KitchenTicket $ticket): void
    {
        $statuses = $ticket->items()->pluck('status');

        if ($statuses->every(fn($s) => $s === 'ready')) {
            $ticket->update(['status' => 'ready']);
        } elseif ($statuses->contains('preparing')) {
            $ticket->update(['status' => 'preparing']);
        } else {
            $ticket->update(['status' => 'pending']);
        }
    }
}
