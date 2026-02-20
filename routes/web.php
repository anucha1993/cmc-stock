<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\Admin\StockItemController;
use App\Http\Controllers\StockAdjustmentRequestController;
use App\Http\Controllers\BarcodeLabelController;
use App\Http\Controllers\StockCheckController;
use App\Http\Controllers\StockCheckSubmissionController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\PublicDeliveryNoteScanController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// =====================================================
// PUBLIC routes — สแกน Barcode ผ่าน share link (ไม่ต้อง login)
// =====================================================
Route::get('/dn/{slug}', [PublicDeliveryNoteScanController::class, 'scan'])->name('public.delivery-note.scan');
Route::post('/dn/{slug}/scan', [PublicDeliveryNoteScanController::class, 'storeScan'])->name('public.delivery-note.store-scan');
Route::post('/dn/{slug}/unscan', [PublicDeliveryNoteScanController::class, 'removeScan'])->name('public.delivery-note.remove-scan');

// Authentication Routes
Auth::routes();

Route::get('/home', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('home');

// Admin Routes (Protected by auth and role middleware)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard — ทุกคนที่ login แล้วเข้าได้
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // =====================================================
    // DRIVER routes (Level 6) — คนรถ: สแกนบาร์โค้ดตอนขนของ + ดูใบตัดสต็อก
    // =====================================================
    Route::middleware(['role:driver'])->group(function () {
        // ดูใบตัดสต็อก + สแกนบาร์โค้ดขนของออกจากคลัง
        Route::get('delivery-notes', [DeliveryNoteController::class, 'index'])->name('delivery-notes.index');
        // *** static routes ก่อน wildcard {deliveryNote} ***
        Route::get('delivery-notes/create', [DeliveryNoteController::class, 'create'])->name('delivery-notes.create')->middleware('role:supervisor');
        Route::get('delivery-notes/{deliveryNote}', [DeliveryNoteController::class, 'show'])->name('delivery-notes.show');
        Route::get('delivery-notes/{deliveryNote}/scan', [DeliveryNoteController::class, 'scan'])->name('delivery-notes.scan');
        Route::get('delivery-notes/{deliveryNote}/review', [DeliveryNoteController::class, 'review'])->name('delivery-notes.review');
        Route::post('delivery-notes/{deliveryNote}/share-link', [DeliveryNoteController::class, 'generateShareLink'])->name('delivery-notes.share-link');
        Route::get('delivery-notes/{deliveryNote}/print', [DeliveryNoteController::class, 'print'])->name('delivery-notes.print');
    });

    // =====================================================
    // VIEWER routes (Level 5+) — ดูข้อมูลอย่างเดียว
    // =====================================================
    Route::middleware(['role:viewer'])->group(function () {
        // ดูสินค้า / สต็อก / คลัง (read-only)
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show')->where('product', '[0-9]+');
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show')->where('category', '[0-9]+');
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show')->where('supplier', '[0-9]+');
        Route::get('packages', [PackageController::class, 'index'])->name('packages.index');
        Route::get('packages/{package}', [PackageController::class, 'show'])->name('packages.show')->where('package', '[0-9]+');
        Route::get('stock-items', [StockItemController::class, 'index'])->name('stock-items.index');
        Route::get('stock-items/{stockItem}', [StockItemController::class, 'show'])->name('stock-items.show')->where('stockItem', '[0-9]+');
        Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show')->where('warehouse', '[0-9]+');
        Route::get('warehouses/{warehouse}/stock', [WarehouseController::class, 'stock'])->name('warehouses.stock')->where('warehouse', '[0-9]+');
        Route::get('inventory-transactions', [StockItemController::class, 'transactions'])->name('inventory-transactions.index');
        Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
        Route::get('transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show')->where('transfer', '[0-9]+');
        Route::get('transfers-report', [TransferController::class, 'report'])->name('transfers.report');
        Route::get('stock-checks', [StockCheckController::class, 'index'])->name('stock-checks.index');
        Route::get('stock-checks/{stockCheck}', [StockCheckController::class, 'show'])->name('stock-checks.show')->where('stockCheck', '[0-9]+');
        Route::get('stock-checks/{stockCheck}/report', [StockCheckController::class, 'report'])->name('stock-checks.report')->where('stockCheck', '[0-9]+');
        Route::get('stock-adjustments', [StockAdjustmentRequestController::class, 'index'])->name('stock-adjustments.index');
        Route::get('stock-adjustments/{stockAdjustment}', [StockAdjustmentRequestController::class, 'show'])->name('stock-adjustments.show')->where('stockAdjustment', '[0-9]+');
        Route::get('production-orders', [ProductionOrderController::class, 'index'])->name('production-orders.index');
        Route::get('production-orders/{productionOrder}', [ProductionOrderController::class, 'show'])->name('production-orders.show')->where('productionOrder', '[0-9]+');
        Route::get('production-orders-report', [ProductionOrderController::class, 'report'])->name('production-orders.report');
        Route::get('production-dashboard', [ProductionOrderController::class, 'dashboard'])->name('production-orders.dashboard');
        Route::get('claims', [ClaimController::class, 'index'])->name('claims.index');
        Route::get('claims/{claim}', [ClaimController::class, 'show'])->name('claims.show')->where('claim', '[0-9]+');
        Route::get('claims-damaged-report', [ClaimController::class, 'damagedReport'])->name('claims.damaged-report');
        Route::get('packages-report', [PackageController::class, 'report'])->name('packages.report');
    });

    // =====================================================
    // STAFF routes (Level 4+) — พนักงานคลัง: สแกน, ตรวจนับ, คำขอปรับสต็อก
    // =====================================================
    Route::middleware(['role:staff'])->group(function () {
        // สแกนบาร์โค้ด
        Route::post('stock-items/find-by-barcode', [StockItemController::class, 'findByBarcode'])->name('stock-items.find-by-barcode');
        Route::get('products/search/barcode', [ProductController::class, 'searchByBarcode'])->name('products.search-barcode');

        // พิมพ์บาร์โค้ด
        Route::get('barcode-labels', [BarcodeLabelController::class, 'index'])->name('barcode-labels.index');
        Route::get('barcode-labels/product/{product}', [BarcodeLabelController::class, 'show'])->name('barcode-labels.show');
        Route::post('barcode-labels/print', [BarcodeLabelController::class, 'print'])->name('barcode-labels.print');
        Route::post('barcode-labels/print-product', [BarcodeLabelController::class, 'printProduct'])->name('barcode-labels.print-product');
        Route::post('barcode-labels/check-reprint', [BarcodeLabelController::class, 'checkReprint'])->name('barcode-labels.check-reprint');
        Route::get('barcode-labels/verify', [BarcodeLabelController::class, 'verify'])->name('barcode-labels.verify');
        Route::post('barcode-labels/verify-scan', [BarcodeLabelController::class, 'verifyScan'])->name('barcode-labels.verify-scan');
        Route::get('barcode-labels/history', [BarcodeLabelController::class, 'history'])->name('barcode-labels.history');
        Route::get('barcode-labels/docs', [BarcodeLabelController::class, 'docs'])->name('barcode-labels.docs');
        Route::get('api/barcode-labels/product/{product}/stock-items', [BarcodeLabelController::class, 'getStockItems'])->name('api.barcode-labels.stock-items');

        // ตรวจนับสต็อก — สแกน
        Route::get('stock-checks/{stockCheck}/scan', [StockCheckController::class, 'scan'])->name('stock-checks.scan');
        Route::post('stock-checks/{stockCheck}/process-scan', [StockCheckController::class, 'processScan'])->name('stock-checks.process-scan');
        Route::get('api/stock-checks/{stockCheck}/stats', [StockCheckController::class, 'getStats'])->name('api.stock-checks.stats');
        Route::get('api/stock-checks/{stockCheck}/recent-scans', [StockCheckController::class, 'getRecentScans'])->name('api.stock-checks.recent-scans');

        // คำขอปรับปรุงสต็อก — สร้าง
        Route::get('stock-adjustments/create', [StockAdjustmentRequestController::class, 'create'])->name('stock-adjustments.create');
        Route::post('stock-adjustments', [StockAdjustmentRequestController::class, 'store'])->name('stock-adjustments.store');
        Route::get('api/warehouse-stock/{warehouse}/{product}', [StockAdjustmentRequestController::class, 'getWarehouseStock'])->name('api.warehouse-stock');
    });

    // =====================================================
    // SUPERVISOR routes (Level 3+) — หัวหน้างาน: สร้าง/แก้ไข ข้อมูลหลัก
    // =====================================================
    Route::middleware(['role:supervisor'])->group(function () {
        // สินค้า — สร้าง/แก้ไข
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');

        // แพสินค้า — สร้าง/แก้ไข
        Route::get('packages/create', [PackageController::class, 'create'])->name('packages.create');
        Route::post('packages', [PackageController::class, 'store'])->name('packages.store');
        Route::get('packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
        Route::put('packages/{package}', [PackageController::class, 'update'])->name('packages.update');
        Route::get('packages/{package}/import', [PackageController::class, 'import'])->name('packages.import');
        Route::post('packages/{package}/import', [PackageController::class, 'processImport'])->name('packages.process-import');
        Route::post('packages/{package}/duplicate', [PackageController::class, 'duplicate'])->name('packages.duplicate');

        // Stock Items — สร้าง/แก้ไข
        Route::get('stock-items/create', [StockItemController::class, 'create'])->name('stock-items.create');
        Route::post('stock-items', [StockItemController::class, 'store'])->name('stock-items.store');
        Route::get('stock-items/{stockItem}/edit', [StockItemController::class, 'edit'])->name('stock-items.edit');
        Route::put('stock-items/{stockItem}', [StockItemController::class, 'update'])->name('stock-items.update');
        Route::get('stock-items/{stockItem}/generate-barcode', [StockItemController::class, 'generateBarcode'])->name('stock-items.generate-barcode');
        Route::post('stock-items/{stockItem}/change-status', [StockItemController::class, 'changeStatus'])->name('stock-items.change-status');
        Route::post('stock-items/{stockItem}/move-warehouse', [StockItemController::class, 'moveWarehouse'])->name('stock-items.move-warehouse');

        // ใบตัดสต็อก — แก้ไข (create ย้ายไป driver group แล้ว)
        Route::post('delivery-notes', [DeliveryNoteController::class, 'store'])->name('delivery-notes.store');
        Route::get('delivery-notes/{deliveryNote}/edit', [DeliveryNoteController::class, 'edit'])->name('delivery-notes.edit');
        Route::put('delivery-notes/{deliveryNote}', [DeliveryNoteController::class, 'update'])->name('delivery-notes.update');
        Route::post('delivery-notes/{deliveryNote}/confirm', [DeliveryNoteController::class, 'confirm'])->name('delivery-notes.confirm');
        Route::post('delivery-notes/{deliveryNote}/reset-scan', [DeliveryNoteController::class, 'resetScan'])->name('delivery-notes.reset-scan');

        // โอนย้ายสินค้า — สร้าง/แก้ไข
        Route::get('transfers/create', [TransferController::class, 'create'])->name('transfers.create');
        Route::post('transfers', [TransferController::class, 'store'])->name('transfers.store');
        Route::get('transfers/{transfer}/edit', [TransferController::class, 'edit'])->name('transfers.edit');
        Route::put('transfers/{transfer}', [TransferController::class, 'update'])->name('transfers.update');

        // ตรวจนับสต็อก — สร้าง/จัดการ/ส่งอนุมัติ
        Route::get('stock-checks/create', [StockCheckController::class, 'create'])->name('stock-checks.create');
        Route::post('stock-checks', [StockCheckController::class, 'store'])->name('stock-checks.store');
        Route::get('stock-checks/{stockCheck}/edit', [StockCheckController::class, 'edit'])->name('stock-checks.edit');
        Route::put('stock-checks/{stockCheck}', [StockCheckController::class, 'update'])->name('stock-checks.update');
        Route::post('stock-checks/{stockCheck}/complete', [StockCheckController::class, 'complete'])->name('stock-checks.complete');
        Route::post('stock-checks/{stockCheck}/generate-adjustment', [StockCheckController::class, 'generateAdjustment'])->name('stock-checks.generate-adjustment');
        Route::post('stock-checks/{stockCheck}/submit', [StockCheckController::class, 'submitForApproval'])->name('stock-checks.submit');

        // คำขอปรับปรุงสต็อก — แก้ไข
        Route::get('stock-adjustments/{stockAdjustment}/edit', [StockAdjustmentRequestController::class, 'edit'])->name('stock-adjustments.edit');
        Route::put('stock-adjustments/{stockAdjustment}', [StockAdjustmentRequestController::class, 'update'])->name('stock-adjustments.update');

        // สั่งผลิต — สร้าง/แก้ไข
        Route::get('production-orders/create', [ProductionOrderController::class, 'create'])->name('production-orders.create');
        Route::post('production-orders', [ProductionOrderController::class, 'store'])->name('production-orders.store');
        Route::get('production-orders/{productionOrder}/edit', [ProductionOrderController::class, 'edit'])->name('production-orders.edit');
        Route::put('production-orders/{productionOrder}', [ProductionOrderController::class, 'update'])->name('production-orders.update');
        Route::post('production-orders/{productionOrder}/update-produced-quantity', [ProductionOrderController::class, 'updateProducedQuantity'])->name('production-orders.update-produced-quantity');

        // เคลม — สร้าง/แก้ไข
        Route::get('claims/create', [ClaimController::class, 'create'])->name('claims.create');
        Route::post('claims', [ClaimController::class, 'store'])->name('claims.store');
        Route::get('claims/{claim}/edit', [ClaimController::class, 'edit'])->name('claims.edit');
        Route::put('claims/{claim}', [ClaimController::class, 'update'])->name('claims.update');
        Route::post('claims/scan-barcode', [ClaimController::class, 'scanBarcode'])->name('claims.scan-barcode');
        Route::get('claims-delivery-note-data', [ClaimController::class, 'getDeliveryNoteData'])->name('claims.delivery-note-data');

        // API routes
        Route::get('api/packages/{package}/products', [ProductionOrderController::class, 'getPackageProducts'])->name('api.packages.products');
        Route::get('api/products/search', [ProductionOrderController::class, 'searchProducts'])->name('api.products.search');
    });

    // =====================================================
    // ADMIN routes (Level 2+) — ผู้จัดการ: อนุมัติ, ลบ, จัดการผู้ใช้
    // =====================================================
    Route::middleware(['role:master-admin,admin'])->group(function () {
        // User Management
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // Profile Management for Admins
        Route::get('profiles', [ProfileController::class, 'index'])->name('profiles.index');
        Route::get('profiles/{profile}', [ProfileController::class, 'showProfile'])->name('profiles.show');
        Route::get('profiles/{profile}/edit', [ProfileController::class, 'editProfile'])->name('profiles.edit');
        Route::put('profiles/{profile}', [ProfileController::class, 'updateProfile'])->name('profiles.update');

        // Master data — CRUD (ลบ)
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('products/{product}/update-stock', [ProductController::class, 'updateStock'])->name('products.update-stock');

        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

        Route::delete('packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');
        Route::delete('stock-items/{stockItem}', [StockItemController::class, 'destroy'])->name('stock-items.destroy');

        // Warehouse — CRUD
        Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
        Route::post('warehouses/{warehouse}/update-stock', [WarehouseController::class, 'updateStock'])->name('warehouses.update-stock');
        Route::post('warehouses/{warehouse}/quick-add-stock', [WarehouseController::class, 'quickAddStock'])->name('warehouses.quick-add-stock');
        Route::get('warehouses/{warehouse}/bulk-stock', [WarehouseController::class, 'bulkStock'])->name('warehouses.bulk-stock');
        Route::post('warehouses/{warehouse}/bulk-update-stock', [WarehouseController::class, 'bulkUpdateStock'])->name('warehouses.bulk-update-stock');

        // อนุมัติ/ปฏิเสธ Actions
        Route::post('stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentRequestController::class, 'approve'])->name('stock-adjustments.approve');
        Route::post('stock-adjustments/{stockAdjustment}/reject', [StockAdjustmentRequestController::class, 'reject'])->name('stock-adjustments.reject');
        Route::post('stock-adjustments/{stockAdjustment}/process', [StockAdjustmentRequestController::class, 'process'])->name('stock-adjustments.process');
        Route::delete('stock-adjustments/{stockAdjustment}', [StockAdjustmentRequestController::class, 'destroy'])->name('stock-adjustments.destroy');

        Route::delete('stock-checks/{stockCheck}', [StockCheckController::class, 'destroy'])->name('stock-checks.destroy');

        // Stock Check Submissions (Admin approval)
        Route::resource('stock-check-submissions', StockCheckSubmissionController::class)->only(['index', 'show'])->parameters(['stock-check-submissions' => 'submission']);
        Route::get('stock-check-submissions/{submission}/review', [StockCheckSubmissionController::class, 'review'])->name('stock-check-submissions.review');
        Route::post('stock-check-submissions/{submission}/process-decision', [StockCheckSubmissionController::class, 'processDecision'])->name('stock-check-submissions.process-decision');
        Route::post('stock-check-submissions/{submission}/request-recheck', [StockCheckSubmissionController::class, 'requestRecheck'])->name('stock-check-submissions.request-recheck');

        // โอนย้าย — อนุมัติ/เสร็จ/ยกเลิก/ลบ
        Route::post('transfers/{transfer}/approve', [TransferController::class, 'approve'])->name('transfers.approve');
        Route::post('transfers/{transfer}/complete', [TransferController::class, 'complete'])->name('transfers.complete');
        Route::post('transfers/{transfer}/cancel', [TransferController::class, 'cancel'])->name('transfers.cancel');
        Route::delete('transfers/{transfer}', [TransferController::class, 'destroy'])->name('transfers.destroy');

        // ใบตัดสต็อก — อนุมัติ/ลบ
        Route::post('delivery-notes/{deliveryNote}/approve', [DeliveryNoteController::class, 'approve'])->name('delivery-notes.approve');
        Route::delete('delivery-notes/{deliveryNote}', [DeliveryNoteController::class, 'destroy'])->name('delivery-notes.destroy');

        // สั่งผลิต — อนุมัติ/ลบ
        Route::post('production-orders/{productionOrder}/update-status', [ProductionOrderController::class, 'updateStatus'])->name('production-orders.update-status');
        Route::delete('production-orders/{productionOrder}', [ProductionOrderController::class, 'destroy'])->name('production-orders.destroy');

        // เคลม — อนุมัติ/ปฏิเสธ/ดำเนินการ/ลบ
        Route::post('claims/{claim}/review', [ClaimController::class, 'review'])->name('claims.review');
        Route::post('claims/{claim}/approve', [ClaimController::class, 'approve'])->name('claims.approve');
        Route::post('claims/{claim}/reject', [ClaimController::class, 'reject'])->name('claims.reject');
        Route::post('claims/{claim}/process', [ClaimController::class, 'process'])->name('claims.process');
        Route::post('claims/{claim}/complete', [ClaimController::class, 'completeClaim'])->name('claims.complete');
        Route::post('claims/{claim}/cancel', [ClaimController::class, 'cancel'])->name('claims.cancel');
        Route::post('claims/{claim}/items/{item}/inspect', [ClaimController::class, 'inspectItem'])->name('claims.inspect-item');
        Route::post('claims/{claim}/items/{item}/process', [ClaimController::class, 'processItem'])->name('claims.process-item');
        Route::delete('claims/{claim}', [ClaimController::class, 'destroy'])->name('claims.destroy');

        // =====================================================
        // MASTER ADMIN routes (Level 1) — จัดการบทบาท
        // =====================================================
        Route::middleware(['role:master-admin'])->group(function () {
            Route::resource('roles', RoleController::class);
            Route::get('roles/{role}/manage-users', [RoleController::class, 'manageUsers'])->name('roles.manage-users');
            Route::post('roles/{role}/add-user', [RoleController::class, 'addUser'])->name('roles.add-user');
            Route::delete('roles/{role}/remove-user/{user}', [RoleController::class, 'removeUser'])->name('roles.remove-user');
        });
    });

    // Profile Management (Current user's profile - All authenticated users)
    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
});
