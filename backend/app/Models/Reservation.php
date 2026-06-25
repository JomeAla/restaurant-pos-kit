<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = ['customer_name', 'customer_phone', 'customer_email', 'party_size', 'table_id', 'date', 'time_slot', 'status', 'notes', 'walk_in', 'created_by'];

    protected $casts = ['date' => 'date', 'walk_in' => 'boolean', 'party_size' => 'integer'];

    public static array $statuses = ['pending', 'confirmed', 'seated', 'cancelled'];

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
