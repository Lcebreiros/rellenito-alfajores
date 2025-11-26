<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = ['user_id','subject','status','type'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function messages(): HasMany
    {
        // Mantener siempre el orden cronológico ascendente para que el chat no se invierta
        return $this->hasMany(SupportMessage::class, 'support_chat_id')
            ->orderBy('created_at');
    }
}
