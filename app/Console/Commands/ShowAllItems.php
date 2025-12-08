<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockItem;
use App\Models\Warehouse;

class ShowAllItems extends Command
{
    protected $signature = 'stock:show-items';
    protected $description = 'Show all StockItems';

    public function handle()
    {
        $items = StockItem::with(['product', 'warehouse'])
            ->get();

        $this->table(['ID', 'Barcode', 'Product', 'Warehouse', 'Status', 'Qty'], 
            $items->map(function($item) {
                return [
                    $item->id,
                    $item->barcode ?? 'N/A',
                    $item->product?->name ?? 'N/A',
                    $item->warehouse?->name ?? 'N/A',
                    $item->status,
                    1
                ];
            })->toArray()
        );

        // Group by warehouse and status
        $this->line("\n=== Summary ===");
        $grouped = $items->groupBy('warehouse_id');
        foreach ($grouped as $whId => $whItems) {
            $wh = Warehouse::find($whId);
            $this->line("Warehouse: {$wh->name}");
            $byStatus = $whItems->groupBy('status');
            foreach ($byStatus as $status => $statusItems) {
                $this->line("  - {$status}: {$statusItems->count()}");
            }
        }
    }
}
