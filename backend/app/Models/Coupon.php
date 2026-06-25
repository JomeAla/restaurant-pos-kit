<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'min_order_amount', 'max_usage_count', 'per_customer_limit', 'applicable_item_ids', 'applicable_category_ids', 'is_active', 'starts_at', 'ends_at'];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_usage_count' => 'integer',
        'per_customer_limit' => 'integer',
        'applicable_item_ids' => 'array',
        'applicable_category_ids' => 'array',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(?string $customerIdentifier = null, ?float $orderAmount = null): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at && now()->gt($this->ends_at)) return false;
        if ($this->max_usage_count && $this->usages()->count() >= $this->max_usage_count) return false;
        if ($customerIdentifier && $this->per_customer_limit) {
            $count = $this->usages()->where('customer_identifier', $customerIdentifier)->count();
            if ($count >= $this->per_customer_limit) return false;
        }
        if ($orderAmount !== null && $this->min_order_amount && $orderAmount < $this->min_order_amount) return false;
        return true;
    }

    public function calculateDiscount(float $orderSubtotal): float
    {
        if ($this->type === 'percentage') {
            return min($orderSubtotal * ($this->value / 100), $orderSubtotal);
        }
        return min($this->value, $orderSubtotal);
    }

    public static function generateCode(): string
    {
        $prefix = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
        $suffix = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6));
        return $prefix . '-' . $suffix;
    }
}
