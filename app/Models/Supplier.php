<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'phone',
        'email',
        'address',
        'tax_id',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * รายการสินค้าจากผู้จำหน่ายนี้
     */
    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    /**
     * รายการสินค้าที่ใช้งานจากผู้จำหน่ายนี้
     */
    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    /**
     * สร้างรหัสผู้จำหน่ายอัตโนมัติ
     */
    public static function generateCode($name): string
    {
        // สร้างรหัสจากชื่อ
        $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        
        // ถ้าชื่อสั้นกว่า 3 ตัวอักษร ให้เติม SUP
        if (strlen($baseCode) < 3) {
            $baseCode = 'SUP';
        }
        
        $counter = 1;
        $code = $baseCode . sprintf('%03d', $counter);
        
        // ตรวจสอบว่าซ้ำหรือไม่
        while (self::where('code', $code)->exists()) {
            $counter++;
            $code = $baseCode . sprintf('%03d', $counter);
        }
        
        return $code;
    }

    /**
     * Scope สำหรับผู้จำหน่ายที่ใช้งาน
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * นับจำนวนสินค้าจากผู้จำหน่าย
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * นับจำนวนสินค้าที่ใช้งานจากผู้จำหน่าย
     */
    public function getActiveProductsCountAttribute(): int
    {
        return $this->activeProducts()->count();
    }
}
