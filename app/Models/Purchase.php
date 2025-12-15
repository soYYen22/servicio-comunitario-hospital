<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
           'product','category_id','supplier_id',
            'cost_price','quantity','expiry_date',
            'entry_date','image','lote','sold','hidden_from_expired'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'hidden_from_expired' => 'boolean',
    ];

        public function remaining()
        {
         return max(0, (int) $this->quantity - (int) $this->sold);
        }

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function purchaseProduct(){
        return $this->hasOne(Product::class);
    }
}
