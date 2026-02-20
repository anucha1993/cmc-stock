<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockItem;
use App\Models\BarcodePrintLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarcodeLabelController extends Controller
{
    /**
     * แสดงหน้าเลือกสินค้าสำหรับพิมพ์ label
     */
    public function index()
    {
        $products = Product::with([
            'category',
            'stockItems' => function($query) {
                $query->where('status', '!=', 'sold');
            }
        ])
        ->whereHas('stockItems', function($query) {
            $query->where('status', '!=', 'sold');
        })
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

        // สถิติการพิมพ์วันนี้
        $todayStats = [
            'total_prints' => BarcodePrintLog::today()->count(),
            'total_labels' => BarcodePrintLog::today()->sum('copies'),
            'reprints' => BarcodePrintLog::today()->reprints()->count(),
            'unverified' => BarcodePrintLog::today()->unverified()->where('print_type', 'stock_item')->count(),
        ];

        return view('admin.barcode-labels.index', compact('products', 'todayStats'));
    }

    /**
     * แสดงรายการ Stock Items ของสินค้าที่เลือก
     */
    public function show(Product $product)
    {
        $stockItems = $product->stockItems()
            ->with(['warehouse', 'printLogs' => function($q) {
                $q->latest()->limit(1);
            }])
            ->where('status', '!=', 'sold')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.barcode-labels.show', compact('product', 'stockItems'));
    }

    /**
     * ตรวจสอบรายการที่เคยพิมพ์แล้ว ก่อนพิมพ์จริง (AJAX)
     */
    public function checkReprint(Request $request)
    {
        $request->validate([
            'stock_item_ids' => 'required|array|min:1',
            'stock_item_ids.*' => 'exists:stock_items,id',
        ]);

        $alreadyPrinted = StockItem::whereIn('id', $request->stock_item_ids)
            ->whereNotNull('label_printed_at')
            ->with(['printLogs' => function($q) {
                $q->latest()->limit(1)->with('printer');
            }])
            ->get()
            ->map(function($item) {
                $lastLog = $item->printLogs->first();
                return [
                    'id' => $item->id,
                    'barcode' => $item->barcode,
                    'serial_number' => $item->serial_number,
                    'printed_at' => $item->label_printed_at->format('d/m/Y H:i'),
                    'print_count' => $item->label_print_count,
                    'printed_by' => $lastLog?->printer?->name ?? 'ไม่ทราบ',
                ];
            });

        return response()->json([
            'has_reprints' => $alreadyPrinted->count() > 0,
            'items' => $alreadyPrinted,
            'new_count' => count($request->stock_item_ids) - $alreadyPrinted->count(),
        ]);
    }

    /**
     * พิมพ์ label ของ Stock Items ที่เลือก + บันทึก log
     */
    public function print(Request $request)
    {
        $request->validate([
            'stock_item_ids' => 'required|array|min:1',
            'stock_item_ids.*' => 'exists:stock_items,id',
            'label_size' => 'required|in:small,medium,large',
            'copies_per_item' => 'required|integer|min:1|max:10',
            'reprint_reason' => 'nullable|string|max:500',
        ]);

        $stockItems = StockItem::with(['product', 'warehouse'])
            ->whereIn('id', $request->stock_item_ids)
            ->orderBy('id')
            ->get();

        $labelSize = $request->label_size;
        $copiesPerItem = $request->copies_per_item;

        // บันทึก print log + อัปเดต stock items
        DB::transaction(function () use ($stockItems, $labelSize, $copiesPerItem, $request) {
            foreach ($stockItems as $item) {
                $isReprint = !is_null($item->label_printed_at);

                BarcodePrintLog::create([
                    'stock_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'printed_by' => Auth::id(),
                    'print_type' => 'stock_item',
                    'label_size' => $labelSize,
                    'copies' => $copiesPerItem,
                    'barcode' => $item->barcode,
                    'is_reprint' => $isReprint,
                    'reason' => $isReprint ? $request->reprint_reason : null,
                ]);

                $item->update([
                    'label_printed_at' => now(),
                    'label_print_count' => $item->label_print_count + 1,
                ]);
            }
        });

        return view('admin.barcode-labels.print', compact('stockItems', 'labelSize', 'copiesPerItem'));
    }

    /**
     * API: ได้รายการ Stock Items ของสินค้า
     */
    public function getStockItems(Product $product)
    {
        $stockItems = $product->stockItems()
            ->with(['warehouse'])
            ->where('status', '!=', 'sold')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'barcode' => $item->barcode,
                    'serial_number' => $item->serial_number,
                    'warehouse_name' => $item->warehouse->name,
                    'status' => $item->status_text,
                    'status_color' => $item->status_color,
                    'location_code' => $item->location_code,
                    'received_date' => $item->received_date ? $item->received_date->format('d/m/Y') : null,
                    'label_printed_at' => $item->label_printed_at?->format('d/m/Y H:i'),
                    'label_print_count' => $item->label_print_count,
                ];
            });

        return response()->json($stockItems);
    }

    /**
     * พิมพ์ label แบบ product-level + บันทึก log
     */
    public function printProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'total_labels' => 'required|integer|min:1|max:1000',
            'label_size' => 'required|in:small,medium,large',
            'copies_per_item' => 'required|integer|min:1|max:10',
        ]);

        $product = Product::findOrFail($request->product_id);
        $total = (int) $request->total_labels;
        $items = collect();

        $warehouseProduct = $product->warehouseProducts()->where('quantity', '>', 0)->first();
        $warehouse = $warehouseProduct ? \App\Models\Warehouse::find($warehouseProduct->warehouse_id) : null;

        // บันทึก log สำหรับ product-level print
        BarcodePrintLog::create([
            'stock_item_id' => null,
            'product_id' => $product->id,
            'printed_by' => Auth::id(),
            'print_type' => 'product',
            'label_size' => $request->label_size,
            'copies' => $total * $request->copies_per_item,
            'barcode' => $product->barcode,
            'is_reprint' => false,
        ]);

        for ($i = 0; $i < $total; $i++) {
            $obj = new \stdClass();
            $obj->id = 'product-' . $product->id . '-' . ($i+1);
            $obj->barcode = $product->barcode;
            $obj->serial_number = null;
            $obj->product = $product;
            $obj->warehouse = $warehouse;
            $obj->status_text = 'product-level';
            $obj->status_color = 'info';
            $obj->location_code = null;
            $obj->received_date = null;
            $items->push($obj);
        }

        $labelSize = $request->label_size;
        $copiesPerItem = $request->copies_per_item;

        return view('admin.barcode-labels.print', [
            'stockItems' => $items,
            'labelSize' => $labelSize,
            'copiesPerItem' => $copiesPerItem,
        ]);
    }

    /**
     * สแกนยืนยันว่าติดบาร์โค้ดถูกตัว (scan verify page)
     */
    public function verify(Request $request)
    {
        // Fetch recently printed (unverified) items for this user
        $recentLogs = BarcodePrintLog::where('printed_by', Auth::id())
            ->where('print_type', 'stock_item')
            ->where('verified', false)
            ->where('created_at', '>=', now()->subHours(24))
            ->with(['stockItem.product', 'stockItem.warehouse'])
            ->latest()
            ->get();

        return view('admin.barcode-labels.verify', compact('recentLogs'));
    }

    /**
     * API: สแกนบาร์โค้ดเพื่อยืนยัน
     */
    public function verifyScan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $barcode = trim($request->barcode);

        // หา stock item จาก barcode
        $stockItem = StockItem::where('barcode', $barcode)->first();

        if (!$stockItem) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบบาร์โค้ด "' . $barcode . '" ในระบบ',
            ], 404);
        }

        // หา print log ที่ยังไม่ verify
        $log = BarcodePrintLog::where('stock_item_id', $stockItem->id)
            ->where('verified', false)
            ->latest()
            ->first();

        if (!$log) {
            // เช็คว่ามี log ที่ verify แล้วไหม
            $verifiedLog = BarcodePrintLog::where('stock_item_id', $stockItem->id)
                ->where('verified', true)
                ->latest()
                ->first();

            if ($verifiedLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'บาร์โค้ดนี้ได้รับการยืนยันแล้วเมื่อ ' . $verifiedLog->verified_at->format('d/m/Y H:i'),
                    'already_verified' => true,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'ไม่พบรายการพิมพ์ที่รอยืนยันสำหรับบาร์โค้ดนี้',
            ]);
        }

        // ยืนยัน
        $log->update([
            'verified' => true,
            'verified_at' => now(),
            'verified_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ยืนยันติด Label สำเร็จ',
            'data' => [
                'barcode' => $stockItem->barcode,
                'product_name' => $stockItem->product->full_name ?? $stockItem->product->name,
                'serial_number' => $stockItem->serial_number,
                'warehouse' => $stockItem->warehouse->name ?? '-',
                'printed_at' => $log->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * ประวัติการพิมพ์ Label
     */
    public function history(Request $request)
    {
        $query = BarcodePrintLog::with(['stockItem.product', 'product', 'printer', 'verifier'])
            ->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('printed_by')) {
            $query->where('printed_by', $request->printed_by);
        }
        if ($request->filled('reprint_only')) {
            $query->where('is_reprint', true);
        }
        if ($request->filled('unverified_only')) {
            $query->where('verified', false)->where('print_type', 'stock_item');
        }

        $logs = $query->paginate(50);
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('admin.barcode-labels.history', compact('logs', 'users'));
    }

    /**
     * หน้าเอกสาร/เงื่อนไขการพิมพ์ Label Barcode
     */
    public function docs()
    {
        // สถิติรวม
        $stats = [
            'total_prints' => BarcodePrintLog::count(),
            'total_labels' => BarcodePrintLog::sum('copies'),
            'total_reprints' => BarcodePrintLog::where('is_reprint', true)->count(),
            'verified_rate' => BarcodePrintLog::where('print_type', 'stock_item')->count() > 0
                ? round(BarcodePrintLog::where('print_type', 'stock_item')->where('verified', true)->count() / BarcodePrintLog::where('print_type', 'stock_item')->count() * 100, 1)
                : 0,
        ];

        return view('admin.barcode-labels.docs', compact('stats'));
    }
}