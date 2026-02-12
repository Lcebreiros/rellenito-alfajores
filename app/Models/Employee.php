<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'first_name',
        'last_name',
        'email',
        'dni',
        'photo_path',
        'contract_type',
        'contract_file_path',
        'address',
        'start_date',
        'role',
        'family_group',
        'evaluations',
        'objectives',
        'tasks',
        'schedules',
        'benefits',
        'medical_coverage',
        'has_computer',
        'salary',
        'notes',
    ];

    protected $casts = [
        'family_group' => 'array',
        'evaluations'  => 'array',
        'objectives'   => 'array',
        'tasks'        => 'array',
        'schedules'    => 'array',
        'benefits'     => 'array',
        'notes'        => 'array',
        'has_computer' => 'boolean',
        'start_date'   => 'date',
        'salary'       => 'decimal:2',
    ];

    // =======================
    // RELACIONES
    // =======================

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'attachable');
    }

    public function parkingShifts()
    {
        return $this->hasMany(ParkingShift::class, 'employee_id');
    }

    // =======================
    // ACCESORS
    // =======================

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return Storage::disk('public')->url($this->photo_path);
        }

        return asset('images/default-avatar.png');
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
