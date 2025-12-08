<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Warehouse;
use App\Models\StockItem;
use App\Models\WarehouseProduct;

class CheckStockCount extends Command
{
    protected $signature = 'stock:check-count';
    protected $description = 'Check actual stock count in warehouse';

    public function handle()
    {
        $warehouse = Warehouse::first();
        if (!$warehouse) {
            $this->error('No warehouse found');
            return;
        }

        $this->info('=== Warehouse: ' . $warehouse->name . ' ===');

        // Count real StockItems
        $totalStockItems = StockItem::where('warehouse_id', $warehouse->id)
            ->where('status', 'available')
            ->count();

        $this->line('Total StockItems (status=available): ' . $totalStockItems);

        // Count grouped by barcode
        $byBarcode = StockItem::where('warehouse_id', $warehouse->id)
            ->where('status', 'available')
            ->with('product:id,name,barcode')
            ->get()
            ->groupBy('barcode');

        $this->line('Products by barcode: ' . $byBarcode->count());
        foreach ($byBarcode as $barcode => $items) {
            $product = $items->first()->product;
            $this->line("  - {$barcode} ({$product->name}): " . $items->count() . ' items');
        }

        // Check WarehouseProduct
        $wpTotal = WarehouseProduct::where('warehouse_id', $warehouse->id)
            ->where('available_quantity', '>', 0)
            ->sum('available_quantity');

        $this->line("\nWarehouseProduct available_quantity total: " . $wpTotal);

        $wpCount = WarehouseProduct::where('warehouse_id', $warehouse->id)
            ->where('available_quantity', '>', 0)
            ->count();

        $this->line('WarehouseProduct rows: ' . $wpCount);

        // Check discrepancy
        if ($totalStockItems !== $wpTotal) {
            $this->warn("\n⚠️  MISMATCH: StockItems={$totalStockItems} vs WarehouseProduct={$wpTotal}");
        }
    }
}
