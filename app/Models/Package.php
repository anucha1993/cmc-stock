<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'package_quantity',
        'length_per_package',
        'length_unit',
        'items_per_package',
        'item_unit',
        'weight_per_package',
        'weight_unit',
        'cost_per_package',
        'selling_price_per_package',
        'color',
        'sort_order',
        'is_active',
        'supplier_id',
        'category_id'
    ];

    protected $casts = [
        'package_quantity' => 'integer',
        'length_per_package' => 'decimal:2',
        'items_per_package' => 'integer',
        'weight_per_package' => 'decimal:2',
        'cost_per_package' => 'decimal:2',
        'selling_price_per_package' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'total_length' => 'decimal:2',
        'total_items' => 'integer',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Stock items ที่อยู่ในแพนี้
     */
    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }

    /**
     * Stock items ที่พร้อมใช้งาน
     */
    public function availableStockItems(): HasMany
    {
        return $this->hasMany(StockItem::class)->available();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'package_products')
                    ->withPivot([
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
                    ])
                    ->withTimestamps()
                    ->orderBy('package_products.sort_order');
    }

    public function packageProducts()
    {
        return $this->hasMany(PackageProduct::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Accessors
    public function getTotalLengthAttribute()
    {
        return $this->length_per_package * $this->package_quantity;
    }

    public function getTotalItemsAttribute()
    {
        return $this->items_per_package * $this->package_quantity;
    }

    public function getTotalCostAttribute()
    {
        return $this->cost_per_package * $this->package_quantity;
    }

    public function getTotalSellingPriceAttribute()
    {
        return $this->selling_price_per_package * $this->package_quantity;
    }

    public function getMainProductAttribute()
    {
        return $this->products()
                    ->orderBy('package_products.sort_order')
                    ->first();
    }

    public function getTextColorAttribute()
    {
        // Calculate text color based on background color
        $color = str_replace('#', '', $this->color);
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        
        return $brightness > 155 ? '#000000' : '#ffffff';
    }

    /**
     * Get text color method for consistency with Category model
     */
    public function getTextColor(): string
    {
        return $this->getTextColorAttribute();
    }

    // Static methods
    public static function generateCode($name = null)
    {
        $prefix = 'PKG';
        $lastPackage = self::where('code', 'like', $prefix . '%')
                          ->orderBy('code', 'desc')
                          ->first();

        if ($lastPackage) {
            $lastNumber = (int) substr($lastPackage->code, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    // Business methods
    public function calculateTotalProducts()
    {
        return $this->packageProducts()
                    ->selectRaw('SUM(quantity_per_package * ' . $this->package_quantity . ') as total')
                    ->value('total') ?? 0;
    }

    public function calculateTotalWeight()
    {
        $productWeight = $this->packageProducts()
                             ->selectRaw('SUM((weight_per_unit * quantity_per_package) * ' . $this->package_quantity . ') as total')
                             ->value('total') ?? 0;
        
        $packageWeight = $this->weight_per_package * $this->package_quantity;
        
        return $productWeight + $packageWeight;
    }

    public function calculateTotalCost()
    {
        $productCost = $this->packageProducts()
                           ->selectRaw('SUM((cost_per_unit * quantity_per_package) * ' . $this->package_quantity . ') as total')
                           ->value('total') ?? 0;
        
        $packageCost = $this->cost_per_package * $this->package_quantity;
        
        return $productCost + $packageCost;
    }

    public function calculateTotalSellingPrice()
    {
        $productPrice = $this->packageProducts()
                            ->selectRaw('SUM((selling_price_per_unit * quantity_per_package) * ' . $this->package_quantity . ') as total')
                            ->value('total') ?? 0;
        
        $packagePrice = $this->selling_price_per_package * $this->package_quantity;
        
        return $productPrice + $packagePrice;
    }

    public function getProductList()
    {
        return $this->products()
                    ->get()
                    ->map(function ($product) {
                        return [
                            'product' => $product,
                            'quantity' => $product->pivot->quantity_per_package * $this->package_quantity,
                            'unit' => $product->pivot->unit,
                            'total_length' => $product->pivot->length_per_unit * $product->pivot->quantity_per_package * $this->package_quantity,
                            'total_weight' => $product->pivot->weight_per_unit * $product->pivot->quantity_per_package * $this->package_quantity,
                            'total_cost' => $product->pivot->cost_per_unit * $product->pivot->quantity_per_package * $this->package_quantity,
                            'total_selling_price' => $product->pivot->selling_price_per_unit * $product->pivot->quantity_per_package * $this->package_quantity,
                            'grade' => $product->pivot->grade,
                            'size' => $product->pivot->size,
                            'specifications' => $product->pivot->specifications,
                        ];
                    });
    }

    // Import to warehouse
    public function importToWarehouse($warehouseId, $notes = null, $userId = null)
    {
        $results = [];
        
        foreach ($this->getProductList() as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];
            
            // Update warehouse stock
            $warehouseProduct = WarehouseProduct::firstOrCreate([
                'warehouse_id' => $warehouseId,
                'product_id' => $product->id,
            ]);
            
            $oldQuantity = $warehouseProduct->quantity;
            $warehouseProduct->increment('quantity', $quantity);
            $warehouseProduct->increment('available_quantity', $quantity);
            
            // Create inventory transaction
            $product->inventoryTransactions()->create([
                'transaction_code' => InventoryTransaction::generateTransactionCode(),
                'type' => 'import_package',
                'quantity' => $quantity,
                'before_quantity' => $oldQuantity,
                'after_quantity' => $oldQuantity + $quantity,
                'notes' => "นำเข้าจากแพ: {$this->name} ({$this->code})" . ($notes ? " - {$notes}" : ""),
                'reference_type' => 'package',
                'reference_id' => $this->id,
                'user_id' => $userId,
                'transaction_date' => now()
            ]);
            
            $results[] = [
                'product' => $product,
                'quantity' => $quantity,
                'success' => true
            ];
        }
        
        return $results;
    }
}