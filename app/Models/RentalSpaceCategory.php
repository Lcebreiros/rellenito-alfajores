<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class RentalSpaceCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'color',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function spaces(): HasMany
    {
        return $this->hasMany(RentalSpace::class, 'category_id');
    }

    public function scopeForCompany(Builder $query, User $user): Builder
    {
        $companyId = $user->isCompany() ? $user->id : $user->parent_id;
        return $query->where('company_id', $companyId);
    }
}
