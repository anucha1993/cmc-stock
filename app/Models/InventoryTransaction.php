<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'product_id',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'before_quantity',
        'after_quantity',
        'notes',
        'reference_type',
        'reference_id',
        'user_id',
        'transaction_date'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'before_quantity' => 'integer',
        'after_quantity' => 'integer',
        'reference_id' => 'integer',
        'user_id' => 'integer',
        'transaction_date' => 'datetime'
    ];

    /**
     * ความสัมพันธ์กับสินค้า
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * ความสัมพันธ์กับผู้ใช้ที่ทำรายการ
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope สำหรับการเข้าสินค้า
     */
    public function scopeIncoming($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope สำหรับการออกสินค้า
     */
    public function scopeOutgoing($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope สำหรับการปรับปรุงสต็อก
     */
    public function scopeAdjustment($query)
    {
        return $query->where('type', 'adjustment');
    }

    /**
     * ประเภทการทำรายการ
     */
    public function getTypeTextAttribute(): string
    {
        switch ($this->type) {
            case 'in':
                return 'รับเข้า';
            case 'out':
                return 'จ่ายออก';
            case 'adjustment':
                return 'ปรับปรุง';
            default:
                return 'ไม่ระบุ';
        }
    }

    /**
     * สีประเภทการทำรายการ
     */
    public function getTypeColorAttribute(): string
    {
        switch ($this->type) {
            case 'in':
                return 'success';
            case 'out':
                return 'danger';
            case 'adjustment':
                return 'warning';
            default:
                return 'secondary';
        }
    }

    /**
     * สร้างรหัสธุรกรรมอัตโนมัติ
     */
    public static function generateTransactionCode(): string
    {
        $date = date('Ymd');
        $counter = 1;
        $code = 'TXN' . $date . sprintf('%04d', $counter);
        
        while (self::where('transaction_code', $code)->exists()) {
            $counter++;
            $code = 'TXN' . $date . sprintf('%04d', $counter);
        }
        
        return $code;
    }
}
