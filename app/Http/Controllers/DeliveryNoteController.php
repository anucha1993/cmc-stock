<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DeliveryNote::with(['creator', 'items.product']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                  ->orWhere('sales_order_number', 'like', "%{$search}%")
                  ->orWhere('quotation_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('delivery_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('delivery_date', '<=', $request->end_date);
        }

        $deliveryNotes = $query->latest()->paginate(20);

        return view('admin.delivery-notes.index', compact('deliveryNotes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::active()->with('category')->get();
        
        return view('admin.delivery-notes.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_order_number' => 'nullable|string|max:100',
            'quotation_number' => 'nullable|string|max:100',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'delivery_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // สร้างใบตัดสต็อก
            $deliveryNote = DeliveryNote::create([
                'sales_order_number' => $request->sales_order_number,
                'quotation_number' => $request->quotation_number,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_date' => $request->delivery_date,
                'notes' => $request->notes,
                'status' => 'pending',
                'created_by' => Auth::id()
            ]);

            // สร้างรายการสินค้า
            foreach ($request->items as $item) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null
                ]);
            }

            DB::commit();
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('success', 'สร้างใบตัดสต็อกเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['items.product', 'creator', 'confirmer', 'scanner', 'approver']);
        
        // ตรวจสอบ discrepancies
        $discrepancies = [];
        if ($deliveryNote->status === 'scanned' || $deliveryNote->status === 'completed') {
            $discrepancies = $deliveryNote->checkDiscrepancies();
        }

        return view('admin.delivery-notes.show', compact('deliveryNote', 'discrepancies'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeliveryNote $deliveryNote)
    {
        // อนุญาตให้แก้ไขเฉพาะสถานะ pending
        if ($deliveryNote->status !== 'pending') {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'ไม่สามารถแก้ไขใบตัดสต็อกที่มีสถานะนอกเหนือจาก "รอยืนยัน" ได้');
        }

        $products = Product::active()->with('category')->get();
        $deliveryNote->load(['items.product']);

        return view('admin.delivery-notes.edit', compact('deliveryNote', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->status !== 'pending') {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'ไม่สามารถแก้ไขใบตัดสต็อกที่มีสถานะนอกเหนือจาก "รอยืนยัน" ได้');
        }

        $validator = Validator::make($request->all(), [
            'sales_order_number' => 'nullable|string|max:100',
            'quotation_number' => 'nullable|string|max:100',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'delivery_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // อัปเดตใบตัดสต็อก
            $deliveryNote->update([
                'sales_order_number' => $request->sales_order_number,
                'quotation_number' => $request->quotation_number,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_date' => $request->delivery_date,
                'notes' => $request->notes
            ]);

            // ลบรายการเดิมทั้งหมด
            $deliveryNote->items()->delete();

            // สร้างรายการใหม่
            foreach ($request->items as $item) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null
                ]);
            }

            DB::commit();
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('success', 'แก้ไขใบตัดสต็อกเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliveryNote $deliveryNote)
    {
        // อนุญาตให้ลบเฉพาะสถานะ pending
        if ($deliveryNote->status !== 'pending') {
            return redirect()->route('admin.delivery-notes.index')
                ->with('error', 'ไม่สามารถลบใบตัดสต็อกที่มีสถานะนอกเหนือจาก "รอยืนยัน" ได้');
        }

        try {
            $deliveryNote->delete();
            return redirect()->route('admin.delivery-notes.index')
                ->with('success', 'ลบใบตัดสต็อกเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ยืนยันใบตัดสต็อก
     */
    public function confirm(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->status !== 'pending') {
            return redirect()->back()->with('error', 'สามารถยืนยันได้เฉพาะสถานะ "รอยืนยัน" เท่านั้น');
        }

        $deliveryNote->confirm(Auth::id());

        return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
            ->with('success', 'ยืนยันใบตัดสต็อกเรียบร้อยแล้ว คนขับรถสามารถเริ่มโหลดสินค้าได้');
    }

    /**
     * หน้าสแกน Barcode
     */
    public function scan(DeliveryNote $deliveryNote)
    {
        if (!in_array($deliveryNote->status, ['pending', 'confirmed', 'scanned'])) {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'ไม่สามารถสแกนใบตัดสต็อกที่มีสถานะนี้ได้');
        }

        $deliveryNote->load(['items.product']);

        return view('admin.delivery-notes.scan', compact('deliveryNote'));
    }

    /**
     * บันทึก Barcode ที่สแกน
     */
    public function storeScan(Request $request, DeliveryNote $deliveryNote)
    {
        $validator = Validator::make($request->all(), [
            'barcode' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'กรุณาระบุ barcode']);
        }

        $barcode = $request->barcode;

        // ค้นหา Stock Item จาก barcode
        $stockItem = StockItem::where('barcode', $barcode)
            ->where('status', 'available')
            ->first();

        if (!$stockItem) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบสินค้าที่มี barcode นี้ หรือสินค้าไม่อยู่ในสถานะพร้อมใช้งาน'
            ]);
        }

        // ตรวจสอบว่าสินค้ามีอยู่ในรายการหรือไม่
        $deliveryNoteItem = $deliveryNote->items()
            ->where('product_id', $stockItem->product_id)
            ->first();

        if (!$deliveryNoteItem) {
            return response()->json([
                'success' => false,
                'message' => 'สินค้านี้ไม่อยู่ในรายการใบตัดสต็อก'
            ]);
        }

        // ตรวจสอบว่าสแกนซ้ำหรือไม่
        if ($deliveryNoteItem->hasScannedBarcode($barcode)) {
            return response()->json([
                'success' => false,
                'message' => 'barcode นี้ถูกสแกนไปแล้ว'
            ]);
        }

        // ตรวจสอบว่าสแกนเกินหรือไม่ (แจ้งเตือนแต่ยังสแกนได้)
        $isOverScanned = false;
        $warningMessage = '';
        if ($deliveryNoteItem->scanned_quantity >= $deliveryNoteItem->quantity) {
            $isOverScanned = true;
            $warningMessage = 'เตือน: สินค้ารายการนี้สแกนเกินจำนวนที่กำหนดแล้ว!';
        }

        // บันทึก barcode
        $deliveryNoteItem->addScannedItem($barcode, $stockItem->serial_number);

        // อัปเดตสถานะใบตัดสต็อกเป็น scanned ถ้ายังไม่เป็น
        if (in_array($deliveryNote->status, ['pending', 'confirmed'])) {
            $deliveryNote->update([
                'status' => 'scanned',
                'scanned_by' => Auth::id(),
                'scanned_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $isOverScanned ? $warningMessage : 'สแกนสินค้าเรียบร้อยแล้ว',
            'is_over_scanned' => $isOverScanned,
            'data' => [
                'product_name' => $stockItem->product->name,
                'serial_number' => $stockItem->serial_number,
                'scanned_quantity' => $deliveryNoteItem->fresh()->scanned_quantity,
                'total_quantity' => $deliveryNoteItem->quantity,
                'completion_percentage' => $deliveryNoteItem->fresh()->completion_percentage
            ]
        ]);
    }

    /**
     * ลบ Barcode ที่สแกนไว้
     */
    public function removeScan(Request $request, DeliveryNote $deliveryNote)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:delivery_note_items,id',
            'barcode' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
        }

        $deliveryNoteItem = DeliveryNoteItem::find($request->item_id);

        if ($deliveryNoteItem->delivery_note_id !== $deliveryNote->id) {
            return response()->json(['success' => false, 'message' => 'ข้อมูลไม่ตรงกัน']);
        }

        $deliveryNoteItem->removeScannedItem($request->barcode);

        return response()->json([
            'success' => true,
            'message' => 'ลบรายการสแกนเรียบร้อยแล้ว'
        ]);
    }

    /**
     * หน้าตรวจสอบและอนุมัติ
     */
    public function review(DeliveryNote $deliveryNote)
    {
        // ตรวจสอบสิทธิ์ admin เท่านั้น
        if (!auth()->user()->can('manage-users')) {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'เฉพาะ Admin เท่านั้นที่สามารถตรวจสอบและอนุมัติได้');
        }

        if ($deliveryNote->status !== 'scanned') {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'ต้องสแกนสินค้าก่อนจึงจะตรวจสอบได้');
        }

        $deliveryNote->load(['items.product', 'warehouse']);
        $discrepancies = $deliveryNote->checkDiscrepancies();

        return view('admin.delivery-notes.review', compact('deliveryNote', 'discrepancies'));
    }

    /**
     * อนุมัติและตัดสต็อก
     */
    public function approve(Request $request, DeliveryNote $deliveryNote)
    {
        // ตรวจสอบสิทธิ์ admin เท่านั้น
        if (!auth()->user()->can('manage-users')) {
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('error', 'เฉพาะ Admin เท่านั้นที่สามารถอนุมัติและตัดสต็อกได้');
        }

        // ตรวจสอบว่ามีการสแกนหรือไม่ (ไม่ใช่เช็คสถานะ)
        $totalScanned = 0;
        foreach ($deliveryNote->items as $item) {
            $totalScanned += $item->scanned_quantity;
        }
        
        if ($totalScanned === 0) {
            return redirect()->back()->with('error', 'กรุณาสแกนสินค้าก่อนอนุมัติ');
        }

        // ตรวจสอบว่ามีการสแกนเกินหรือไม่
        $hasOverScanned = false;
        foreach ($deliveryNote->items as $item) {
            if ($item->scanned_quantity > $item->quantity) {
                $hasOverScanned = true;
                break;
            }
        }

        // ถ้าสแกนเกินและไม่ได้บังคับอนุมัติ ให้แจ้งเตือน
        if ($hasOverScanned && !$request->has('force_approve')) {
            return redirect()->back()->with('error', 'ไม่สามารถอนุมัติได้ เนื่องจากมีรายการที่สแกนเกินจำนวนที่กำหนด กรุณาลบรายการที่สแกนเกินออกก่อน หรือใช้ปุ่ม "บังคับอนุมัติ"');
        }

        $forceApprove = $request->has('force_approve');
        $result = $deliveryNote->approveAndCutStock(Auth::id(), $forceApprove);

        if ($result['success']) {
            $message = $result['message'];
            if (!empty($result['discrepancies'])) {
                $message .= ' (มีความไม่ตรงกัน ' . count($result['discrepancies']) . ' รายการ)';
            }
            
            return redirect()->route('admin.delivery-notes.show', $deliveryNote->id)
                ->with('success', $message);
        } else {
            if (isset($result['discrepancies'])) {
                return redirect()->route('admin.delivery-notes.review', $deliveryNote->id)
                    ->with('warning', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        }
    }

    /**
     * พิมพ์ใบตัดสต็อก
     */
    public function print(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['items.product', 'warehouse', 'creator']);
        
        return view('admin.delivery-notes.print', compact('deliveryNote'));
    }
}
