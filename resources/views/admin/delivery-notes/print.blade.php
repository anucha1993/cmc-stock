<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบตัดสต็อก - {{ $deliveryNote->delivery_number }}</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', 'TH Sarabun New', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .header .company-name {
            font-size: 20px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }

        .document-number {
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
            margin-top: 10px;
        }

        /* Info Section */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .info-left, .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }

        .info-box {
            border: 1px solid #ddd;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            min-height: 150px;
        }

        .info-box h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }

        .info-box p {
            margin: 5px 0;
            font-size: 13px;
        }

        .info-box .label {
            display: inline-block;
            width: 100px;
            font-weight: bold;
            color: #555;
        }

        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead {
            background: #34495e;
            color: white;
        }

        .items-table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .items-table tbody tr:hover {
            background: #e8f4f8;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Summary */
        .summary {
            margin-top: 20px;
            text-align: right;
        }

        .summary-row {
            padding: 8px 0;
            font-size: 16px;
        }

        .summary-row.total {
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
            border-top: 2px solid #333;
            margin-top: 10px;
            padding-top: 15px;
        }

        /* Footer */
        .signature-section {
            margin-top: 60px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin: 50px 30px 10px 30px;
            padding-top: 10px;
        }

        .signature-label {
            font-weight: bold;
            color: #555;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }

        .status-pending {
            background: #ffeaa7;
            color: #d63031;
        }

        .status-confirmed {
            background: #74b9ff;
            color: #0984e3;
        }

        .status-scanned {
            background: #a29bfe;
            color: #6c5ce7;
        }

        .status-completed {
            background: #55efc4;
            color: #00b894;
        }

        /* Notes */
        .notes {
            margin-top: 20px;
            padding: 15px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .notes strong {
            color: #856404;
        }

        /* Discrepancy Warning */
        .discrepancy-warning {
            margin-top: 20px;
            padding: 15px;
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        .discrepancy-warning strong {
            font-size: 16px;
        }

        /* Print Styles */
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }

            .container {
                box-shadow: none;
            }

            .items-table tbody tr:hover {
                background: inherit;
            }

            @page {
                margin: 1cm;
            }
        }

        /* Print Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 30px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .print-button:hover {
            background: #2980b9;
        }

        .close-button {
            position: fixed;
            top: 20px;
            right: 180px;
            padding: 12px 30px;
            background: #95a5a6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .close-button:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>
    <!-- Print & Close Buttons -->
    <button onclick="window.print()" class="print-button no-print">
        🖨️ พิมพ์เอกสาร
    </button>
    <button onclick="window.close()" class="close-button no-print">
        ✖️ ปิด
    </button>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">CMC STOCK MANAGEMENT</div>
            <h1>ใบตัดสต็อก / Delivery Note</h1>
            <div class="document-number">{{ $deliveryNote->delivery_number }}</div>
            @if($deliveryNote->status === 'pending')
                <span class="status-badge status-pending">รอดำเนินการ</span>
            @elseif($deliveryNote->status === 'confirmed')
                <span class="status-badge status-confirmed">ยืนยันแล้ว</span>
            @elseif($deliveryNote->status === 'scanned')
                <span class="status-badge status-scanned">สแกนแล้ว</span>
            @elseif($deliveryNote->status === 'completed')
                <span class="status-badge status-completed">เสร็จสมบูรณ์</span>
            @endif
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-left">
                <div class="info-box">
                    <h3>📋 ข้อมูลลูกค้า</h3>
                    <p><span class="label">ชื่อลูกค้า:</span> {{ $deliveryNote->customer_name }}</p>
                    @if($deliveryNote->customer_phone)
                        <p><span class="label">เบอร์โทร:</span> {{ $deliveryNote->customer_phone }}</p>
                    @endif
                    @if($deliveryNote->customer_address)
                        <p><span class="label">ที่อยู่:</span> {{ $deliveryNote->customer_address }}</p>
                    @endif
                </div>
            </div>
            <div class="info-right">
                <div class="info-box">
                    <h3>🚚 ข้อมูลการจัดส่ง</h3>
                    <p><span class="label">วันที่จัดส่ง:</span> {{ $deliveryNote->delivery_date->format('d/m/Y') }}</p>
                    <p><span class="label">คลังสินค้า:</span> {{ $deliveryNote->warehouse->name }}</p>
                    @if($deliveryNote->quotation_number)
                        <p><span class="label">ใบเสนอราคา:</span> {{ $deliveryNote->quotation_number }}</p>
                    @endif
                    <p><span class="label">ผู้สร้าง:</span> {{ $deliveryNote->creator->name }}</p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">ลำดับ</th>
                    <th width="40%">รายการสินค้า</th>
                    <th width="15%" class="text-center">จำนวน</th>
                    <th width="15%" class="text-right">ราคา/หน่วย</th>
                    <th width="25%" class="text-right">ยอดรวม</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryNote->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <small style="color: #7f8c8d;">SKU: {{ $item->product->sku }}</small>
                        
                        @if($deliveryNote->status === 'completed' && $item->scanned_quantity != $item->quantity)
                            <br><small style="color: #e74c3c;">⚠️ สแกนจริง: {{ $item->scanned_quantity }} ชิ้น</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <strong>จำนวนรายการ:</strong> {{ $deliveryNote->items->count() }} รายการ
            </div>
            <div class="summary-row total">
                <strong>ยอดรวมทั้งสิ้น:</strong> {{ number_format($deliveryNote->total_amount, 2) }} บาท
            </div>
        </div>

        <!-- Notes -->
        @if($deliveryNote->notes)
        <div class="notes">
            <strong>📝 หมายเหตุ:</strong><br>
            {{ $deliveryNote->notes }}
        </div>
        @endif

        <!-- Discrepancy Warning -->
        @if($deliveryNote->status === 'completed' && $deliveryNote->discrepancy_notes)
        <div class="discrepancy-warning">
            <strong>⚠️ มีความไม่ตรงกันระหว่างที่วางแผนกับที่สแกนจริง</strong><br>
            {{ $deliveryNote->discrepancy_notes }}
            <br><br>
            <small>ผู้อนุมัติ: {{ $deliveryNote->approver->name ?? 'N/A' }} 
            เมื่อ {{ $deliveryNote->approved_at ? $deliveryNote->approved_at->format('d/m/Y H:i') : 'N/A' }}</small>
        </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">ผู้จัดเตรียม</div>
                    <div style="color: #7f8c8d; font-size: 12px;">Prepared by</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">ผู้ตรวจสอบ</div>
                    <div style="color: #7f8c8d; font-size: 12px;">Checked by</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">ผู้อนุมัติ</div>
                    <div style="color: #7f8c8d; font-size: 12px;">Approved by</div>
                    @if($deliveryNote->status === 'completed' && $deliveryNote->approver)
                        <div style="margin-top: 5px; font-size: 11px;">
                            {{ $deliveryNote->approver->name }}<br>
                            {{ $deliveryNote->approved_at->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top: 40px; text-align: center; color: #7f8c8d; font-size: 12px; border-top: 1px solid #ddd; padding-top: 15px;">
            <p>พิมพ์เมื่อ: {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>CMC Stock Management System | โทร: 02-XXX-XXXX | อีเมล: contact@cmc.com</p>
        </div>
    </div>

    <script>
        // Auto print dialog when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>
