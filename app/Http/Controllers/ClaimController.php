<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimItem;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\Warehouse;
use App\Models\DeliveryNote;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClaimController extends Controller
{
    /**
     * รายการเคลมทั้งหมด
     */
    public function index(Request $request)
    {
        $query = Claim::with(['items.product', 'creator', 'deliveryNote']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('claim_type')) {
            $query->where('claim_type', $request->claim_type);
        }

        if ($request->filled('claim_source')) {
            $query->where('claim_source', $request->claim_source);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('claim_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('claim_date', '<=', $request->date_to);
        }

        $claims = $query->latest()->paginate(15);

        $stats = [
            'total' => Claim::count(),
            'pending' => Claim::where('status', 'pending')->count(),
            'reviewing' => Claim::where('status', 'reviewing')->count(),
            'approved' => Claim::where('status', 'approved')->count(),
            'processing' => Claim::where('status', 'processing')->count(),
            'completed' => Claim::where('status', 'completed')->count(),
        ];

        return view('admin.claims.index', compact('claims', 'stats'));
    }

    /**
     * ฟอร์มสร้างใบเคลม
     */
    public function create(Request $request)
    {
        $warehouses = Warehouse::active()->get();
        $deliveryNotes = DeliveryNote::where('status', 'completed')
            ->latest()
            ->limit(100)
            ->get();

        $defaultSource = $request->get('source', 'delivery_note');

        return view('admin.claims.create', compact('warehouses', 'deliveryNotes', 'defaultSource'));
    }

    /**
     * บันทึกใบเคลม
     */
    public function store(Request $request)
    {
        $rules = [
            'claim_source' => 'required|in:delivery_note,stock_damage',
            'claim_type' => 'required|in:defective,damaged,wrong_item,missing_item,warranty,other',
            'priority' => 'required|in:low,normal,high,urgent',
            'description' => 'required|string',
            'claim_date' => 'required|date',
            'damaged_warehouse_id' => 'nullable|exists:warehouses,id',
            'scanned_items' => 'required|json',
        ];

        if ($request->claim_source === 'delivery_note') {
            $rules['delivery_note_id'] = 'required|exists:delivery_notes,id';
        } else {
            $rules['delivery_note_id'] = 'nullable|exists:delivery_notes,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $scannedItems = json_decode($request->scanned_items, true);
        if (empty($scannedItems)) {
            return redirect()->back()->with('error', 'กรุณาสแกนสินค้าอย่างน้อย 1 รายการ')->withInput();
        }

        DB::beginTransaction();
        try {
            $customerName = $request->customer_name;
            $customerPhone = $request->customer_phone;
            if ($request->claim_source === 'delivery_note' && $request->delivery_note_id) {
                $dn = DeliveryNote::find($request->delivery_note_id);
                if ($dn && empty($customerName)) {
                    $customerName = $dn->customer_name;
                    $customerPhone = $dn->customer_phone;
                }
            }

            $claim = Claim::create([
                'claim_source' => $request->claim_source,
                'customer_name' => $customerName ?: ($request->claim_source === 'stock_damage' ? 'ชำรุดจากสต็อก' : '-'),
                'customer_phone' => $customerPhone ?? $request->customer_phone,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'reference_number' => $request->reference_number,
                'delivery_note_id' => $request->delivery_note_id,
                'claim_type' => $request->claim_type,
                'priority' => $request->priority,
                'description' => $request->description,
                'claim_date' => $request->claim_date,
                'damaged_warehouse_id' => $request->damaged_warehouse_id,
                'created_by' => Auth::id(),
            ]);

            foreach ($scannedItems as $item) {
                $claim->items()->create([
                    'product_id' => $item['product_id'],
                    'stock_item_id' => $item['stock_item_id'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                    'reason' => $item['reason'] ?? 'broken',
                    'description' => $item['description'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.claims.show', $claim)
                ->with('success', 'สร้างใบเคลม ' . $claim->claim_number . ' เรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * แสดงรายละเอียดใบเคลม
     */
    public function show(Claim $claim)
    {
        $claim->load([
            'items.product',
            'items.stockItem',
            'items.replacementStockItem',
            'items.inspector',
            'deliveryNote.items.product',
            'damagedWarehouse',
            'creator',
            'reviewer',
            'approver',
            'processor',
        ]);

        return view('admin.claims.show', compact('claim'));
    }

    /**
     * ฟอร์มแก้ไขใบเคลม
     */
    public function edit(Claim $claim)
    {
        if (!in_array($claim->status, ['pending', 'reviewing'])) {
            return redirect()->route('admin.claims.show', $claim)
                ->with('error', 'ไม่สามารถแก้ไขใบเคลมที่ดำเนินการแล้วได้');
        }

        $claim->load('items.product', 'items.stockItem');
        $warehouses = Warehouse::active()->get();
        $deliveryNotes = DeliveryNote::where('status', 'completed')->latest()->limit(100)->get();

        return view('admin.claims.edit', compact('claim', 'warehouses', 'deliveryNotes'));
    }

    /**
     * อัปเดตใบเคลม
     */
    public function update(Request $request, Claim $claim)
    {
        if (!in_array($claim->status, ['pending', 'reviewing'])) {
            return redirect()->route('admin.claims.show', $claim)
                ->with('error', 'ไม่สามารถแก้ไขใบเคลมที่ดำเนินการแล้วได้');
        }

        $validator = Validator::make($request->all(), [
            'claim_source' => 'required|in:delivery_note,stock_damage',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
            'delivery_note_id' => 'nullable|exists:delivery_notes,id',
            'claim_type' => 'required|in:defective,damaged,wrong_item,missing_item,warranty,other',
            'priority' => 'required|in:low,normal,high,urgent',
            'description' => 'required|string',
            'claim_date' => 'required|date',
            'damaged_warehouse_id' => 'nullable|exists:warehouses,id',
            'scanned_items' => 'required|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $scannedItems = json_decode($request->scanned_items, true);
        if (empty($scannedItems)) {
            return redirect()->back()->with('error', 'กรุณาสแกนสินค้าอย่างน้อย 1 รายการ')->withInput();
        }

        DB::beginTransaction();
        try {
            $claim->update([
                'claim_source' => $request->claim_source,
                'customer_name' => $request->customer_name ?: ($request->claim_source === 'stock_damage' ? 'ชำรุดจากสต็อก' : '-'),
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'reference_number' => $request->reference_number,
                'delivery_note_id' => $request->delivery_note_id,
                'claim_type' => $request->claim_type,
                'priority' => $request->priority,
                'description' => $request->description,
                'claim_date' => $request->claim_date,
                'damaged_warehouse_id' => $request->damaged_warehouse_id,
            ]);

            $claim->items()->delete();

            foreach ($scannedItems as $item) {
                $claim->items()->create([
                    'product_id' => $item['product_id'],
                    'stock_item_id' => $item['stock_item_id'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                    'reason' => $item['reason'] ?? 'broken',
                    'description' => $item['description'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.claims.show', $claim)
                ->with('success', 'อัปเดตใบเคลมเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * ลบใบเคลม
     */
    public function destroy(Claim $claim)
    {
        if (!in_array($claim->status, ['pending', 'cancelled'])) {
            return redirect()->route('admin.claims.index')
                ->with('error', 'สามารถลบได้เฉพาะใบเคลมที่ยังรอดำเนินการหรือยกเลิกแล้ว');
        }

        $claim->items()->delete();
        $claim->delete();

        return redirect()->route('admin.claims.index')
            ->with('success', 'ลบใบเคลมเรียบร้อยแล้ว');
    }

    // ===== Workflow Actions =====

    public function review(Claim $claim)
    {
        $claim->startReview();
        return redirect()->route('admin.claims.show', $claim)
            ->with('success', 'เริ่มตรวจสอบใบเคลมแล้ว');
    }

    public function approve(Request $request, Claim $claim)
    {
        $validator = Validator::make($request->all(), [
            'resolution_type' => 'required|in:replace,repair,refund,credit,none',
            'resolution_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $claim->approve($request->resolution_type, $request->resolution_notes);
        return redirect()->route('admin.claims.show', $claim)
            ->with('success', 'อนุมัติใบเคลมเรียบร้อยแล้ว');
    }

    public function reject(Request $request, Claim $claim)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $claim->reject($request->rejection_reason);
        return redirect()->route('admin.claims.show', $claim)
            ->with('success', 'ปฏิเสธใบเคลมเรียบร้อยแล้ว');
    }

    public function process(Claim $claim)
    {
        $claim->startProcessing();
        return redirect()->route('admin.claims.show', $claim)
            ->with('success', 'เริ่มดำเนินการใบเคลมแล้ว');
    }

    public function completeClaim(Request $request, Claim $claim)
    {
        $claim->complete($request->resolution_notes);
        return redirect()->route('admin.claims.show', $claim)
            ->with('success', 'ใบเคลมเสร็จสิ้นแล้ว');
    }

    public function cancel(Request $request, Claim $claim)
    {
        $claim->cancel($request->cancel_reason);
        return redirect()->route('admin.claims.show', $claim)
            ->with('success', 'ยกเลิกใบเคลมเรียบร้อยแล้ว');
    }

    // ===== Item Inspection & Processing =====

    public function inspectItem(Request $request, Claim $claim, ClaimItem $item)
    {
        $validator = Validator::make($request->all(), [
            'damaged_status' => 'required|in:confirmed_damaged,repairable,unrepairable',
            'inspection_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        switch ($request->damaged_status) {
            case 'confirmed_damaged':
                $item->markAsDamaged($request->inspection_notes);
                break;
            case 'repairable':
                $item->markAsRepairable($request->inspection_notes);
                break;
            case 'unrepairable':
                $item->markAsUnrepairable($request->inspection_notes);
                break;
        }

        return redirect()->route('admin.claims.show', $claim)
            ->with('success', 'บันทึกผลการตรวจสอบเรียบร้อยแล้ว');
    }

    public function processItem(Request $request, Claim $claim, ClaimItem $item)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:scrap,return_supplier,return_stock,replace',
            'notes' => 'nullable|string',
            'replacement_stock_item_id' => 'nullable|exists:stock_items,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            switch ($request->action) {
                case 'scrap':
                    $item->scrap($request->notes);
                    break;

                case 'return_supplier':
                    $item->returnToSupplier($request->notes);
                    break;

                case 'return_stock':
                    $item->returnToStock($request->notes);
                    if ($item->stockItem && $item->stockItem->warehouse_id) {
                        InventoryTransaction::create([
                            'transaction_code' => InventoryTransaction::generateTransactionCode(),
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'before_quantity' => 0,
                            'after_quantity' => $item->quantity,
                            'product_id' => $item->product_id,
                            'warehouse_id' => $item->stockItem->warehouse_id,
                            'notes' => 'คืนเข้าสต็อกจากเคลม: ' . $claim->claim_number,
                            'reference_type' => 'claim',
                            'reference_id' => $claim->id,
                            'user_id' => Auth::id(),
                            'transaction_date' => now(),
                        ]);
                    }
                    break;

                case 'replace':
                    if ($request->replacement_stock_item_id) {
                        $item->replaceWith($request->replacement_stock_item_id, $request->notes);
                        $replacementItem = StockItem::find($request->replacement_stock_item_id);
                        if ($replacementItem) {
                            $replacementItem->changeStatus('sold', 'เปลี่ยนทดแทนเคลม: ' . $claim->claim_number);
                        }
                        InventoryTransaction::create([
                            'transaction_code' => InventoryTransaction::generateTransactionCode(),
                            'type' => 'out',
                            'quantity' => $item->quantity,
                            'before_quantity' => 0,
                            'after_quantity' => 0,
                            'product_id' => $item->product_id,
                            'warehouse_id' => $replacementItem->warehouse_id ?? null,
                            'notes' => 'เปลี่ยนทดแทนเคลม: ' . $claim->claim_number,
                            'reference_type' => 'claim',
                            'reference_id' => $claim->id,
                            'user_id' => Auth::id(),
                            'transaction_date' => now(),
                        ]);
                    }
                    break;
            }

            DB::commit();
            return redirect()->route('admin.claims.show', $claim)
                ->with('success', 'ดำเนินการกับสินค้าเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    // ===== API Endpoints for AJAX =====

    /**
     * API: สแกน Barcode เพื่อค้นหาสินค้า
     */
    public function scanBarcode(Request $request)
    {
        $barcode = trim($request->barcode);
        if (empty($barcode)) {
            return response()->json(['success' => false, 'message' => 'กรุณาระบุ Barcode']);
        }

        // ค้นหา StockItem จาก barcode
        $stockItem = StockItem::with('product', 'warehouse')
            ->where('barcode', $barcode)
            ->first();

        if (!$stockItem) {
            // ค้นหาจาก Product barcode/sku
            $product = Product::where('barcode', $barcode)
                ->orWhere('sku', $barcode)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบสินค้าจาก Barcode: ' . $barcode,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku ?? '',
                    'stock_item_id' => null,
                    'barcode' => $barcode,
                    'serial_number' => null,
                    'warehouse_name' => null,
                    'status' => null,
                ],
                'message' => 'พบสินค้า: ' . $product->name,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product_id' => $stockItem->product_id,
                'product_name' => $stockItem->product->name,
                'product_sku' => $stockItem->product->sku ?? '',
                'stock_item_id' => $stockItem->id,
                'barcode' => $stockItem->barcode,
                'serial_number' => $stockItem->serial_number,
                'warehouse_name' => $stockItem->warehouse->name ?? '-',
                'status' => $stockItem->status,
            ],
            'message' => 'พบสินค้า: ' . $stockItem->product->name . ' (SN: ' . $stockItem->serial_number . ')',
        ]);
    }

    /**
     * API: ดึงข้อมูลใบตัดสต็อก
     */
    public function getDeliveryNoteData(Request $request)
    {
        $deliveryNoteId = $request->delivery_note_id;
        $deliveryNote = DeliveryNote::with(['items.product'])
            ->find($deliveryNoteId);

        if (!$deliveryNote) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบใบตัดสต็อก',
            ]);
        }

        $items = [];
        foreach ($deliveryNote->items as $dnItem) {
            $scannedItems = $dnItem->scanned_items ?? [];
            foreach ($scannedItems as $scanned) {
                $stockItem = StockItem::where('barcode', $scanned['barcode'])->first();
                $items[] = [
                    'product_id' => $dnItem->product_id,
                    'product_name' => $dnItem->product->name,
                    'product_sku' => $dnItem->product->sku ?? '',
                    'stock_item_id' => $stockItem ? $stockItem->id : null,
                    'barcode' => $scanned['barcode'],
                    'serial_number' => $stockItem ? $stockItem->serial_number : null,
                ];
            }

            if (empty($scannedItems)) {
                for ($i = 0; $i < $dnItem->quantity; $i++) {
                    $items[] = [
                        'product_id' => $dnItem->product_id,
                        'product_name' => $dnItem->product->name,
                        'product_sku' => $dnItem->product->sku ?? '',
                        'stock_item_id' => null,
                        'barcode' => null,
                        'serial_number' => null,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'delivery_number' => $deliveryNote->delivery_number,
                'customer_name' => $deliveryNote->customer_name,
                'customer_phone' => $deliveryNote->customer_phone,
                'delivery_date' => $deliveryNote->delivery_date?->format('d/m/Y'),
                'items' => $items,
                'total_items' => count($items),
            ],
        ]);
    }

    // ===== Reports =====

    /**
     * รายงานสินค้าชำรุดจากเคลม
     */
    public function damagedReport(Request $request)
    {
        $query = ClaimItem::with(['claim', 'product', 'stockItem', 'inspector'])
            ->where('damaged_status', '!=', 'pending_inspection');

        if ($request->filled('damaged_status')) {
            $query->where('damaged_status', $request->damaged_status);
        }

        if ($request->filled('action_taken')) {
            $query->where('action_taken', $request->action_taken);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->whereHas('claim', fn($q) => $q->whereDate('claim_date', '>=', $request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereHas('claim', fn($q) => $q->whereDate('claim_date', '<=', $request->date_to));
        }

        $damagedItems = $query->latest()->paginate(20);

        $stats = [
            'total_damaged' => ClaimItem::where('damaged_status', 'confirmed_damaged')->count(),
            'repairable' => ClaimItem::where('damaged_status', 'repairable')->count(),
            'unrepairable' => ClaimItem::where('damaged_status', 'unrepairable')->count(),
            'scrapped' => ClaimItem::where('damaged_status', 'scrapped')->count(),
            'returned_supplier' => ClaimItem::where('damaged_status', 'returned_to_supplier')->count(),
            'returned_stock' => ClaimItem::where('damaged_status', 'returned_to_stock')->count(),
        ];

        $products = Product::active()->orderBy('name')->get();

        return view('admin.claims.damaged-report', compact('damagedItems', 'stats', 'products'));
    }
}
