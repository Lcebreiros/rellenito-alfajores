<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GeneratedReport extends Model
{
    protected $fillable = [
        'user_id',
        'frequency_type',
        'period_start',
        'period_end',
        'file_path',
        'file_size',
        'status',
        'error_message',
        'downloaded_at',
    ];

    protected $casts = [
        'period_start'   => 'date',
        'period_end'     => 'date',
        'file_size'      => 'integer',
        'downloaded_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready' && $this->file_path && Storage::exists($this->file_path);
    }

    public function fileSizeFormatted(): string
    {
        if (!$this->file_size) return '—';
        $kb = $this->file_size / 1024;
        return $kb < 1024
            ? round($kb, 1) . ' KB'
            : round($kb / 1024, 2) . ' MB';
    }

    public function periodLabel(): string
    {
        $labels = [
            'weekly'     => 'Semanal',
            'monthly'    => 'Mensual',
            'quarterly'  => 'Trimestral',
            'semiannual' => 'Semestral',
            'annual'     => 'Anual',
            'manual'     => 'Manual',
        ];
        return $labels[$this->frequency_type] ?? $this->frequency_type;
    }

    public function markDownloaded(): void
    {
        $this->update(['downloaded_at' => now()]);
    }
}
