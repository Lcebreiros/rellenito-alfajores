<?php
// app/Models/SupplyPurchase.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyPurchase extends Model {
  protected $fillable = ['supply_id','qty','unit','unit_to_base','total_cost'];
  public function supply(): BelongsTo { return $this->belongsTo(Supply::class); }
  public function baseQty(): float { return (float) $this->qty * (float) $this->unit_to_base; }
  public function costPerBase(): float {
    $b = $this->baseQty(); return $b > 0 ? (float)$this->total_cost / $b : 0.0;
  }
}
