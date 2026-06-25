<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PrintJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function receipt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'printer_ip' => 'nullable|string',
            'printer_port' => 'nullable|integer',
        ]);

        $order = Order::with('items.menuItem', 'payments', 'table')->findOrFail($validated['order_id']);

        $printJob = PrintJob::create([
            'order_id' => $order->id,
            'type' => 'receipt',
            'status' => 'pending',
            'printer_ip' => $validated['printer_ip'] ?? null,
            'printer_port' => $validated['printer_port'] ?? 9100,
        ]);

        return response()->json($printJob, 201);
    }

    public function kitchenTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'printer_ip' => 'nullable|string',
            'printer_port' => 'nullable|integer',
        ]);

        $order = Order::with('items.menuItem', 'table')->findOrFail($validated['order_id']);

        $printJob = PrintJob::create([
            'order_id' => $order->id,
            'type' => 'kitchen_ticket',
            'status' => 'pending',
            'printer_ip' => $validated['printer_ip'] ?? null,
            'printer_port' => $validated['printer_port'] ?? 9100,
        ]);

        return response()->json($printJob, 201);
    }
}
