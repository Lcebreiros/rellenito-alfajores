<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationHistory extends Model
{
    protected $table = 'invitations_history';

    protected $fillable = [
        'invitation_id',
        'key',
        'email',
        'notes',
        'used_at',
        'used_by',
        'payload',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'used_by');
    }
}
