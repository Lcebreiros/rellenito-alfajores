<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToUser;

class DashboardWidget extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'widget_type',
        'position',
        'width',
        'height',
        'x',            // <—
        'y',            // <—
        'is_visible',
        'settings',
    ];

    protected $casts = [
        'settings'   => 'array',
        'is_visible' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
