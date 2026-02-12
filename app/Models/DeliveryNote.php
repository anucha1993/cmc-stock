<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class DeliveryNote extends Model
{
    protected $fillable = [
        'delivery_number',
        'sales_order_number',
        'quotation_number',
        'customer_name',
        'customer_phone',
        'delivery_date',
        'status',
        'notes',
        'created_by',
        'confirmed_by',
        'confirmed_at',
        'scanned_by',
        'scanned_at',
        'approved_by',
        'approved_at',
        'discrepancy_notes'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'confirmed_at' => 'datetime',
        'scanned_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deliveryNote) {
            if (empty($deliveryNote->delivery_number)) {
                $deliveryNote->delivery_number = self::generateDeliveryNumber();
            }
        });
    }

    /**
     * สร้างเลขที่ใบส่งของอัตโนมัติ
     */
    public static function generateDeliveryNumber(): string
    {
        $prefix = 'DN';
        $date = now()->format('Ymd');
        
        $lastDeliveryNote = self::whereDate('created_at', today())
            ->where('delivery_number', 'like', $prefix . $date . '%')
            ->orderBy('delivery_number', 'desc')
            ->first();

        if ($lastDeliveryNote) {
            $lastNumber = (int) substr($lastDeliveryNote->delivery_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * ยืนยันใบตัดสต็อก
     */
    public function confirm($userId)
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_by' => $userId,
            'confirmed_at' => now()
        ]);
    }

    /**
     * ตรวจสอบความถูกต้องหลังสแกน
     */
    public function checkDiscrepancies(): array
    {
        $discrepancies = [];
        
        foreach ($this->items as $item) {
            $planned = $item->quantity;
            $scanned = $item->scanned_quantity;
            
            if ($scanned != $planned) {
                $discrepancies[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'planned' => $planned,
                    'scanned' => $scanned,
                    'difference' => $scanned - $planned,
                    'status' => $scanned > $planned ? 'over' : 'under'
                ];
            }
        }
        
        return $discrepancies;
    }

    /**
     * อนุมัติและตัดสต็อก
     */
    public function approveAndCutStock($userId, $forceApprove = false)
    {
        DB::beginTransaction();
        try {
            $discrepancies = $this->checkDiscrepancies();
            
            // ถ้ามี discrepancy และไม่ได้บังคับอนุมัติ
            if (!empty($discrepancies) && !$forceApprove) {
                return [
                    'success' => false,
                    'discrepancies' => $discrepancies,
                    'message' => 'พบความไม่ตรงกันระหว่างรายการที่วางแผนกับที่สแกนจริง'
                ];
            }

            // ตัดสต็อกจริง
            foreach ($this->items as $item) {
                $scannedBarcodes = $item->scanned_items ?? [];
                
                foreach ($scannedBarcodes as $barcode) {
                    $stockItem = StockItem::where('barcode', $barcode)
                        ->where('status', 'available')
                        ->first();
                    
                    if ($stockItem) {
                        $stockItem->changeStatus('sold', 'ขายผ่านใบส่งของ ' . $this->delivery_number);
                        
                        // หาจำนวน stock ปัจจุบัน
                        $warehouseProduct = \App\Models\WarehouseProduct::where('product_id', $stockItem->product_id)
                            ->where('warehouse_id', $stockItem->warehouse_id)
                            ->first();
                        
                        $beforeQuantity = $warehouseProduct ? $warehouseProduct->quantity : 0;
                        
                        // บันทึก Inventory Transaction
                        InventoryTransaction::create([
                            'transaction_code' => InventoryTransaction::generateTransactionCode(),
                            'transaction_date' => now(),
                            'product_id' => $stockItem->product_id,
                            'warehouse_id' => $stockItem->warehouse_id,
                            'type' => 'out',
                            'quantity' => 1,
                            'before_quantity' => $beforeQuantity,
                            'after_quantity' => $beforeQuantity - 1,
                            'reference_type' => 'delivery_note',
                            'reference_id' => $this->id,
                            'notes' => 'ขายสินค้า SN: ' . $stockItem->serial_number,
                            'user_id' => $userId
                        ]);
                        
                        // อัพเดทจำนวนสต๊อกในคลัง
                        if ($warehouseProduct) {
                            $warehouseProduct->decrement('quantity', 1);
                        }
                    }
                }
            }

            // อัปเดตสถานะ
            $this->update([
                'status' => 'completed',
                'approved_by' => $userId,
                'approved_at' => now(),
                'discrepancy_notes' => !empty($discrepancies) ? json_encode($discrepancies) : null
            ]);

            DB::commit();
            
            return [
                'success' => true,
                'message' => 'ตัดสต็อกเรียบร้อยแล้ว',
                'discrepancies' => $discrepancies
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeScanned($query)
    {
        return $query->where('status', 'scanned');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Accessors
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'scanned' => 'primary',
            'completed' => 'success',
            default => 'secondary'
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'รอยืนยัน',
            'confirmed' => 'ยืนยันแล้ว',
            'scanned' => 'สแกนแล้ว',
            'completed' => 'เสร็จสิ้น',
            default => 'ไม่ทราบสถานะ'
        };
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getTotalScannedAttribute(): int
    {
        return $this->items->sum('scanned_quantity');
    }

    public function getHasDiscrepanciesAttribute(): bool
    {
        return !empty($this->checkDiscrepancies());
    }
}
