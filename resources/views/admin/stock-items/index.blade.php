@extends('adminlte::page')

@section('title', 'จัดการสินค้าแต่ละชิ้น')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>จัดการสินค้าแต่ละชิ้น</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">จัดการสินค้าแต่ละชิ้น</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Search & Filter Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ค้นหาและกรอง</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.stock-items.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Barcode</label>
                                    <input type="text" class="form-control" name="barcode" value="{{ request('barcode') }}" placeholder="ระบุ barcode">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>สินค้า</label>
                                    <select class="form-control select2" name="product_id">
                                        <option value="">-- เลือกสินค้า --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>คลังสินค้า</label>
                                    <select class="form-control" name="warehouse_id">
                                        <option value="">-- เลือกคลัง --</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>สถานะ</label>
                                    <select class="form-control" name="status">
                                        <option value="">-- เลือกสถานะ --</option>
                                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>พร้อมใช้งาน</option>
                                        <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>จองแล้ว</option>
                                        <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>ขายแล้ว</option>
                                        <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>เสียหาย</option>
                                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>หมดอายุ</option>
                                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>ส่งคืน</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>หมายเลขล็อต</label>
                                    <input type="text" class="form-control" name="lot_number" value="{{ request('lot_number') }}" placeholder="ระบุหมายเลขล็อต">
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="d-block">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> ค้นหา
                                        </button>
                                        <a href="{{ route('admin.stock-items.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> ล้างการค้นหา
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">รายการสินค้าแต่ละชิ้น</h3>
                    <div class="card-tools">
                        @can('create-edit')
                        <a href="{{ route('admin.stock-items.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> เพิ่มรายการสินค้า
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Barcode</th>
                                <th>สินค้า</th>
                                <th>คลัง</th>
                                <th>ล็อต/แบทช์</th>
                                <th>สถานะ</th>
                                <th>วันหมดอายุ</th>
                                <th>ราคา</th>
                                <th>การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockItems as $item)
                                <tr>
                                    <td>
                                        <code>{{ $item->barcode }}</code>
                                        @if($item->serial_number)
                                            <br><small class="text-muted">SN: {{ $item->serial_number }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $item->product->full_name }}</strong>
                                        @if($item->size)
                                            <br><small class="text-muted">ขนาด: {{ $item->size }}</small>
                                        @endif
                                        @if($item->grade)
                                            <br><small class="text-muted">เกรด: {{ $item->grade }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->warehouse->name }}
                                        @if($item->location_code)
                                            <br><small class="text-muted">ตำแหน่ง: {{ $item->location_code }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->lot_number)
                                            <span class="badge badge-info">L: {{ $item->lot_number }}</span><br>
                                        @endif
                                        @if($item->batch_number)
                                            <span class="badge badge-secondary">B: {{ $item->batch_number }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $item->status_color }}">
                                            {{ $item->status_text }}
                                        </span>
                                        @if($item->isExpired())
                                            <br><span class="badge badge-danger">หมดอายุ</span>
                                        @elseif($item->isNearExpiry())
                                            <br><span class="badge badge-warning">ใกล้หมดอายุ</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->expire_date)
                                            {{ $item->expire_date->format('d/m/Y') }}
                                            @if($item->days_until_expiry !== null)
                                                <br><small class="text-muted">
                                                    {{ $item->days_until_expiry }} วัน
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->selling_price)
                                            ฿{{ number_format($item->selling_price, 2) }}
                                        @endif
                                        @if($item->cost_price)
                                            <br><small class="text-muted">ต้นทุน: ฿{{ number_format($item->cost_price, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.stock-items.show', $item) }}" class="btn btn-sm btn-info" title="ดูรายละเอียด">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('create-edit')
                                            <a href="{{ route('admin.stock-items.edit', $item) }}" class="btn btn-sm btn-warning" title="แก้ไข">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            <button type="button" class="btn btn-sm btn-success" onclick="generateBarcode({{ $item->id }})" title="สร้าง Barcode">
                                                <i class="fas fa-barcode"></i>
                                            </button>
                                            @can('delete')
                                            <form action="{{ route('admin.stock-items.destroy', $item) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('คุณแน่ใจที่จะลบรายการนี้?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="ลบ">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">ไม่พบข้อมูล</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($stockItems->hasPages())
                    <div class="card-footer">
                        {{ $stockItems->appends(request()->query())->links() }}
                    </div>
                @endif
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