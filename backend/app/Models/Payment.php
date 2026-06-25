<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['order_id', 'amount', 'method', 'reference', 'status', 'notes', 'change_due', 'amount_tendered', 'processed_by', 'paid_at'];

    protected $casts = ['paid_at' => 'datetime', 'amount' => 'decimal:2', 'change_due' => 'decimal:2', 'amount_tendered' => 'decimal:2'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
