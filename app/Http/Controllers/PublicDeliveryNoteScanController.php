<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\StockItem;
use Illuminate\Http\Request;

class PublicDeliveryNoteScanController extends Controller
{
    /**
     * หน้าสแกน Barcode สาธารณะ (ผ่าน share link + token)
     */
    public function scan(string $slug, Request $request)
    {
        $deliveryNote = DeliveryNote::where('slug', $slug)->firstOrFail();

        // ตรวจ token
        if (!$deliveryNote->isShareTokenValid($request->query('token'))) {
            return response()->view('public.delivery-notes.expired', [], 403);
        }

        // ใบตัดสต็อกที่ completed แล้วให้ดูอย่างเดียว
        if ($deliveryNote->status === 'completed') {
            return response()->view('public.delivery-notes.completed', compact('deliveryNote'));
        }

        $deliveryNote->load(['items.product']);

        return view('public.delivery-notes.scan', [
            'deliveryNote' => $deliveryNote,
            'token' => $request->query('token'),
        ]);
    }

    /**
     * บันทึก Barcode ที่สแกน (จาก public link)
     */
    public function storeScan(string $slug, Request $request)
    {
        $deliveryNote = DeliveryNote::where('slug', $slug)->firstOrFail();

        // ตรวจ token
        if (!$deliveryNote->isShareTokenValid($request->input('token'))) {
            return response()->json(['success' => false, 'message' => 'ลิงก์หมดอายุแล้ว กรุณาขอลิงก์ใหม่'], 403);
        }

        $barcode = $request->input('barcode');
        if (!$barcode) {
            return response()->json(['success' => false, 'message' => 'กรุณาระบุ barcode']);
        }

        // ค้นหา Stock Item
        $stockItem = StockItem::where('barcode', $barcode)
            ->where('status', 'available')
            ->first();

        if (!$stockItem) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบสินค้าที่มี barcode นี้ หรือสินค้าไม่อยู่ในสถานะพร้อมใช้งาน',
            ]);
        }

        // ตรวจสอบว่าสินค้ามีอยู่ในรายการหรือไม่
        $deliveryNoteItem = $deliveryNote->items()
            ->where('product_id', $stockItem->product_id)
            ->first();

        if (!$deliveryNoteItem) {
            return response()->json([
                'success' => false,
                'message' => 'สินค้านี้ไม่อยู่ในรายการใบตัดสต็อก',
            ]);
        }

        // ตรวจสอบว่าสแกนซ้ำหรือไม่
        if ($deliveryNoteItem->hasScannedBarcode($barcode)) {
            return response()->json([
                'success' => false,
                'message' => 'barcode นี้ถูกสแกนไปแล้ว',
            ]);
        }

        // ตรวจสอบว่าสแกนเกินหรือไม่
        $isOverScanned = false;
        $warningMessage = '';
        if ($deliveryNoteItem->scanned_quantity >= $deliveryNoteItem->quantity) {
            $isOverScanned = true;
            $warningMessage = 'เตือน: สินค้ารายการนี้สแกนเกินจำนวนที่กำหนดแล้ว!';
        }

        // บันทึก barcode
        $deliveryNoteItem->addScannedItem($barcode, $stockItem->serial_number);

        // อัปเดตสถานะใบตัดสต็อกเป็น scanned
        if (in_array($deliveryNote->status, ['pending', 'confirmed'])) {
            $deliveryNote->update([
                'status' => 'scanned',
                'scanned_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $isOverScanned ? $warningMessage : 'สแกนสินค้าเรียบร้อยแล้ว',
            'is_over_scanned' => $isOverScanned,
            'data' => [
                'product_name' => $stockItem->product->full_name,
                'barcode' => $barcode,
                'serial_number' => $stockItem->serial_number,
                'item_id' => $deliveryNoteItem->id,
                'scanned_quantity' => $deliveryNoteItem->fresh()->scanned_quantity,
                'total_quantity' => $deliveryNoteItem->quantity,
                'completion_percentage' => $deliveryNoteItem->fresh()->completion_percentage,
            ],
        ]);
    }

    /**
     * ลบ Barcode ที่สแกนแล้ว (unscan)
     */
    public function removeScan(string $slug, Request $request)
    {
        $deliveryNote = DeliveryNote::where('slug', $slug)->firstOrFail();

        // ตรวจ token
        if (!$deliveryNote->isShareTokenValid($request->input('token'))) {
            return response()->json(['success' => false, 'message' => 'ลิงก์หมดอายุแล้ว กรุณาขอลิงก์ใหม่'], 403);
        }

        $barcode = $request->input('barcode');
        $itemId = $request->input('item_id');

        if (!$barcode || !$itemId) {
            return response()->json(['success' => false, 'message' => 'ข้อมูลไม่ครบ']);
        }

        $deliveryNoteItem = $deliveryNote->items()->find($itemId);
        if (!$deliveryNoteItem) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรายการสินค้า']);
        }

        if (!$deliveryNoteItem->hasScannedBarcode($barcode)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบ barcode นี้ในรายการที่สแกนแล้ว']);
        }

        // ลบ barcode
        $deliveryNoteItem->removeScannedItem($barcode);

        // คืนสถานะ StockItem กลับเป็น available (ถ้าต้องการ)
        // StockItem::where('barcode', $barcode)->update(['status' => 'available']);

        return response()->json([
            'success' => true,
            'message' => "ลบ barcode {$barcode} เรียบร้อยแล้ว",
            'data' => [
                'item_id' => $deliveryNoteItem->id,
                'barcode' => $barcode,
                'scanned_quantity' => $deliveryNoteItem->fresh()->scanned_quantity,
                'total_quantity' => $deliveryNoteItem->quantity,
            ],
        ]);
    }
}
