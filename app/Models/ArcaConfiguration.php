<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ArcaConfiguration extends Model
{
    protected $fillable = [
        'company_id',
        'cuit',
        'business_name',
        'tax_condition',
        'environment',
        'certificate',
        'private_key',
        'certificate_password',
        'sale_points',
        'default_sale_point',
        'is_active',
        'certificate_expires_at',
        'last_sync_at',
        'enabled_voucher_types',
    ];

    protected $casts = [
        'sale_points' => 'array',
        'enabled_voucher_types' => 'array',
        'is_active' => 'boolean',
        'certificate_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Relación con la empresa/usuario
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    /**
     * Mutator: Encriptar el certificado al guardarlo
     */
    public function setCertificateAttribute($value)
    {
        if ($value) {
            $this->attributes['certificate'] = Crypt::encryptString($value);
        }
    }

    /**
     * Accessor: Desencriptar el certificado al leerlo
     */
    public function getCertificateAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Mutator: Encriptar la clave privada al guardarla
     */
    public function setPrivateKeyAttribute($value)
    {
        if ($value) {
            $this->attributes['private_key'] = Crypt::encryptString($value);
        }
    }

    /**
     * Accessor: Desencriptar la clave privada al leerla
     */
    public function getPrivateKeyAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Mutator: Encriptar la contraseña del certificado al guardarla
     */
    public function setCertificatePasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['certificate_password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Accessor: Desencriptar la contraseña del certificado al leerla
     */
    public function getCertificatePasswordAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Verificar si la configuración está completa
     */
    public function isConfigured(): bool
    {
        return !empty($this->cuit) &&
               !empty($this->certificate) &&
               !empty($this->private_key) &&
               $this->is_active;
    }

    /**
     * Verificar si el certificado está vigente
     */
    public function isCertificateValid(): bool
    {
        if (!$this->certificate_expires_at) {
            return false;
        }

        return $this->certificate_expires_at->isFuture();
    }

    /**
     * Obtener el ambiente como URL
     */
    public function getEnvironmentUrl(): string
    {
        return $this->environment === 'production'
            ? 'https://servicios1.afip.gov.ar/wsfev1/service.asmx?WSDL'
            : 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL';
    }
}
