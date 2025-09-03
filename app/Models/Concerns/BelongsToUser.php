<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToUser
{
    public static function bootBelongsToUser(): void
    {
        static::creating(function ($model) {
            if (empty($model->user_id) && Auth::check()) {
                $model->user_id = Auth::id();
            }
        });

        static::addGlobalScope('byUser', function (Builder $query) {
            if (app()->runningInConsole() || !Auth::check()) return;
            $table = $query->getModel()->getTable();
            $query->where("{$table}.user_id", Auth::id());
        });
    }

    public function scopeForUser(Builder $query, int|string $userId): Builder
    {
        $table = $query->getModel()->getTable();
        return $query->withoutGlobalScope('byUser')->where("{$table}.user_id", $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
