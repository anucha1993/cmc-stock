<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCheckItem extends Model
{
    protected $fillable = [
        'session_id',
        'barcode',
        'product_id',
        'stock_item_id',
        'scanned_count',
        'location_found',
        'status',
        'notes',
        'first_scanned_at',
        'last_scanned_at',
        'scanned_by'
    ];

    protected $casts = [
        'first_scanned_at' => 'datetime',
        'last_scanned_at' => 'datetime'
    ];

    const STATUS_FOUND = 'found';
    const STATUS_NOT_IN_SYSTEM = 'not_in_system';
    const STATUS_DUPLICATE = 'duplicate';
    const STATUS_CONFIRMED = 'confirmed';

    // Relationships
    public function session(): BelongsTo
    {
        return $this->belongsTo(StockCheckSession::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    // Methods - ไม่เปลี่ยนสถานะเป็น duplicate เพราะ barcode ไม่ซ้ำกัน
    public function incrementScan($userId)
    {
        $this->increment('scanned_count');
        $this->update([
            'last_scanned_at' => now(),
            'scanned_by' => $userId
            // ไม่เปลี่ยนสถานะ - เก็บสถานะเดิม (found/not_in_system)
        ]);
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_FOUND => 'พบในระบบ',
            self::STATUS_NOT_IN_SYSTEM => 'ไม่มีในระบบ',
            self::STATUS_DUPLICATE => 'สแกนซ้ำ',
            self::STATUS_CONFIRMED => 'ยืนยันแล้ว',
            default => 'ไม่ทราบสถานะ'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_FOUND => 'success',
            self::STATUS_NOT_IN_SYSTEM => 'warning',
            self::STATUS_DUPLICATE => 'info',
            self::STATUS_CONFIRMED => 'primary',
            default => 'secondary'
        };
    }
}