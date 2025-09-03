<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;

class Order extends Model
{
    use BelongsToUser;

    protected $fillable = ['user_id','status','total']; // <— fix aquí
    protected $casts = ['total'=>'decimal:2'];

    const STATUS_DRAFT     = 'draft';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED  = 'canceled';

    public function items(){ return $this->hasMany(OrderItem::class); }

    public function recalcTotal(): void
    {
        $this->total = $this->items()->sum('subtotal'); // recuerda $this->save() donde corresponda
    }
}
