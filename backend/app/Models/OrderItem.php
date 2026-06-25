<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'menu_item_id', 'quantity', 'unit_price', 'total_price', 'modifier_summary', 'status', 'notes', 'course', 'void_reason'];

    protected $casts = ['modifier_summary' => 'array', 'quantity' => 'integer', 'unit_price' => 'decimal:2', 'total_price' => 'decimal:2'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
