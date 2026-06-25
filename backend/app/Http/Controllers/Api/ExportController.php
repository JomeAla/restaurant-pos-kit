<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function orders(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Order::with(['items.menuItem', 'table', 'payments']);
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->status) $query->where('status', $request->status);

        $orders = $query->orderBy('created_at', 'desc')->get();

        $headers = ['Order #', 'Date', 'Type', 'Table', 'Customer', 'Items', 'Subtotal', 'Tax', 'Total', 'Status', 'Payment Method', 'Reference'];
        $rows = $orders->map(fn ($o) => [
            $o->order_number, $o->created_at->format('Y-m-d H:i'), $o->type,
            $o->table?->table_number ?? '', $o->customer_name ?? '',
            $o->items->count(), $o->subtotal, $o->tax_total, $o->total,
            $o->status, $o->payments->first()?->method ?? '', $o->payments->first()?->reference ?? '',
        ]);

        return $this->csvResponse('orders-export.csv', $headers, $rows);
    }

    public function inventory(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $items = InventoryItem::with('category')->orderBy('name')->get();

        $headers = ['Name', 'Category', 'SKU', 'Current Stock', 'Min Stock', 'Unit', 'Cost per Unit'];
        $rows = $items->map(fn ($i) => [
            $i->name, $i->category?->name ?? '', $i->sku ?? '',
            $i->current_stock, $i->min_stock, $i->unit ?? '', $i->cost_per_unit ?? 0,
        ]);

        return $this->csvResponse('inventory-export.csv', $headers, $rows);
    }

    public function payments(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Payment::with(['order', 'processedBy']);
        $payments = $query->orderBy('paid_at', 'desc')->get();

        $headers = ['ID', 'Order #', 'Amount', 'Method', 'Reference', 'Status', 'Processed By', 'Paid At'];
        $rows = $payments->map(fn ($p) => [
            $p->id, $p->order?->order_number ?? '', $p->amount,
            $p->method, $p->reference ?? '', $p->status,
            $p->processedBy?->name ?? '', $p->paid_at?->format('Y-m-d H:i') ?? '',
        ]);

        return $this->csvResponse('payments-export.csv', $headers, $rows);
    }

    public function menuItems(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $items = MenuItem::with('category')->orderBy('name')->get();

        $headers = ['Name', 'Category', 'Price', 'Cost', 'Course', 'Available'];
        $rows = $items->map(fn ($i) => [
            $i->name, $i->category?->name ?? '', $i->price, $i->cost ?? 0,
            $i->course ?? '', $i->is_available ? 'Yes' : 'No',
        ]);

        return $this->csvResponse('menu-items-export.csv', $headers, $rows);
    }

    protected function csvResponse(string $filename, array $headers, iterable $rows): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $callback = function () use ($headers, $rows) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, $headers);
            foreach ($rows as $row) {
                fputcsv($fh, $row);
            }
            fclose($fh);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
