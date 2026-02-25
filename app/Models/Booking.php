<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'rental_space_id',
        'rental_duration_option_id',
        'client_id',
        'client_name',
        'client_phone',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'status',
        'total_amount',
        'notes',
        'google_calendar_event_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'duration_minutes' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    // ---------- RELACIONES ----------

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(RentalSpace::class, 'rental_space_id');
    }

    public function durationOption(): BelongsTo
    {
        return $this->belongsTo(RentalDurationOption::class, 'rental_duration_option_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'booking_payment_methods')
            ->withPivot(['amount'])
            ->withTimestamps();
    }

    // ---------- SCOPES ----------

    public function scopeForCompany(Builder $query, User $user): Builder
    {
        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            return $query;
        }

        $companyId = $user->isCompany() ? $user->id : $user->parent_id;
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeForDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('starts_at', $date->toDateString());
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('starts_at', $year)->whereMonth('starts_at', $month);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now())->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeOverlapping(Builder $query, int $spaceId, Carbon $start, Carbon $end, ?int $excludeId = null): Builder
    {
        return $query
            ->where('rental_space_id', $spaceId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId));
    }

    // ---------- HELPERS ----------

    public function getClientDisplayName(): string
    {
        if ($this->client) {
            return $this->client->name;
        }
        return $this->client_name ?? 'Sin nombre';
    }

    public function getClientDisplayPhone(): ?string
    {
        if ($this->client) {
            return $this->client->phone;
        }
        return $this->client_phone;
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function isPast(): bool
    {
        return $this->ends_at->isPast();
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'   => 'Pendiente',
            'confirmed' => 'Confirmada',
            'finished'  => 'Finalizada',
            'cancelled' => 'Cancelada',
            default     => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'pending'   => 'yellow',
            'confirmed' => 'green',
            'finished'  => 'gray',
            'cancelled' => 'red',
            default     => 'gray',
        };
    }
}
