<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentGateway extends Model
{
    protected $fillable = ['gateway', 'label', 'credentials', 'is_sandbox', 'is_active', 'webhook_secret', 'webhook_url'];

    protected $casts = [
        'is_sandbox' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentGatewayLog::class);
    }

    public function getDecryptedCredentialsAttribute(): ?array
    {
        if (!$this->credentials) return null;
        return json_decode(decrypt($this->credentials), true);
    }

    public function setDecryptedCredentialsAttribute(?array $value): void
    {
        $this->credentials = $value ? encrypt(json_encode($value)) : null;
    }
}
