<?php
// app/Models/Supply.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supply extends Model {
  protected $fillable = ['name','base_unit','stock_base_qty','avg_cost_per_base'];
  public function purchases(): HasMany { return $this->hasMany(SupplyPurchase::class); }
}
