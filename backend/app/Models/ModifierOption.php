<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModifierOption extends Model
{
    protected $fillable = ['modifier_id', 'name', 'price_adjustment', 'is_default', 'is_active'];

    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean', 'price_adjustment' => 'decimal:2'];

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(Modifier::class);
    }
}
