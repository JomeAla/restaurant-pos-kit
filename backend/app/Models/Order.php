<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['order_number', 'user_id', 'table_id', 'customer_name', 'customer_phone', 'type', 'status', 'subtotal', 'tax_total', 'discount_total', 'total', 'notes', 'void_reason', 'ordered_at', 'coupon_id', 'coupon_code'];

    protected $casts = ['ordered_at' => 'datetime', 'subtotal' => 'decimal:2', 'tax_total' => 'decimal:2', 'discount_total' => 'decimal:2', 'total' => 'decimal:2'];

    public static array $statuses = ['pending', 'sent', 'preparing', 'ready', 'served', 'paid', 'closed', 'voided'];

    public static array $types = ['dine-in', 'takeaway', 'delivery'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function kitchenTickets(): HasMany
    {
        return $this->hasMany(KitchenTicket::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function splitBills(): HasMany
    {
        return $this->hasMany(SplitBill::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $flow = [
            'pending' => ['sent', 'voided'],
            'sent' => ['preparing', 'voided'],
            'preparing' => ['ready', 'voided'],
            'ready' => ['served', 'voided'],
            'served' => ['paid', 'voided'],
            'paid' => ['closed'],
            'closed' => [],
            'voided' => [],
        ];

        return in_array($newStatus, $flow[$this->status] ?? []);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->where('status', '!=', 'voided')->sum('total_price');
        $this->total = $this->subtotal + $this->tax_total - $this->discount_total;
        $this->save();
    }
}
