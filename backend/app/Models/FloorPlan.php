<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FloorPlan extends Model
{
    protected $fillable = ['name', 'width', 'height', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class);
    }
}
