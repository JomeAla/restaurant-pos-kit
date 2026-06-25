<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitchenTicket extends Model
{
    protected $fillable = ['order_id', 'course', 'status', 'sent_at', 'bumped_at'];

    protected $casts = ['sent_at' => 'datetime', 'bumped_at' => 'datetime'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(KitchenTicketItem::class, 'ticket_id');
    }
}
