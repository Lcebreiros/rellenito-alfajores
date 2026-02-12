<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'event_type',
        'title',
        'description',
        'color',
        'event_date',
        'due_date',
        'reminder_date',
        'completed_at',
        'status',
        'related_type',
        'related_id',
        'metadata',
        'is_recurring',
        'recurrence_pattern',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'due_date' => 'datetime',
        'reminder_date' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
        'is_recurring' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        return $query->whereBetween('event_date', [$startOfMonth, $endOfMonth]);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->where('due_date', '<', now())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('due_date')
                         ->where('event_date', '<', now());
                  });
            });
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'pending')
            ->where('event_date', '>=', now())
            ->orderBy('event_date', 'asc');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function markAsCompleted(): self
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $this;
    }

    public function checkAndUpdateOverdueStatus(): void
    {
        if ($this->status === 'pending') {
            $checkDate = $this->due_date ?? $this->event_date;
            if ($checkDate < now()) {
                $this->update(['status' => 'overdue']);
            }
        }
    }
}
