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

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Auth::routes();

Route::get('/home', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('home');

// Admin Routes (Protected by auth and role middleware)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Master Admin and Admin only routes
    Route::middleware(['role:master-admin,admin'])->group(function () {
        
        // User Management
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        
        // Profile Management for Admins (manage all profiles)
        Route::get('profiles', [ProfileController::class, 'index'])->name('profiles.index');
        Route::get('profiles/{profile}', [ProfileController::class, 'showProfile'])->name('profiles.show');
        Route::get('profiles/{profile}/edit', [ProfileController::class, 'editProfile'])->name('profiles.edit');
        Route::put('profiles/{profile}', [ProfileController::class, 'updateProfile'])->name('profiles.update');
        
        // Inventory Management
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/update-stock', [ProductController::class, 'updateStock'])->name('products.update-stock');
        Route::get('products/search/barcode', [ProductController::class, 'searchByBarcode'])->name('products.search-barcode');
        
        Route::resource('categories', CategoryController::class);
        Route::resource('suppliers', SupplierController::class);
        
        // Package Management
        Route::resource('packages', PackageController::class);
        Route::get('packages/{package}/import', [PackageController::class, 'import'])->name('packages.import');
        Route::post('packages/{package}/import', [PackageController::class, 'processImport'])->name('packages.process-import');
        Route::post('packages/{package}/duplicate', [PackageController::class, 'duplicate'])->name('packages.duplicate');
        Route::get('packages-report', [PackageController::class, 'report'])->name('packages.report');
        
        // Package Management
        Route::resource('packages', PackageController::class);
        Route::get('packages/{package}/import', [PackageController::class, 'import'])->name('packages.import');
        Route::post('packages/{package}/import', [PackageController::class, 'processImport'])->name('packages.process-import');
        Route::post('packages/{package}/duplicate', [PackageController::class, 'duplicate'])->name('packages.duplicate');
        Route::get('packages-report', [PackageController::class, 'report'])->name('packages.report');
        
        // Stock Item Management  
        Route::resource('stock-items', StockItemController::class);
        Route::get('stock-items/{stockItem}/generate-qr', [StockItemController::class, 'generateQR'])->name('stock-items.generate-qr');
        Route::post('stock-items/find-by-barcode', [StockItemController::class, 'findByBarcode'])->name('stock-items.find-by-barcode');
        Route::post('stock-items/{stockItem}/change-status', [StockItemController::class, 'changeStatus'])->name('stock-items.change-status');
        Route::post('stock-items/{stockItem}/move-warehouse', [StockItemController::class, 'moveWarehouse'])->name('stock-items.move-warehouse');
        
        // Inventory Transactions
        Route::get('inventory-transactions', [StockItemController::class, 'transactions'])->name('inventory-transactions.index');
        
        // Stock Adjustment Requests
        Route::resource('stock-adjustments', StockAdjustmentRequestController::class);
        Route::post('stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentRequestController::class, 'approve'])->name('stock-adjustments.approve');
        Route::post('stock-adjustments/{stockAdjustment}/reject', [StockAdjustmentRequestController::class, 'reject'])->name('stock-adjustments.reject');
        Route::post('stock-adjustments/{stockAdjustment}/process', [StockAdjustmentRequestController::class, 'process'])->name('stock-adjustments.process');
        
        // API Routes for Stock Adjustments
        Route::get('api/warehouse-stock/{warehouse}/{product}', [StockAdjustmentRequestController::class, 'getWarehouseStock'])->name('api.warehouse-stock');
        
        // Barcode Label System
        Route::get('barcode-labels', [BarcodeLabelController::class, 'index'])->name('barcode-labels.index');
        Route::get('barcode-labels/product/{product}', [BarcodeLabelController::class, 'show'])->name('barcode-labels.show');
    Route::post('barcode-labels/print', [BarcodeLabelController::class, 'print'])->name('barcode-labels.print');
    // Product-level printing when there are no StockItem rows (generate labels from product SKU/barcode)
    Route::post('barcode-labels/print-product', [BarcodeLabelController::class, 'printProduct'])->name('barcode-labels.print-product');
        Route::get('api/barcode-labels/product/{product}/stock-items', [BarcodeLabelController::class, 'getStockItems'])->name('api.barcode-labels.stock-items');
        
        // Warehouse Management
        Route::resource('warehouses', WarehouseController::class);
        Route::get('warehouses/{warehouse}/stock', [WarehouseController::class, 'stock'])->name('warehouses.stock');
        Route::post('warehouses/{warehouse}/update-stock', [WarehouseController::class, 'updateStock'])->name('warehouses.update-stock');
        Route::post('warehouses/{warehouse}/quick-add-stock', [WarehouseController::class, 'quickAddStock'])->name('warehouses.quick-add-stock');
        Route::get('warehouses/{warehouse}/bulk-stock', [WarehouseController::class, 'bulkStock'])->name('warehouses.bulk-stock');
        Route::post('warehouses/{warehouse}/bulk-update-stock', [WarehouseController::class, 'bulkUpdateStock'])->name('warehouses.bulk-update-stock');
        
        // Transfer Management
        Route::resource('transfers', TransferController::class);
        Route::post('transfers/{transfer}/approve', [TransferController::class, 'approve'])->name('transfers.approve');
        Route::post('transfers/{transfer}/complete', [TransferController::class, 'complete'])->name('transfers.complete');
        Route::post('transfers/{transfer}/cancel', [TransferController::class, 'cancel'])->name('transfers.cancel');
        Route::get('transfers-report', [TransferController::class, 'report'])->name('transfers.report');
        
        // Production Order Management
        Route::resource('production-orders', ProductionOrderController::class);
        Route::post('production-orders/{productionOrder}/update-status', [ProductionOrderController::class, 'updateStatus'])->name('production-orders.update-status');
        Route::post('production-orders/{productionOrder}/update-produced-quantity', [ProductionOrderController::class, 'updateProducedQuantity'])->name('production-orders.update-produced-quantity');
        Route::get('production-orders-report', [ProductionOrderController::class, 'report'])->name('production-orders.report');
        
        // API routes
        Route::get('api/packages/{package}/products', [ProductionOrderController::class, 'getPackageProducts'])->name('api.packages.products');
        Route::get('api/products/search', [ProductionOrderController::class, 'searchProducts'])->name('api.products.search');
        Route::get('production-dashboard', [ProductionOrderController::class, 'dashboard'])->name('production-orders.dashboard');
        
        // Role Management (Master Admin only)
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
