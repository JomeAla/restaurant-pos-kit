<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Combo extends Model
{
    protected $fillable = ['name', 'description', 'price', 'is_active', 'start_date', 'end_date'];

    protected $casts = ['is_active' => 'boolean', 'price' => 'decimal:2', 'start_date' => 'date', 'end_date' => 'date'];

    public function items(): HasMany
    {
        return $this->hasMany(ComboItem::class);
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'combo_items')->withPivot('quantity');
    }
}
