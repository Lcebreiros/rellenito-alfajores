<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'document_number',
        'company',
        'address',
        'city',
        'province',
        'country',
        'tags',
        'notes',
        'balance',
    ];

    protected $casts = [
        'tags'    => 'array',
        'balance' => 'decimal:2',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
