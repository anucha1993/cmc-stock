<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockItem;
use App\Models\Warehouse;
use App\Models\DeliveryNote;
use App\Models\Transfer;
use App\Models\Claim;
use App\Models\StockAdjustmentRequest;


class AdminController extends Controller
{
    public function dashboard()
    {
        // ===== สถิติสินค้า & สต็อก =====
        $totalProducts = Product::count();
        $totalStockItems = StockItem::where('status', 'available')->count();
        $totalWarehouses = Warehouse::where('is_active', true)->count();

        // ===== สินค้าสต็อกต่ำ =====
        $lowStockProducts = Product::where('is_active', true)
            ->where('min_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->with('category')
            ->orderBy('stock_quantity', 'asc')
            ->limit(10)
            ->get();

        // ===== สินค้าหมดอายุแล้ว =====
        $expiredItems = StockItem::where('status', 'available')
            ->whereNotNull('expire_date')
            ->where('expire_date', '<', now())
            ->count();

        // ===== งานรออนุมัติ =====
        $pendingDeliveryNotes = DeliveryNote::where('status', 'scanned')->count();
        $pendingTransfers = Transfer::where('status', 'pending')->count();
        $pendingAdjustments = StockAdjustmentRequest::where('status', 'pending')->count();
        $pendingClaims = Claim::where('status', 'pending')->count();
        $totalPendingApprovals = $pendingDeliveryNotes + $pendingTransfers + $pendingAdjustments + $pendingClaims;

        // ===== รายการล่าสุด =====
        $recentDeliveryNotes = DeliveryNote::with(['creator'])
            ->latest()->limit(5)->get();
        $recentTransfers = Transfer::with(['fromWarehouse', 'toWarehouse', 'product'])
            ->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalProducts', 'totalStockItems', 'totalWarehouses',
            'lowStockProducts', 'expiredItems',
            'pendingDeliveryNotes', 'pendingTransfers', 'pendingAdjustments', 'pendingClaims', 'totalPendingApprovals',
            'recentDeliveryNotes', 'recentTransfers'
        ));
    }
}
