<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'product_id',
        'quantity',
        'scanned_quantity',
        'notes',
        'scanned_items',
        'status'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'scanned_quantity' => 'integer',
        'scanned_items' => 'array'
    ];

    /**
     * Relationships
     */
    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * เพิ่มรายการ barcode ที่สแกน
     */
    public function addScannedItem($barcode, $serialNumber = null)
    {
        $scannedItems = $this->scanned_items ?? [];
        
        $scannedItems[] = [
            'barcode' => $barcode,
            'serial_number' => $serialNumber,
            'scanned_at' => now()->toDateTimeString()
        ];

        $this->update([
            'scanned_items' => $scannedItems,
            'scanned_quantity' => count($scannedItems),
            'status' => count($scannedItems) >= $this->quantity ? 'completed' : 'partial'
        ]);
    }

    /**
     * ลบรายการ barcode ที่สแกน
     */
    public function removeScannedItem($barcode)
    {
        $scannedItems = $this->scanned_items ?? [];
        
        $scannedItems = array_filter($scannedItems, function($item) use ($barcode) {
            return $item['barcode'] !== $barcode;
        });

        $scannedItems = array_values($scannedItems);

        $this->update([
            'scanned_items' => $scannedItems,
            'scanned_quantity' => count($scannedItems),
            'status' => count($scannedItems) >= $this->quantity ? 'completed' : (count($scannedItems) > 0 ? 'partial' : 'pending')
        ]);
    }

    /**
     * ตรวจสอบว่า barcode นี้สแกนแล้วหรือยัง
     */
    public function hasScannedBarcode($barcode): bool
    {
        $scannedItems = $this->scanned_items ?? [];
        
        foreach ($scannedItems as $item) {
            if ($item['barcode'] === $barcode) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Accessors
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'partial' => 'warning',
            'completed' => 'success',
            default => 'secondary'
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'รอสแกน',
            'partial' => 'สแกนบางส่วน',
            'completed' => 'สแกนครบ',
            default => 'ไม่ทราบสถานะ'
        };
    }

    public function getRemainingQuantityAttribute(): int
    {
        return $this->quantity - $this->scanned_quantity;
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->quantity == 0) {
            return 0;
        }
        
        return round(($this->scanned_quantity / $this->quantity) * 100, 2);
    }
}
