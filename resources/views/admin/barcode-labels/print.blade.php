<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พิมพ์ Barcode Label</title>
    <style>
        @page {
            margin: 0.5cm;
            size: A4;
        }
        
        body {
            font-family: 'Sarabun', Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        
        .print-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.2cm;
            justify-content: flex-start;
        }
        
        /* Small Label - 4x2 cm */
        .label-small {
            width: 4cm;
            height: 2cm;
            border: 1px solid #000;
            padding: 0.1cm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            page-break-inside: avoid;
        }
        
        .label-small .barcode {
            font-family: 'Libre Barcode 128', monospace;
            font-size: 18px;
            margin: 0.05cm 0;
        }
        
        .label-small .product-name {
            font-size: 8px;
            font-weight: bold;
            line-height: 1;
            margin: 0.02cm 0;
        }
        
        .label-small .product-sku {
            font-size: 7px;
            margin: 0.02cm 0;
        }
        
        .label-small .barcode-text {
            font-size: 6px;
            font-family: monospace;
        }
        
        /* Medium Label - 6x3 cm */
        .label-medium {
            width: 6cm;
            height: 3cm;
            border: 1px solid #000;
            padding: 0.15cm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            page-break-inside: avoid;
        }
        
        .label-medium .barcode {
            font-family: 'Libre Barcode 128', monospace;
            font-size: 24px;
            margin: 0.1cm 0;
        }
        
        .label-medium .product-name {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.2;
            margin: 0.05cm 0;
        }
        
        .label-medium .product-sku {
            font-size: 9px;
            margin: 0.05cm 0;
        }
        
        .label-medium .barcode-text {
            font-size: 8px;
            font-family: monospace;
        }
        
        .label-medium .serial-number {
            font-size: 8px;
            color: #666;
            margin: 0.02cm 0;
        }
        
        /* Large Label - 8x4 cm */
        .label-large {
            width: 8cm;
            height: 4cm;
            border: 1px solid #000;
            padding: 0.2cm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            page-break-inside: avoid;
        }
        
        .label-large .barcode {
            font-family: 'Libre Barcode 128', monospace;
            font-size: 32px;
            margin: 0.15cm 0;
        }
        
        .label-large .product-name {
            font-size: 12px;
            font-weight: bold;
            line-height: 1.3;
            margin: 0.1cm 0;
        }
        
        .label-large .product-sku {
            font-size: 11px;
            margin: 0.05cm 0;
        }
        
        .label-large .barcode-text {
            font-size: 10px;
            font-family: monospace;
        }
        
        .label-large .serial-number {
            font-size: 10px;
            color: #666;
            margin: 0.05cm 0;
        }
        
        .label-large .warehouse {
            font-size: 9px;
            color: #888;
            margin: 0.02cm 0;
        }
        
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        /* Control Panel */
        .control-panel {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .btn {
            padding: 8px 16px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
    </style>
    
    <!-- Google Fonts for Barcode -->
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Control Panel -->
    <div class="control-panel no-print">
        <div style="margin-bottom: 10px;">
            <strong>การพิมพ์ Label Barcode</strong>
        </div>
        <div style="margin-bottom: 10px; font-size: 12px;">
            รายการ: {{ $stockItems->count() }} | 
            สำเนาต่อรายการ: {{ $copiesPerItem }} | 
            รวม: {{ $stockItems->count() * $copiesPerItem }} ใบ
        </div>
        <button class="btn btn-primary" onclick="window.print()">
            🖨️ พิมพ์
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            ❌ ปิด
        </button>
    </div>

    <!-- Labels Container -->
    <div class="print-container">
        @foreach($stockItems as $stockItem)
            @for($copy = 1; $copy <= $copiesPerItem; $copy++)
                <div class="label-{{ $labelSize }}">
                    <!-- Product Name -->
                    <div class="product-name">
                        {{ Str::limit($stockItem->product->name, $labelSize === 'small' ? 25 : ($labelSize === 'medium' ? 35 : 50)) }}
                    </div>
                    
                    <!-- Product SKU -->
                    <div class="product-sku">
                        {{ $stockItem->product->sku }}
                    </div>
                    
                    <!-- Barcode (visual representation) -->
                    <div class="barcode">
                        {{ $stockItem->barcode }}
                    </div>
                    
                    <!-- Barcode Text -->
                    <div class="barcode-text">
                        {{ $stockItem->barcode }}
                    </div>
                    
                    @if($labelSize !== 'small' && $stockItem->serial_number)
                        <!-- Serial Number -->
                        <div class="serial-number">
                            SN: {{ $stockItem->serial_number }}
                        </div>
                    @endif
                    
                    @if($labelSize === 'large')
                        <!-- Warehouse -->
                        <div class="warehouse">
                            คลัง: {{ $stockItem->warehouse->name }}
                        </div>
                    @endif
                </div>
            @endfor
        @endforeach
    </div>

    <script>
        // Auto print when page loads
        window.addEventListener('load', function() {
            // Small delay to ensure everything is loaded
            setTimeout(function() {
                // Ask user if they want to print automatically
                if (confirm('ต้องการพิมพ์ Label ทันทีหรือไม่?')) {
                    window.print();
                }
            }, 500);
        });
        
        // Close window after printing
        window.addEventListener('afterprint', function() {
            if (confirm('พิมพ์เสร็จแล้ว ต้องการปิดหน้าต่างนี้หรือไม่?')) {
                window.close();
            }
        });
    </script>
</body>
</html>