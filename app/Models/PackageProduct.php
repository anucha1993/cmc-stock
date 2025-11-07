<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'product_id',
        'quantity_per_package',
        'unit',
        'length_per_unit',
        'weight_per_unit',
        'cost_per_unit',
        'selling_price_per_unit',
        'grade',
        'size',
        'specifications',
        'sort_order',
        'is_main_product'
    ];

    protected $casts = [
        'quantity_per_package' => 'decimal:2',
        'length_per_unit' => 'decimal:2',
        'weight_per_unit' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'selling_price_per_unit' => 'decimal:2',
        'sort_order' => 'integer',
        'is_main_product' => 'boolean',
    ];

    // Relationships
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getTotalQuantityAttribute()
    {
        return $this->quantity_per_package * $this->package->package_quantity;
    }

    public function getTotalLengthAttribute()
    {
        return $this->length_per_unit * $this->quantity_per_package * $this->package->package_quantity;
    }

    public function getTotalWeightAttribute()
    {
        return $this->weight_per_unit * $this->quantity_per_package * $this->package->package_quantity;
    }

    public function getTotalCostAttribute()
    {
        return $this->cost_per_unit * $this->quantity_per_package * $this->package->package_quantity;
    }

    public function getTotalSellingPriceAttribute()
    {
        return $this->selling_price_per_unit * $this->quantity_per_package * $this->package->package_quantity;
    }

    // Scopes
    public function scopeMainProduct($query)
    {
        return $query->where('is_main_product', true);
    }

    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopeBySize($query, $size)
    {
        return $query->where('size', $size);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}