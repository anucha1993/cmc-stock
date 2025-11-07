<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'color',
        'icon',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * รายการสินค้าในหมวดหมู่นี้
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * รายการสินค้าที่ใช้งานในหมวดหมู่นี้
     */
    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    /**
     * แพสินค้าในหมวดหมู่นี้
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    /**
     * แพสินค้าที่ใช้งานในหมวดหมู่นี้
     */
    public function activePackages()
    {
        return $this->hasMany(Package::class)->where('is_active', true);
    }

    /**
     * สร้างรหัสหมวดหมู่อัตโนมัติ
     */
    public static function generateCode($name): string
    {
        // สร้างรหัสจากชื่อ
        $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        
        // ถ้าชื่อสั้นกว่า 3 ตัวอักษร ให้เติม CAT
        if (strlen($baseCode) < 3) {
            $baseCode = 'CAT';
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
     * Scope สำหรับหมวดหมู่ที่ใช้งาน
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * นับจำนวนสินค้าในหมวดหมู่
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * นับจำนวนสินค้าที่ใช้งานในหมวดหมู่
     */
    public function getActiveProductsCountAttribute(): int
    {
        return $this->activeProducts()->count();
    }

    /**
     * คำนวณสีตัวอักษรที่เหมาะสมกับสีพื้นหลัง
     */
    public function getTextColor(): string
    {
        if (!$this->color) {
            return '#000000';
        }

        // แปลงสี hex เป็น RGB
        $hex = ltrim($this->color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // คำนวณความสว่างของสี
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        // ถ้าสีสว่าง ให้ใช้ตัวอักษรสีดำ ถ้าสีมืด ให้ใช้ตัวอักษรสีขาว
        return $brightness > 155 ? '#000000' : '#ffffff';
    }
}
