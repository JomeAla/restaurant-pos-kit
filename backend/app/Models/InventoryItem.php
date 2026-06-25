<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = ['name', 'sku', 'category', 'unit', 'current_stock', 'min_stock', 'cost_per_unit', 'supplier'];

    protected $casts = ['current_stock' => 'decimal:2', 'min_stock' => 'decimal:2', 'cost_per_unit' => 'decimal:2'];

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->min_stock;
    }

    public function adjustStock(float $quantity, string $type): void
    {
        if ($type === 'in') {
            $this->increment('current_stock', $quantity);
        } elseif ($type === 'out') {
            $this->decrement('current_stock', $quantity);
        }
    }
}
