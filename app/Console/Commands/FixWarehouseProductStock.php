<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WarehouseProduct;
use App\Models\StockItem;
use App\Models\Warehouse;

class FixWarehouseProductStock extends Command
{
    protected $signature = 'stock:fix-warehouse-products';
    protected $description = 'Fix WarehouseProduct quantities from StockItems';

    public function handle()
    {
        $this->info('Fixing WarehouseProduct quantities from StockItems...');

        // Get all warehouses
        $warehouses = Warehouse::all();

        foreach ($warehouses as $warehouse) {
            $this->line("Processing warehouse: {$warehouse->name}");

            // Group StockItems by product and warehouse
            $itemsByProduct = StockItem::where('warehouse_id', $warehouse->id)
                ->where('status', 'available')
                ->groupBy('product_id')
                ->selectRaw('product_id, COUNT(*) as cnt')
                ->get();

            foreach ($itemsByProduct as $group) {
                $count = $group->cnt;
                
                // Update or create WarehouseProduct
                $wp = WarehouseProduct::updateOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $group->product_id
                    ],
                    [
                        'quantity' => $count,
                        'reserved_quantity' => 0,
                        'available_quantity' => $count
                    ]
                );

                $this->line("  ✓ Product {$group->product_id}: {$count} items");
            }
        }

        $this->info('✅ Done!');
    }
}
