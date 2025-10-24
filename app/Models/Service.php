<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use BelongsToUser, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'description',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'user_id' => 'integer',
        'company_id' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function company() { return $this->belongsTo(User::class, 'company_id'); }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailableFor(Builder $query, User $user): Builder
    {
        if ($user->isMaster()) {
            return $query;
        }
        if ($user->isCompany()) {
            return $query->where('company_id', $user->id);
        }
        if ($user->isAdmin()) {
            $company = $user->rootCompany();
            return $query->where(function ($q) use ($user, $company) {
                $q->where('user_id', $user->id)
                  ->orWhere('company_id', $company->id);
            });
        }
        // usuario regular: sus servicios o de su parent
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('user_id', $user->parent_id ?? 0);
        });
    }

    protected static function booted(): void
    {
        static::creating(function (Service $service) {
            if (!$service->company_id && $service->user) {
                $service->company_id = $service->user->rootCompany()?->id ?? $service->user_id;
            }
        });
    }
}
