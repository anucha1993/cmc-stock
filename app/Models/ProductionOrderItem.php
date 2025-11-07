<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderItem extends Model
{
    protected $fillable = [
        'production_order_id',
        'product_id',
        'quantity',
        'produced_quantity',
        'unit_cost',
        'total_cost',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'produced_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // Relationships
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getProgressPercentageAttribute(): float
    {
        if ($this->quantity == 0) return 0;
        return ($this->produced_quantity / $this->quantity) * 100;
    }

    public function getRemainingQuantityAttribute(): int
    {
        return $this->quantity - $this->produced_quantity;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->produced_quantity >= $this->quantity;
    }

    // Business Methods
    public function updateProgress($producedQuantity, $notes = null): bool
    {
        if ($producedQuantity > $this->quantity) {
            throw new \Exception('จำนวนที่ผลิตไม่สามารถมากกว่าจำนวนที่สั่งได้');
        }

        $this->update([
            'produced_quantity' => $producedQuantity,
            'notes' => $this->notes . ($notes ? "\n" . now()->format('Y-m-d H:i') . ": " . $notes : '')
        ]);

        return true;
    }

    public function calculateTotalCost(): void
    {
        if ($this->unit_cost) {
            $this->update([
                'total_cost' => $this->quantity * $this->unit_cost
            ]);
        }
    }
}
