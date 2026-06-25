<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['supplier', 'items', 'total_cost', 'status', 'ordered_at', 'received_at'];

    protected $casts = ['items' => 'array', 'total_cost' => 'decimal:2', 'ordered_at' => 'datetime', 'received_at' => 'datetime'];

    public static array $statuses = ['pending', 'received', 'cancelled'];
}
