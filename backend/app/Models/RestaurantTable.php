<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantTable extends Model
{
    protected $fillable = ['table_number', 'capacity', 'section', 'status', 'pos_x', 'pos_y', 'width', 'height', 'shape', 'floor_plan_id'];

    protected $casts = ['pos_x' => 'decimal:2', 'pos_y' => 'decimal:2', 'width' => 'decimal:2', 'height' => 'decimal:2'];

    public function floorPlan(): BelongsTo
    {
        return $this->belongsTo(FloorPlan::class);
    }
}
