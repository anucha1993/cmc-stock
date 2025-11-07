<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseProduct extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'location_code',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'available_quantity' => 'integer'
    ];

    /**
     * ความสัมพันธ์กับคลัง
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * ความสัมพันธ์กับสินค้า
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * อัปเดต available_quantity อัตโนมัติ
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($warehouseProduct) {
            $warehouseProduct->available_quantity = $warehouseProduct->quantity - $warehouseProduct->reserved_quantity;
        });
    }

    /**
     * จองสต็อก
     */
    public function reserve($quantity): bool
    {
        if ($this->available_quantity >= $quantity) {
            $this->reserved_quantity += $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * ยกเลิกการจอง
     */
    public function unreserve($quantity): bool
    {
        if ($this->reserved_quantity >= $quantity) {
            $this->reserved_quantity -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * ยืนยันการใช้สต็อกที่จองไว้
     */
    public function commitReserved($quantity): bool
    {
        if ($this->reserved_quantity >= $quantity) {
            $this->quantity -= $quantity;
            $this->reserved_quantity -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * สถานะสต็อก
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity == 0) {
            return 'out_of_stock';
        } elseif ($this->available_quantity <= 0) {
            return 'all_reserved';
        } elseif ($this->quantity <= $this->product->min_stock) {
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
            case 'all_reserved':
                return 'warning';
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
            case 'all_reserved':
                return 'จองหมดแล้ว';
            case 'low_stock':
                return 'สต็อกต่ำ';
            default:
                return 'ปกติ';
        }
    }
}
