<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $filleable = ['status','total'];
    protected $casts = ['total'=>'decimal:2'];

    const STATUS_DRAFT = 'draft';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';

    public function items(){ return $this->hasMany(OrderItem::class); }

    public function recalcTotal(): void
    {
        $this->total = $this->items()->sum('subtotal');
    }
}
