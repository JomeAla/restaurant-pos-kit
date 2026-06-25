<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    protected $fillable = ['user_id', 'opened_at', 'closed_at', 'opening_cash', 'closing_cash', 'expected_cash', 'difference', 'notes'];

    protected $casts = ['opened_at' => 'datetime', 'closed_at' => 'datetime', 'opening_cash' => 'decimal:2', 'closing_cash' => 'decimal:2', 'expected_cash' => 'decimal:2', 'difference' => 'decimal:2'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
