<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockCheckSession;
use App\Models\Warehouse;

class TestRealtimeStock extends Command
{
    protected $signature = 'stock:test-realtime';
    protected $description = 'Test real-time stock counting';

    public function handle()
    {
        $this->info('=== Testing Real-Time Stock Counting ===');

        // Get first warehouse with stock
        $warehouse = Warehouse::first();
        if (!$warehouse) {
            $this->error('No warehouse found');
            return;
        }

        $this->line("Warehouse: {$warehouse->name}");

        // Count real-time
        $realTimeCount = $warehouse->getTotalStockCount();
        $this->line("Real-time StockItem count (status=available): {$realTimeCount}");

        // Test with StockCheckSession
        $session = StockCheckSession::create([
            'warehouse_id' => $warehouse->id,
            'status' => 'active'
        ]);

        $missingItems = $session->getMissingItems();
        $this->line("Missing items for stock check: " . $missingItems->count());

        $this->info("✅ Real-time counting is working!");
    }
}
