<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = ['user_id', 'subject', 'category', 'priority', 'status', 'assigned_to', 'resolved_at'];

    protected $casts = ['resolved_at' => 'datetime'];

    public static array $priorities = ['low', 'medium', 'high', 'urgent'];
    public static array $statuses = ['open', 'assigned', 'in_progress', 'resolved', 'closed'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }
}
