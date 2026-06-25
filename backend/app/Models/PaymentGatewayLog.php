<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewayLog extends Model
{
    protected $fillable = ['payment_gateway_id', 'gateway', 'request_payload', 'response_payload', 'status', 'reference', 'amount'];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'amount' => 'decimal:2',
    ];

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }
}
