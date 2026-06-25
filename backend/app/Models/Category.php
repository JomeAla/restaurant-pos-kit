<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'image', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
