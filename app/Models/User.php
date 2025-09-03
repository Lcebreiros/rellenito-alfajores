<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable;

    public const DEFAULT_APP_LOGO      = 'images/logo.png';
    public const DEFAULT_RECEIPT_LOGO  = 'images/logo.png'; // mismo default si no sube uno

    protected $fillable = [
        'name', 'email', 'password',
        'has_seen_welcome',
        'app_logo_path',
        'theme', 'site_title', 'receipt_logo_path',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_recovery_codes', 'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
        'app_logo_url',
        'receipt_logo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'has_seen_welcome'  => 'boolean',
        ];
    }

    public function getAppLogoUrlAttribute(): string
    {
        if ($this->app_logo_path && Storage::disk('public')->exists($this->app_logo_path)) {
            $url = Storage::disk('public')->url($this->app_logo_path);
            $v   = Storage::disk('public')->lastModified($this->app_logo_path) ?: time();
            return "{$url}?v={$v}";
        }
        return asset(self::DEFAULT_APP_LOGO);
    }

    public function getReceiptLogoUrlAttribute(): string
    {
        if ($this->receipt_logo_path && Storage::disk('public')->exists($this->receipt_logo_path)) {
            $url = Storage::disk('public')->url($this->receipt_logo_path);
            $v   = Storage::disk('public')->lastModified($this->receipt_logo_path) ?: time();
            return "{$url}?v={$v}";
        }
        return asset(self::DEFAULT_RECEIPT_LOGO);
    }
}
