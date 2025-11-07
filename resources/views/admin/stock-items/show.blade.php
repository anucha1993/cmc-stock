@extends('adminlte::page')

@section('title', 'รายละเอียดสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-items.index') }}">จัดการสินค้าแต่ละชิ้น</a></li>
                <li class="breadcrumb-item active">รายละเอียดสินค้า</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลสินค้า</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.stock-items.edit', $stockItem) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> แก้ไข
                        </a>
                        <button type="button" class="btn btn-success btn-sm" onclick="generateQR({{ $stockItem->id }})">
                            <i class="fas fa-qrcode"></i> QR Code
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Barcode</th>
                                    <td><code>{{ $stockItem->barcode }}</code></td>
                                </tr>
                                <tr>
                                    <th>Serial Number</th>
                                    <td>{{ $stockItem->serial_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>สินค้า</th>
                                    <td>
                                        <strong>{{ $stockItem->product->name }}</strong><br>
                                        <small class="text-muted">รหัส: {{ $stockItem->product->code }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>คลังสินค้า</th>
                                    <td>{{ $stockItem->warehouse->name }}</td>
                                </tr>
                                <tr>
                                    <th>แพ</th>
                                    <td>
                                        @if($stockItem->package)
                                            <a href="{{ route('admin.packages.show', $stockItem->package) }}">
                                                {{ $stockItem->package->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>สถานะ</th>
                                    <td>
                                        <span class="badge badge-{{ $stockItem->status_color }}">
                                            {{ $stockItem->status_text }}
                                        </span>
                                        @if($stockItem->isExpired())
                                            <span class="badge badge-danger ml-1">หมดอายุ</span>
                                        @elseif($stockItem->isNearExpiry())
                                            <span class="badge badge-warning ml-1">ใกล้หมดอายุ</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">หมายเลขล็อต</th>
                                    <td>{{ $stockItem->lot_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>หมายเลขแบทช์</th>
                                    <td>{{ $stockItem->batch_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ตำแหน่งในคลัง</th>
                                    <td>{{ $stockItem->location_code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>เกรด</th>
                                    <td>{{ $stockItem->grade ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ขนาด</th>
                                    <td>{{ $stockItem->size ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>วันที่ผลิต</th>
                                    <td>{{ $stockItem->manufacture_date ? $stockItem->manufacture_date->format('d/m/Y') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h5>ข้อมูลวันที่</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">วันที่หมดอายุ</th>
                                    <td>
                                        @if($stockItem->expire_date)
                                            {{ $stockItem->expire_date->format('d/m/Y') }}
                                            @if($stockItem->days_until_expiry !== null)
                                                <br><small class="text-muted">เหลือ {{ $stockItem->days_until_expiry }} วัน</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>วันที่รับเข้าคลัง</th>
                                    <td>{{ $stockItem->received_date ? $stockItem->received_date->format('d/m/Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>วันที่สร้าง</th>
                                    <td>{{ $stockItem->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>แก้ไขล่าสุด</th>
                                    <td>{{ $stockItem->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>ข้อมูลราคา</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">ราคาต้นทุน</th>
                                    <td>{{ $stockItem->cost_price ? '฿' . number_format($stockItem->cost_price, 2) : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ราคาขาย</th>
                                    <td>{{ $stockItem->selling_price ? '฿' . number_format($stockItem->selling_price, 2) : '-' }}</td>
                                </tr>
                                @if($stockItem->cost_price && $stockItem->selling_price)
                                <tr>
                                    <th>กำไร</th>
                                    <td>
                                        ฿{{ number_format($stockItem->selling_price - $stockItem->cost_price, 2) }}
                                        <small class="text-muted">
                                            ({{ number_format((($stockItem->selling_price - $stockItem->cost_price) / $stockItem->cost_price) * 100, 1) }}%)
                                        </small>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>ผู้สร้าง</th>
                                    <td>{{ $stockItem->creator ? $stockItem->creator->name : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($stockItem->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>หมายเหตุ</h5>
                            <div class="alert alert-info">
                                {{ $stockItem->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.stock-items.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> กลับ
                    </a>
                    <a href="{{ route('admin.stock-items.edit', $stockItem) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> แก้ไข
                    </a>
                    <form action="{{ route('admin.stock-items.destroy', $stockItem) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('คุณแน่ใจที่จะลบรายการนี้?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> ลบ
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status Management -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">จัดการสถานะ</h3>
                </div>
                <div class="card-body">
                    <form id="statusForm">
                        <div class="form-group">
                            <label>เปลี่ยนสถานะ</label>
                            <select class="form-control" id="newStatus">
                                <option value="available" {{ $stockItem->status == 'available' ? 'selected' : '' }}>พร้อมใช้งาน</option>
                                <option value="reserved" {{ $stockItem->status == 'reserved' ? 'selected' : '' }}>จองแล้ว</option>
                                <option value="sold" {{ $stockItem->status == 'sold' ? 'selected' : '' }}>ขายแล้ว</option>
                                <option value="damaged" {{ $stockItem->status == 'damaged' ? 'selected' : '' }}>เสียหาย</option>
                                <option value="expired" {{ $stockItem->status == 'expired' ? 'selected' : '' }}>หมดอายุ</option>
                                <option value="returned" {{ $stockItem->status == 'returned' ? 'selected' : '' }}>ส่งคืน</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>หมายเหตุการเปลี่ยนสถานะ</label>
                            <textarea class="form-control" id="statusNotes" rows="3"></textarea>
                        </div>
                        <button type="button" class="btn btn-primary btn-block" onclick="changeStatus()">
                            <i class="fas fa-sync"></i> เปลี่ยนสถานะ
                        </button>
                    </form>
                </div>
            </div>

            <!-- Warehouse Management -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ย้ายคลัง</h3>
                </div>
                <div class="card-body">
                    <form id="warehouseForm">
                        <div class="form-group">
                            <label>คลังปลายทาง</label>
                            <select class="form-control" id="newWarehouse">
                                @foreach(App\Models\Warehouse::orderBy('name')->get() as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $stockItem->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>ตำแหน่งใหม่</label>
                            <input type="text" class="form-control" id="newLocation" value="{{ $stockItem->location_code }}">
                        </div>
                        <button type="button" class="btn btn-success btn-block" onclick="moveWarehouse()">
                            <i class="fas fa-truck"></i> ย้ายคลัง
                        </button>
                    </form>
                </div>
            </div>

            <!-- QR Code -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">QR Code</h3>
                </div>
                <div class="card-body text-center">
                    <div id="qrcode"></div>
                    <p class="mt-2">{{ $stockItem->barcode }}</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="generateQR({{ $stockItem->id }})">
                        <i class="fas fa-qrcode"></i> สร้าง QR Code
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="printQR()">
                        <i class="fas fa-print"></i> พิมพ์
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        function generateQR(stockItemId) {
            fetch(`/admin/stock-items/${stockItemId}/generate-qr`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear previous QR code
                        document.getElementById('qrcode').innerHTML = '';
                        
                        // Generate new QR code
                        QRCode.toCanvas(document.getElementById('qrcode'), data.data, {
                            width: 150,
                            height: 150
                        });
                    } else {
                        alert('เกิดข้อผิดพลาดในการสร้าง QR Code');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการสร้าง QR Code');
                });
        }

        function changeStatus() {
            const status = document.getElementById('newStatus').value;
            const notes = document.getElementById('statusNotes').value;

            fetch(`/admin/stock-items/{{ $stockItem->id }}/change-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status: status,
                    notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('เปลี่ยนสถานะเรียบร้อยแล้ว');
                    location.reload();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเปลี่ยนสถานะ');
            });
        }

        function moveWarehouse() {
            const warehouseId = document.getElementById('newWarehouse').value;
            const locationCode = document.getElementById('newLocation').value;

            fetch(`/admin/stock-items/{{ $stockItem->id }}/move-warehouse`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    warehouse_id: warehouseId,
                    location_code: locationCode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('ย้ายคลังเรียบร้อยแล้ว');
                    location.reload();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการย้ายคลัง');
            });
        }

        function printQR() {
            const qrCanvas = document.querySelector('#qrcode canvas');
            const barcode = '{{ $stockItem->barcode }}';
            
            if (qrCanvas) {
                const dataURL = qrCanvas.toDataURL();
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>QR Code - ${barcode}</title>
                            <style>
                                body { text-align: center; margin: 20px; font-family: Arial, sans-serif; }
                                img { margin: 20px 0; }
                                .barcode { font-family: monospace; font-size: 16px; font-weight: bold; }
                                .product { font-size: 14px; margin-top: 10px; }
                            </style>
                        </head>
                        <body>
                            <img src="${dataURL}" alt="QR Code">
                            <div class="barcode">${barcode}</div>
                            <div class="product">{{ $stockItem->product->name }}</div>
                            <script>window.print(); window.close();</script>
                        </body>
                    </html>
                `);
            }
        }

        // สร้าง QR Code เมื่อโหลดหน้า
        document.addEventListener('DOMContentLoaded', function() {
            generateQR({{ $stockItem->id }});
        });
    </script>
@stop