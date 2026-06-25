<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modifier extends Model
{
    protected $fillable = ['name', 'type', 'is_required', 'min_selection', 'max_selection'];

    protected $casts = ['is_required' => 'boolean', 'min_selection' => 'integer', 'max_selection' => 'integer'];

    public function options(): HasMany
    {
        return $this->hasMany(ModifierOption::class);
    }
}
