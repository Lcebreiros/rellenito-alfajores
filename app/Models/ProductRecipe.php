<?php
// app/Models/ProductRecipe.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRecipe extends Model {
  protected $fillable = ['product_id','supply_id','qty','unit','waste_pct'];
  public function product(): BelongsTo { return $this->belongsTo(Product::class); }
  public function supply(): BelongsTo  { return $this->belongsTo(Supply::class); }
}
