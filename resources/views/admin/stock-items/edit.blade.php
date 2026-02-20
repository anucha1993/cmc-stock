@extends('adminlte::page')

@section('title', 'แก้ไขรายการสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>แก้ไขรายการสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-items.index') }}">จัดการสินค้าแต่ละชิ้น</a></li>
                <li class="breadcrumb-item active">แก้ไขรายการสินค้า</li>
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
                </div>
                <form action="{{ route('admin.stock-items.update', $stockItem) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="product_id">สินค้า <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('product_id') is-invalid @enderror" name="product_id" id="product_id" required>
                                        <option value="">-- เลือกสินค้า --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('product_id', $stockItem->product_id) == $product->id ? 'selected' : '' }}>
                                                {{ $product->full_name }} ({{ $product->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="warehouse_id">คลังสินค้า <span class="text-danger">*</span></label>
                                    <select class="form-control @error('warehouse_id') is-invalid @enderror" name="warehouse_id" id="warehouse_id" required>
                                        <option value="">-- เลือกคลัง --</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $stockItem->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="package_id">แพ (ถ้ามี)</label>
                                    <select class="form-control @error('package_id') is-invalid @enderror" name="package_id" id="package_id">
                                        <option value="">-- ไม่ใส่ในแพ --</option>
                                        @foreach($packages as $package)
                                            <option value="{{ $package->id }}" {{ old('package_id', $stockItem->package_id) == $package->id ? 'selected' : '' }}>
                                                {{ $package->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('package_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">สถานะ <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" name="status" id="status" required>
                                        <option value="available" {{ old('status', $stockItem->status) == 'available' ? 'selected' : '' }}>พร้อมใช้งาน</option>
                                        <option value="reserved" {{ old('status', $stockItem->status) == 'reserved' ? 'selected' : '' }}>จองแล้ว</option>
                                        <option value="sold" {{ old('status', $stockItem->status) == 'sold' ? 'selected' : '' }}>ขายแล้ว</option>
                                        <option value="damaged" {{ old('status', $stockItem->status) == 'damaged' ? 'selected' : '' }}>เสียหาย</option>
                                        <option value="expired" {{ old('status', $stockItem->status) == 'expired' ? 'selected' : '' }}>หมดอายุ</option>
                                        <option value="returned" {{ old('status', $stockItem->status) == 'returned' ? 'selected' : '' }}>ส่งคืน</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="barcode">Barcode <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('barcode') is-invalid @enderror" name="barcode" id="barcode" value="{{ old('barcode', $stockItem->barcode) }}" required>
                                    @error('barcode')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="serial_number">Serial Number</label>
                                    <input type="text" class="form-control @error('serial_number') is-invalid @enderror" name="serial_number" id="serial_number" value="{{ old('serial_number', $stockItem->serial_number) }}">
                                    @error('serial_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lot_number">หมายเลขล็อต</label>
                                    <input type="text" class="form-control @error('lot_number') is-invalid @enderror" name="lot_number" id="lot_number" value="{{ old('lot_number', $stockItem->lot_number) }}">
                                    @error('lot_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="batch_number">หมายเลขแบทช์</label>
                                    <input type="text" class="form-control @error('batch_number') is-invalid @enderror" name="batch_number" id="batch_number" value="{{ old('batch_number', $stockItem->batch_number) }}">
                                    @error('batch_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="manufacture_date">วันที่ผลิต</label>
                                    <input type="date" class="form-control @error('manufacture_date') is-invalid @enderror" name="manufacture_date" id="manufacture_date" value="{{ old('manufacture_date', $stockItem->manufacture_date ? $stockItem->manufacture_date->format('Y-m-d') : '') }}">
                                    @error('manufacture_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="expire_date">วันที่หมดอายุ</label>
                                    <input type="date" class="form-control @error('expire_date') is-invalid @enderror" name="expire_date" id="expire_date" value="{{ old('expire_date', $stockItem->expire_date ? $stockItem->expire_date->format('Y-m-d') : '') }}">
                                    @error('expire_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="received_date">วันที่รับเข้าคลัง</label>
                                    <input type="date" class="form-control @error('received_date') is-invalid @enderror" name="received_date" id="received_date" value="{{ old('received_date', $stockItem->received_date ? $stockItem->received_date->format('Y-m-d') : '') }}">
                                    @error('received_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cost_price">ราคาต้นทุน</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">฿</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" class="form-control @error('cost_price') is-invalid @enderror" name="cost_price" id="cost_price" value="{{ old('cost_price', $stockItem->cost_price) }}">
                                        @error('cost_price')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="selling_price">ราคาขาย</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">฿</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" class="form-control @error('selling_price') is-invalid @enderror" name="selling_price" id="selling_price" value="{{ old('selling_price', $stockItem->selling_price) }}">
                                        @error('selling_price')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="grade">เกรดสินค้า</label>
                                    <input type="text" class="form-control @error('grade') is-invalid @enderror" name="grade" id="grade" value="{{ old('grade', $stockItem->grade) }}" placeholder="เช่น A, B, C">
                                    @error('grade')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="size">ขนาด</label>
                                    <input type="text" class="form-control @error('size') is-invalid @enderror" name="size" id="size" value="{{ old('size', $stockItem->size) }}">
                                    @error('size')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="location_code">รหัสตำแหน่งในคลัง</label>
                            <input type="text" class="form-control @error('location_code') is-invalid @enderror" name="location_code" id="location_code" value="{{ old('location_code', $stockItem->location_code) }}" placeholder="เช่น A1-B2-C3">
                            @error('location_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" id="notes" rows="3">{{ old('notes', $stockItem->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
                        </button>
                        <a href="{{ route('admin.stock-items.show', $stockItem) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> ยกเลิก
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลปัจจุบัน</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Barcode:</th>
                            <td><code>{{ $stockItem->barcode }}</code></td>
                        </tr>
                        <tr>
                            <th>สินค้า:</th>
                            <td>{{ $stockItem->product->name }}</td>
                        </tr>
                        <tr>
                            <th>คลัง:</th>
                            <td>{{ $stockItem->warehouse->name }}</td>
                        </tr>
                        <tr>
                            <th>สถานะ:</th>
                            <td>
                                <span class="badge badge-{{ $stockItem->status_color }}">
                                    {{ $stockItem->status_text }}
                                </span>
                            </td>
                        </tr>
                        @if($stockItem->expire_date)
                        <tr>
                            <th>หมดอายุ:</th>
                            <td>
                                {{ $stockItem->expire_date->format('d/m/Y') }}
                                @if($stockItem->isExpired())
                                    <span class="badge badge-danger">หมดอายุ</span>
                                @elseif($stockItem->isNearExpiry())
                                    <span class="badge badge-warning">ใกล้หมดอายุ</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>สร้างเมื่อ:</th>
                            <td>{{ $stockItem->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>แก้ไขล่าสุด:</th>
                            <td>{{ $stockItem->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">คำแนะนำ</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-info-circle text-info"></i> <strong>Barcode</strong> ต้องไม่ซ้ำกับที่มีอยู่</li>
                        <li><i class="fas fa-info-circle text-info"></i> <strong>วันที่หมดอายุ</strong> ควรมากกว่าวันที่ผลิต</li>
                        <li><i class="fas fa-info-circle text-info"></i> <strong>ตำแหน่งในคลัง</strong> ช่วยในการจัดเก็บและค้นหา</li>
                        <li><i class="fas fa-info-circle text-info"></i> การแก้ไข<strong>สถานะ</strong>จะมีผลต่อการนับสต็อก</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">การจัดการเร็ว</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.stock-items.show', $stockItem) }}" class="btn btn-info btn-block">
                        <i class="fas fa-eye"></i> ดูรายละเอียด
                    </a>
                    <button type="button" class="btn btn-success btn-block" onclick="generateBarcode({{ $stockItem->id }})">
                        <i class="fas fa-barcode"></i> สร้าง Barcode
                    </button>
                    <form action="{{ route('admin.stock-items.destroy', $stockItem) }}" method="POST" style="display: inline-block; width: 100%;" onsubmit="return confirm('คุณแน่ใจที่จะลบรายการนี้?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> ลบรายการ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barcode Modal -->
<div class="modal fade" id="barcodeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-barcode"></i> Barcode</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <svg id="barcode-svg"></svg>
                <p class="mt-2" id="barcodeText"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" onclick="printBarcode()">พิมพ์</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: '-- เลือกสินค้า --',
                allowClear: true
            });
        });

        function generateBarcode(stockItemId) {
            fetch(`/admin/stock-items/${stockItemId}/generate-barcode`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        JsBarcode('#barcode-svg', data.barcode, {
                            format: 'CODE128',
                            width: 2,
                            height: 80,
                            displayValue: true,
                            fontSize: 16,
                            margin: 10
                        });
                        document.getElementById('barcodeText').textContent = data.product;
                        $('#barcodeModal').modal('show');
                    } else {
                        alert('เกิดข้อผิดพลาดในการสร้าง Barcode');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการสร้าง Barcode');
                });
        }

        function printBarcode() {
            const svgElement = document.getElementById('barcode-svg');
            const productText = document.getElementById('barcodeText').textContent;
            
            if (svgElement) {
                const svgData = new XMLSerializer().serializeToString(svgElement);
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Barcode</title>
                            <style>
                                body { text-align: center; margin: 20px; font-family: Arial, sans-serif; }
                                svg { margin: 20px 0; }
                                .product { font-size: 14px; margin-top: 10px; }
                            </style>
                        </head>
                        <body>
                            ${svgData}
                            <div class="product">${productText}</div>
                            <script>window.print(); window.close();</script>
                        </body>
                    </html>
                `);
            }
        }
    </script>
@stop