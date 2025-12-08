<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockItem;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;

class CheckAllStock extends Command
{
    protected $signature = 'stock:check-all';
    protected $description = 'Check all stock data';

    public function handle()
    {
        $items = StockItem::count();
        $this->line("Total StockItems in DB: $items");

        $wh = Warehouse::count();
        $this->line("Total Warehouses: $wh");

        $wp = WarehouseProduct::count();
        $this->line("Total WarehouseProducts: $wp");

        if ($wh > 0) {
            $warehouse = Warehouse::first();
            $this->line("\nFirst warehouse: {$warehouse->id} - {$warehouse->name}");

            $itemsInWh = StockItem::where('warehouse_id', $warehouse->id)->count();
            $this->line("  StockItems in this warehouse: $itemsInWh");

            if ($itemsInWh > 0) {
                $statuses = StockItem::where('warehouse_id', $warehouse->id)
                    ->groupBy('status')
                    ->selectRaw('status, COUNT(*) as cnt')
                    ->get();
                foreach ($statuses as $s) {
                    $this->line("    - status '{$s->status}': {$s->cnt} items");
                }
            }

            $wpInWh = WarehouseProduct::where('warehouse_id', $warehouse->id)->count();
            $this->line("  WarehouseProducts in this warehouse: $wpInWh");
        }
    }
}
