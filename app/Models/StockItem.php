<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StockItem extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id', 
        'package_id',
        'barcode',
        'serial_number',
        'lot_number',
        'batch_number',
        'location_code',
        'status',
        'manufacture_date',
        'expire_date',
        'received_date',
        'cost_price',
        'selling_price',
        'grade',
        'size',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expire_date' => 'date',
        'received_date' => 'date',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2'
    ];

    /**
     * สินค้าหลัก
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * คลังที่เก็บ
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * แพที่สินค้าอยู่ (ถ้ามี)
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * ผู้สร้าง
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ผู้แก้ไขล่าสุด
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * สร้าง Barcode อัตโนมัติ
     */
    public static function generateBarcode($productId, $warehouseId): string
    {
        $product = Product::find($productId);
        $warehouse = Warehouse::find($warehouseId);
        
        $prefix = $product->code . $warehouse->code;
        $timestamp = now()->format('ymdHis');
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * สร้าง Serial Number อัตโนมัติ
     */
    public static function generateSerialNumber($productId): string
    {
        $product = Product::find($productId);
        $count = self::where('product_id', $productId)->count() + 1;
        
        return $product->code . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * ตรวจสอบว่าสินค้าหมดอายุหรือไม่
     */
    public function isExpired(): bool
    {
        return $this->expire_date && $this->expire_date->isPast();
    }

    /**
     * ตรวจสอบว่าสินค้าใกล้หมดอายุหรือไม่ (30 วัน)
     */
    public function isNearExpiry($days = 30): bool
    {
        return $this->expire_date && $this->expire_date->diffInDays(now()) <= $days;
    }

    /**
     * ตรวจสอบว่าสินค้าพร้อมใช้งานหรือไม่
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && !$this->isExpired();
    }

    /**
     * เปลี่ยนสถานะสินค้า
     */
    public function changeStatus($newStatus, $notes = null): void
    {
        $this->update([
            'status' => $newStatus,
            'notes' => $notes,
            'updated_by' => Auth::id()
        ]);
    }

    /**
     * ย้ายไปคลังใหม่
     */
    public function moveToWarehouse($warehouseId, $locationCode = null): void
    {
        $this->update([
            'warehouse_id' => $warehouseId,
            'location_code' => $locationCode,
            'updated_by' => Auth::id()
        ]);
    }

    /**
     * Scope สินค้าที่พร้อมใช้งาน
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                    ->where(function($q) {
                        $q->whereNull('expire_date')
                          ->orWhere('expire_date', '>', now());
                    });
    }

    /**
     * Scope สินค้าที่หมดอายุ
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expire_date')
                    ->where('expire_date', '<=', now());
    }

    /**
     * Scope สินค้าใกล้หมดอายุ
     */
    public function scopeNearExpiry($query, $days = 30)
    {
        return $query->whereNotNull('expire_date')
                    ->whereBetween('expire_date', [now(), now()->addDays($days)]);
    }

    /**
     * Scope ตาม Lot Number
     */
    public function scopeByLot($query, $lotNumber)
    {
        return $query->where('lot_number', $lotNumber);
    }

    /**
     * Scope ตาม Warehouse
     */
    public function scopeInWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * ได้วันที่คงเหลือก่อนหมดอายุ
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        return $this->expire_date ? $this->expire_date->diffInDays(now()) : null;
    }

    /**
     * ได้สีสถานะสำหรับแสดงผล
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'available' => 'success',
            'reserved' => 'warning', 
            'sold' => 'info',
            'damaged' => 'danger',
            'expired' => 'dark',
            'returned' => 'secondary',
            default => 'light'
        };
    }

    /**
     * ได้ข้อความสถานะภาษาไทย
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'available' => 'พร้อมใช้งาน',
            'reserved' => 'จองแล้ว',
            'sold' => 'ขายแล้ว',
            'damaged' => 'เสียหาย',
            'expired' => 'หมดอายุ',
            'returned' => 'ส่งคืน',
            default => 'ไม่ระบุ'
        };
    }
}
