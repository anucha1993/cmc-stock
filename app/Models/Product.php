<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'description',
        'category_id',
        'supplier_id',
        'unit',
        'size_type',
        'custom_size_options',
        'allow_custom_order',
        'length',
        'thickness',
        'steel_type',
        'side_steel_type',
        'measurement_unit',
        'min_stock',
        'max_stock',
        'stock_quantity',
        'location',
        'images',
        'is_active'
    ];

    protected $casts = [
        'length' => 'decimal:2',
        'thickness' => 'decimal:2',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'stock_quantity' => 'integer',
        'images' => 'array',
        'custom_size_options' => 'array',
        'is_active' => 'boolean',
        'allow_custom_order' => 'boolean'
    ];

    /**
     * Events
     */
    protected static function boot()
    {
        parent::boot();

        // สร้าง SKU และ Barcode อัตโนมัติเมื่อสร้างสินค้าใหม่
        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = self::generateSKU($product->name, $product->category_id);
            }
            
            if (empty($product->barcode)) {
                $product->barcode = BarcodeSequence::generateUniqueBarcode('product', 'CMC');
            }
        });
    }

    /**
     * ความสัมพันธ์กับหมวดหมู่
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * ความสัมพันธ์กับผู้จำหน่าย
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * ประวัติการเข้า-ออกสินค้า
     */
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Stock items ของสินค้านี้
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

    /**
     * สต็อกในแต่ละคลัง
     */
    public function warehouseProducts(): HasMany
    {
        return $this->hasMany(WarehouseProduct::class);
    }

    /**
     * การโยกย้ายสินค้า
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    /**
     * คำสั่งผลิต
     */
    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    /**
     * แพสินค้า
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_products')
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

    /**
     * รายการสินค้าในแพ
     */
    public function packageProducts()
    {
        return $this->hasMany(PackageProduct::class);
    }

    /**
     * สร้าง SKU อัตโนมัติ
     */
    public static function generateSKU($name, $categoryId = null): string
    {
        $prefix = 'PRD';
        
        if ($categoryId) {
            $category = Category::find($categoryId);
            if ($category) {
                $prefix = substr($category->code, 0, 3);
            }
        }
        
        // สร้าง SKU จากชื่อสินค้า
        $namePart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        if (strlen($namePart) < 3) {
            $namePart = $prefix;
        }
        
        $counter = 1;
        $sku = $prefix . $namePart . sprintf('%04d', $counter);
        
        // ตรวจสอบว่าซ้ำหรือไม่
        while (self::where('sku', $sku)->exists()) {
            $counter++;
            $sku = $prefix . $namePart . sprintf('%04d', $counter);
        }
        
        return $sku;
    }

    /**
     * Scope สำหรับสินค้าที่ใช้งาน
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope สำหรับสินค้าที่สต็อกต่ำ
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock');
    }

    /**
     * Scope สำหรับสินค้าหมด
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', 0);
    }

    /**
     * ตรวจสอบว่าเป็นไซส์มาตรฐานหรือไม่
     */
    public function isStandardSize(): bool
    {
        return $this->size_type === 'standard';
    }

    /**
     * ตรวจสอบว่าเป็นไซส์กำหนดเองหรือไม่
     */
    public function isCustomSize(): bool
    {
        return $this->size_type === 'custom';
    }

    /**
     * รับตัวเลือกไซส์กำหนดเอง
     */
    public function getCustomSizeOptionsArrayAttribute(): array
    {
        if (!$this->isCustomSize() || empty($this->custom_size_options)) {
            return [];
        }
        
        return is_array($this->custom_size_options) ? $this->custom_size_options : [];
    }

    /**
     * ข้อความประเภทไซส์
     */
    public function getSizeTypeTextAttribute(): string
    {
        switch ($this->size_type) {
            case 'standard':
                return 'ไซส์มาตรฐาน';
            case 'custom':
                return 'ไซส์กำหนดเอง';
            default:
                return 'ไม่ระบุ';
        }
    }

    /**
     * สีสำหรับประเภทไซส์
     */
    public function getSizeTypeColorAttribute(): string
    {
        switch ($this->size_type) {
            case 'standard':
                return 'info';
            case 'custom':
                return 'warning';
            default:
                return 'secondary';
        }
    }

    /**
     * ข้อความประเภทเหล็ก
     */
    public function getSteelTypeTextAttribute(): string
    {
        switch ($this->steel_type) {
            case 'not_specified':
                return 'ไม่ระบุ';
            case 'wire_4':
                return 'ลวด 4 เส้น';
            case 'wire_5':
                return 'ลวด 5 เส้น';
            case 'wire_6':
                return 'ลวด 6 เส้น';
            case 'wire_7':
                return 'ลวด 7 เส้น';
            default:
                return 'ไม่ระบุ';
        }
    }

    /**
     * ข้อความประเภทเหล็กข้าง
     */
    public function getSideSteelTypeTextAttribute(): string
    {
        switch ($this->side_steel_type) {
            case 'not_specified':
                return 'ไม่ระบุ';
            case 'no_side_steel':
                return 'ไม่ Show เหล็กข้าง';
            case 'show_side_steel':
                return 'Show เหล็กข้าง';
            default:
                return 'ไม่ระบุ';
        }
    }

    /**
     * ข้อความหน่วยวัด
     */
    public function getMeasurementUnitTextAttribute(): string
    {
        switch ($this->measurement_unit) {
            case 'meter':
                return 'เมตร';
            case 'centimeter':
                return 'เซ็นติเมตร';
            case 'millimeter':
                return 'มิลลิเมตร';
            default:
                return 'เมตร';
        }
    }

    /**
     * ข้อความหน่วยวัดแบบสั้น
     */
    public function getMeasurementUnitShortAttribute(): string
    {
        switch ($this->measurement_unit) {
            case 'meter':
                return 'ม.';
            case 'centimeter':
                return 'ซม.';
            case 'millimeter':
                return 'มม.';
            default:
                return 'ม.';
        }
    }

    /**
     * ตรวจสอบสถานะสต็อกรวมทุกคลัง
     */
    public function getStockStatusAttribute(): string
    {
        $totalStock = $this->getTotalStockAttribute();
        if ($totalStock == 0) {
            return 'out_of_stock';
        } elseif ($totalStock <= $this->min_stock) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * สีสถานะสต็อก
     */
    public function getStockStatusColorAttribute(): string
    {
        switch ($this->stock_status) {
            case 'out_of_stock':
                return 'danger';
            case 'low_stock':
                return 'warning';
            default:
                return 'success';
        }
    }

    /**
     * ข้อความสถานะสต็อก
     */
    public function getStockStatusTextAttribute(): string
    {
        switch ($this->stock_status) {
            case 'out_of_stock':
                return 'หมดสต็อก';
            case 'low_stock':
                return 'สต็อกต่ำ';
            default:
                return 'ปกติ';
        }
    }

    /**
     * สต็อกรวมทุกคลัง
     */
    public function getTotalStockAttribute(): int
    {
        return $this->warehouseProducts()->sum('quantity');
    }

    /**
     * สต็อกพร้อมใช้รวมทุกคลัง
     */
    public function getAvailableStockAttribute(): int
    {
        return $this->warehouseProducts()->sum('available_quantity');
    }

    /**
     * สต็อกที่จองไว้รวมทุกคลัง
     */
    public function getReservedStockAttribute(): int
    {
        return $this->warehouseProducts()->sum('reserved_quantity');
    }

    /**
     * ได้สต็อกจากคลังที่ระบุ
     */
    public function getWarehouseStock($warehouseId): int
    {
        $warehouseProduct = $this->warehouseProducts()->where('warehouse_id', $warehouseId)->first();
        return $warehouseProduct ? $warehouseProduct->quantity : 0;
    }

    /**
     * ได้รายการคลังที่มีสินค้านี้
     */
    public function getWarehousesWithStock()
    {
        return $this->warehouseProducts()
                   ->with('warehouse')
                   ->where('quantity', '>', 0)
                   ->get()
                   ->pluck('warehouse');
    }



    /**
     * รูปภาพหลัก
     */
    public function getMainImageAttribute(): ?string
    {
        if (empty($this->images) || !is_array($this->images)) {
            return null;
        }
        
        return asset('storage/' . $this->images[0]);
    }

    /**
     * อัปเดตสต็อกในคลังที่ระบุ
     */
    public function updateWarehouseStock($warehouseId, $quantity, $type = 'adjustment', $notes = null, $userId = null)
    {
        $warehouse = Warehouse::findOrFail($warehouseId);
        $warehouseProduct = $this->warehouseProducts()->where('warehouse_id', $warehouseId)->first();
        
        $oldQuantity = $warehouseProduct ? $warehouseProduct->quantity : 0;
        
        switch ($type) {
            case 'in':
                $warehouse->addStock($this->id, $quantity, null, $notes);
                $newQuantity = $oldQuantity + $quantity;
                break;
            case 'out':
                $warehouse->removeStock($this->id, $quantity);
                $newQuantity = $oldQuantity - $quantity;
                break;
            case 'adjustment':
                // สำหรับการปรับปรุงสต็อก
                if ($warehouseProduct) {
                    $warehouseProduct->quantity = $quantity;
                    $warehouseProduct->available_quantity = $quantity - $warehouseProduct->reserved_quantity;
                    $warehouseProduct->save();
                } else {
                    $warehouse->addStock($this->id, $quantity, null, $notes);
                }
                $newQuantity = $quantity;
                break;
        }
        
        // บันทึกประวัติการเปลี่ยนแปลง
        $this->inventoryTransactions()->create([
            'transaction_code' => InventoryTransaction::generateTransactionCode(),
            'type' => $type,
            'quantity' => $type === 'adjustment' ? ($newQuantity - $oldQuantity) : ($type === 'out' ? -$quantity : $quantity),
            'before_quantity' => $oldQuantity,
            'after_quantity' => $newQuantity,
            'notes' => $notes . " (คลัง: {$warehouse->name})",
            'reference_type' => 'warehouse',
            'reference_id' => $warehouseId,
            'user_id' => $userId ?: (\Illuminate\Support\Facades\Auth::id() ?? 1),
            'transaction_date' => now()
        ]);
    }
}
