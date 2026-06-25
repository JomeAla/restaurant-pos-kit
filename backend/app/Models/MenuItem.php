<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MenuItem extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'cost', 'image', 'is_active', 'is_available', 'tax_rate'];

    protected $casts = ['is_active' => 'boolean', 'is_available' => 'boolean', 'price' => 'decimal:2', 'cost' => 'decimal:2', 'tax_rate' => 'decimal:2'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function modifiers(): BelongsToMany
    {
        return $this->belongsToMany(Modifier::class, 'menu_item_modifier');
    }
}
