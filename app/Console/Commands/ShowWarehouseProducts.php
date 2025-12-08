<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WarehouseProduct;

class ShowWarehouseProducts extends Command
{
    protected $signature = 'stock:show-wh-products';
    protected $description = 'Show all WarehouseProducts';

    public function handle()
    {
        $items = WarehouseProduct::with(['product', 'warehouse'])
            ->get();

        $this->table(
            ['ID', 'Warehouse', 'Product', 'Qty', 'Reserved', 'Available'], 
            $items->map(function($item) {
                return [
                    $item->id,
                    $item->warehouse?->name ?? 'N/A',
                    $item->product?->name ?? 'N/A',
                    $item->quantity,
                    $item->reserved_quantity,
                    $item->available_quantity
                ];
            })->toArray()
        );

        $this->line("\n=== Total ===");
        $total = WarehouseProduct::sum('available_quantity');
        $this->line("Total available_quantity: $total");
    }
}
