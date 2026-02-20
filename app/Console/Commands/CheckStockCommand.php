<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Warehouse;
use App\Models\StockItem;
use App\Models\WarehouseProduct;

class CheckStockCommand extends Command
{
    protected $signature = 'stock:check {--warehouse= : Warehouse ID to check}';
    protected $description = 'ตรวจสอบสต็อกสินค้าในคลัง';

    public function handle(): int
    {
        $warehouseId = $this->option('warehouse');

        if ($warehouseId) {
            $warehouse = Warehouse::find($warehouseId);
            if (!$warehouse) {
                $this->error("ไม่พบคลังสินค้า ID: {$warehouseId}");
                return self::FAILURE;
            }
            $this->checkWarehouse($warehouse);
        } else {
            $warehouses = Warehouse::all();
            if ($warehouses->isEmpty()) {
                $this->warn('ไม่พบคลังสินค้าในระบบ');
                return self::FAILURE;
            }
            foreach ($warehouses as $warehouse) {
                $this->checkWarehouse($warehouse);
                $this->newLine();
            }
        }

        return self::SUCCESS;
    }

    private function checkWarehouse(Warehouse $warehouse): void
    {
        $this->info("=== คลัง: {$warehouse->name} (ID: {$warehouse->id}) ===");

        // นับ StockItems ที่พร้อมใช้งาน
        $totalStockItems = StockItem::where('warehouse_id', $warehouse->id)
            ->where('status', 'available')
            ->count();
        $this->line("StockItems (status='available'): {$totalStockItems}");

        // จัดกลุ่มตาม product
        $byProduct = StockItem::where('warehouse_id', $warehouse->id)
            ->where('status', 'available')
            ->selectRaw('product_id, COUNT(*) as qty')
            ->groupBy('product_id')
            ->with('product:id,name,sku')
            ->get();

        $this->line("จำนวนสินค้าที่มีสต็อก: {$byProduct->count()} รายการ");

        $rows = $byProduct->map(fn($item) => [
            $item->product_id,
            $item->product->name ?? 'N/A',
            $item->product->sku ?? 'N/A',
            $item->qty,
        ])->toArray();

        if (!empty($rows)) {
            $this->table(['Product ID', 'ชื่อสินค้า', 'SKU', 'จำนวน'], $rows);
        }

        // WarehouseProduct
        $wpTotal = WarehouseProduct::where('warehouse_id', $warehouse->id)
            ->where('available_quantity', '>', 0)
            ->sum('available_quantity');

        $wpCount = WarehouseProduct::where('warehouse_id', $warehouse->id)
            ->where('available_quantity', '>', 0)
            ->count();

        $this->line("WarehouseProduct available_quantity total: {$wpTotal}");
        $this->line("WarehouseProduct rows count: {$wpCount}");
    }
}
