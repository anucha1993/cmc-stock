<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'code',
        'location',
        'description',
        'is_active',
        'is_main'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_main' => 'boolean'
    ];

    /**
     * Stock items ในคลังนี้
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
     * สินค้าในคลัง
     */
    public function warehouseProducts(): HasMany
    {
        return $this->hasMany(WarehouseProduct::class);
    }

    /**
     * การโยกย้ายออกจากคลัง
     */
    public function transfersOut(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_warehouse_id');
    }

    /**
     * การโยกย้ายเข้าคลัง
     */
    public function transfersIn(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_warehouse_id');
    }

    /**
     * คำสั่งผลิตที่จะส่งมาคลังนี้
     */
    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class, 'target_warehouse_id');
    }

    /**
     * สร้างรหัสคลังอัตโนมัติ
     */
    public static function generateCode($name): string
    {
        $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        
        if (strlen($baseCode) < 3) {
            $baseCode = 'WH';
        }
        
        $counter = 1;
        $code = $baseCode . sprintf('%02d', $counter);
        
        while (self::where('code', $code)->exists()) {
            $counter++;
            $code = $baseCode . sprintf('%02d', $counter);
        }
        
        return $code;
    }

    /**
     * Scope คลังที่ใช้งาน
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope คลังหลัก
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    /**
     * เปอร์เซ็นต์การใช้งานคลัง
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->max_capacity == 0) return 0;
        return ($this->current_usage / $this->max_capacity) * 100;
    }

    /**
     * พื้นที่ว่างที่เหลือ
     */
    public function getRemainingCapacityAttribute(): float
    {
        return $this->max_capacity - $this->current_usage;
    }

    /**
     * นับจำนวนสินค้าในคลัง
     */
    public function getTotalProductsAttribute(): int
    {
        return $this->warehouseProducts()->count();
    }

    /**
     * นับจำนวนสต็อกรวมในคลัง
     */
    public function getTotalStockAttribute(): int
    {
        return $this->warehouseProducts()->sum('quantity');
    }

    /**
     * ตรวจสอบว่าสินค้ามีในคลังหรือไม่
     */
    public function hasProduct($productId): bool
    {
        return $this->warehouseProducts()->where('product_id', $productId)->exists();
    }

    /**
     * ได้จำนวนสต็อกของสินค้าในคลัง
     */
    public function getProductStock($productId): int
    {
        $warehouseProduct = $this->warehouseProducts()->where('product_id', $productId)->first();
        return $warehouseProduct ? $warehouseProduct->quantity : 0;
    }

    /**
     * เพิ่มสต็อกสินค้าในคลัง
     */
    public function addStock($productId, $quantity, $locationCode = null, $notes = null)
    {
        $warehouseProduct = $this->warehouseProducts()->firstOrCreate(
            ['product_id' => $productId],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'location_code' => $locationCode,
                'notes' => $notes
            ]
        );

        $warehouseProduct->quantity += $quantity;
        $warehouseProduct->available_quantity += $quantity;
        $warehouseProduct->save();

        return $warehouseProduct;
    }

    /**
     * ลดสต็อกสินค้าในคลัง
     */
    public function removeStock($productId, $quantity)
    {
        $warehouseProduct = $this->warehouseProducts()->where('product_id', $productId)->first();
        
        if (!$warehouseProduct || $warehouseProduct->available_quantity < $quantity) {
            throw new \Exception('สต็อกไม่เพียงพอสำหรับการจ่าย');
        }

        $warehouseProduct->quantity -= $quantity;
        $warehouseProduct->available_quantity -= $quantity;
        $warehouseProduct->save();

        return $warehouseProduct;
    }
}
