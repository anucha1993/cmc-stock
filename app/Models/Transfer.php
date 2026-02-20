<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transfer_code',
        'from_warehouse_id',
        'to_warehouse_id',
        'product_id',
        'quantity',
        'status',
        'reason',
        'notes',
        'requested_by',
        'approved_by',
        'requested_at',
        'approved_at',
        'completed_at'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * คลังต้นทาง
     */
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * คลังปลายทาง
     */
    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * สินค้าที่โยกย้าย
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * ผู้ขอโยกย้าย
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * ผู้อนุมัติ
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * สร้างรหัสการโยกย้ายอัตโนมัติ
     */
    public static function generateTransferCode(): string
    {
        $date = date('Ymd');
        $counter = 1;
        $code = 'TF' . $date . sprintf('%04d', $counter);
        
        while (self::where('transfer_code', $code)->exists()) {
            $counter++;
            $code = 'TF' . $date . sprintf('%04d', $counter);
        }
        
        return $code;
    }

    /**
     * Scope สำหรับสถานะที่รอดำเนินการ
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope สำหรับสถานะกำลังขนส่ง
     */
    public function scopeInTransit($query)
    {
        return $query->where('status', self::STATUS_IN_TRANSIT);
    }

    /**
     * Scope สำหรับสถานะเสร็จสิ้น
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * ข้อความสถานะ
     */
    public function getStatusTextAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'รอดำเนินการ';
            case self::STATUS_IN_TRANSIT:
                return 'กำลังขนส่ง';
            case self::STATUS_COMPLETED:
                return 'เสร็จสิ้น';
            case self::STATUS_CANCELLED:
                return 'ยกเลิก';
            default:
                return 'ไม่ระบุ';
        }
    }

    /**
     * สีสถานะ
     */
    public function getStatusColorAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'warning';
            case self::STATUS_IN_TRANSIT:
                return 'info';
            case self::STATUS_COMPLETED:
                return 'success';
            case self::STATUS_CANCELLED:
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * อนุมัติการโยกย้าย
     */
    public function approve($userId = null): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        // ตรวจสอบสต็อกในคลังต้นทาง
        $fromStock = $this->fromWarehouse->getProductStock($this->product_id);
        if ($fromStock < $this->quantity) {
            throw new \Exception('สต็อกในคลังต้นทางไม่เพียงพอ');
        }

        $this->update([
            'status' => self::STATUS_IN_TRANSIT,
            'approved_by' => $userId ?: \Illuminate\Support\Facades\Auth::id(),
            'approved_at' => now()
        ]);

        // จองสต็อกในคลังต้นทาง
        $warehouseProduct = $this->fromWarehouse->warehouseProducts()
                                               ->where('product_id', $this->product_id)
                                               ->first();
        if ($warehouseProduct) {
            $warehouseProduct->reserve($this->quantity);
        }

        return true;
    }

    /**
     * เสร็จสิ้นการโยกย้าย
     */
    public function complete(): bool
    {
        if ($this->status !== self::STATUS_IN_TRANSIT) {
            return false;
        }

        // ลดสต็อกจากคลังต้นทาง
        $fromWarehouseProduct = $this->fromWarehouse->warehouseProducts()
                                                   ->where('product_id', $this->product_id)
                                                   ->first();
        if ($fromWarehouseProduct) {
            $fromWarehouseProduct->commitReserved($this->quantity);
        }

        // เพิ่มสต็อกในคลังปลายทาง
        $this->toWarehouse->addStock($this->product_id, $this->quantity);

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now()
        ]);

        // บันทึกประวัติใน inventory_transactions
        $this->product->inventoryTransactions()->create([
            'transaction_code' => InventoryTransaction::generateTransactionCode(),
            'type' => 'out',
            'quantity' => -$this->quantity,
            'before_quantity' => $fromWarehouseProduct ? $fromWarehouseProduct->quantity + $this->quantity : $this->quantity,
            'after_quantity' => $fromWarehouseProduct ? $fromWarehouseProduct->quantity : 0,
            'notes' => "โยกย้ายออกจาก {$this->fromWarehouse->name} ไป {$this->toWarehouse->name}",
            'reference_type' => 'transfer',
            'reference_id' => $this->id,
            'user_id' => $this->approved_by,
            'transaction_date' => now()
        ]);

        $toWarehouseProduct = $this->toWarehouse->warehouseProducts()
                                               ->where('product_id', $this->product_id)
                                               ->first();

        $this->product->inventoryTransactions()->create([
            'transaction_code' => InventoryTransaction::generateTransactionCode(),
            'type' => 'in',
            'quantity' => $this->quantity,
            'before_quantity' => $toWarehouseProduct ? $toWarehouseProduct->quantity - $this->quantity : 0,
            'after_quantity' => $toWarehouseProduct ? $toWarehouseProduct->quantity : $this->quantity,
            'notes' => "รับโยกย้ายจาก {$this->fromWarehouse->name} มา {$this->toWarehouse->name}",
            'reference_type' => 'transfer',
            'reference_id' => $this->id,
            'user_id' => $this->approved_by,
            'transaction_date' => now()
        ]);

        return true;
    }

    /**
     * ยกเลิกการโยกย้าย
     */
    public function cancel($reason = null): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_TRANSIT])) {
            return false;
        }

        // ถ้าอยู่ในสถานะ in_transit ต้องยกเลิกการจอง
        if ($this->status === self::STATUS_IN_TRANSIT) {
            $warehouseProduct = $this->fromWarehouse->warehouseProducts()
                                                   ->where('product_id', $this->product_id)
                                                   ->first();
            if ($warehouseProduct) {
                $warehouseProduct->unreserve($this->quantity);
            }
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $this->notes . "\nยกเลิก: " . $reason
        ]);

        return true;
    }
}
