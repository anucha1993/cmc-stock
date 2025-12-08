<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

// Get first warehouse
$warehouse = \App\Models\Warehouse::first();
if (!$warehouse) {
    echo "No warehouse found\n";
    exit;
}

echo "=== Warehouse: " . $warehouse->name . " ===\n";

// Count real StockItems
$totalStockItems = \App\Models\StockItem::where('warehouse_id', $warehouse->id)
    ->where('status', 'available')
    ->count();

echo "Total StockItems (status='available'): " . $totalStockItems . "\n";

// Count grouped by barcode
$byBarcode = \App\Models\StockItem::where('warehouse_id', $warehouse->id)
    ->where('status', 'available')
    ->groupBy('barcode')
    ->selectRaw('barcode, COUNT(*) as qty')
    ->get();

echo "Products by barcode: " . $byBarcode->count() . "\n";
foreach ($byBarcode as $item) {
    echo "  - " . ($item->barcode ?? 'N/A') . ": " . $item->qty . " items\n";
}

// Check WarehouseProduct
$wpTotal = \App\Models\WarehouseProduct::where('warehouse_id', $warehouse->id)
    ->where('available_quantity', '>', 0)
    ->sum('available_quantity');

echo "\nWarehouseProduct available_quantity total: " . $wpTotal . "\n";

$wpCount = \App\Models\WarehouseProduct::where('warehouse_id', $warehouse->id)
    ->where('available_quantity', '>', 0)
    ->count();

echo "WarehouseProduct rows count: " . $wpCount . "\n";
