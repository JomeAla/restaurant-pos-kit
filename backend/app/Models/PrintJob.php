<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends Model
{
    protected $fillable = ['order_id', 'type', 'status', 'printer_ip', 'printer_port', 'content', 'error_message', 'attempts'];

    protected $casts = ['attempts' => 'integer', 'printer_port' => 'integer'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
