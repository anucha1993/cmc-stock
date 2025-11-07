<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarcodeSequence extends Model
{
    protected $fillable = [
        'prefix',
        'type',
        'current_number',
        'padding',
        'format_example'
    ];

    protected $casts = [
        'current_number' => 'integer',
        'padding' => 'integer'
    ];

    /**
     * สร้าง barcode ถัดไป
     */
    public static function generateNext($type = 'product', $prefix = 'CMC'): string
    {
        $sequence = self::firstOrCreate(
            ['prefix' => $prefix, 'type' => $type],
            [
                'current_number' => 1,
                'padding' => 8,
                'format_example' => $prefix . '00000001'
            ]
        );

        $barcode = $prefix . str_pad($sequence->current_number, $sequence->padding, '0', STR_PAD_LEFT);
        
        // อัปเดตเลขถัดไป
        $sequence->increment('current_number');
        
        return $barcode;
    }

    /**
     * ตรวจสอบว่า barcode ซ้ำหรือไม่
     */
    public static function isUniqueBarcode($barcode, $type = 'product'): bool
    {
        switch ($type) {
            case 'product':
                return !Product::where('barcode', $barcode)->exists();
            default:
                return true;
        }
    }

    /**
     * สร้าง barcode ที่ไม่ซ้ำ
     */
    public static function generateUniqueBarcode($type = 'product', $prefix = 'CMC'): string
    {
        do {
            $barcode = self::generateNext($type, $prefix);
        } while (!self::isUniqueBarcode($barcode, $type));
        
        return $barcode;
    }
}
