@extends('adminlte::page')

@section('title', 'เลือกรายการพิมพ์ Label - ' . $product->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>เลือกรายการพิมพ์ Label</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.barcode-labels.index') }}">พิมพ์ Label Barcode</a></li>
                <li class="breadcrumb-item active">{{ $product->name }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Product Info -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-box"></i> ข้อมูลสินค้า
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            @if($product->images && count($product->images) > 0)
                                <img src="{{ asset('storage/' . $product->images[0]) }}" 
                                     alt="{{ $product->name }}" 
                                     class="img-fluid rounded">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                     style="height: 150px;">
                                    <i class="fas fa-box fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>{{ $product->name }}</h4>
                                    <div class="mb-2">
                                        <strong>รหัสสินค้า:</strong> {{ $product->sku }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Barcode:</strong> <code>{{ $product->barcode }}</code>
                                    </div>
                                    <div class="mb-2">
                                        <strong>หมวดหมู่:</strong> 
                                        @if($product->category)
                                            <span class="badge" style="background-color: {{ $product->category->color }}; color: {{ $product->category->getTextColor() }};">
                                                {{ $product->category->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">ไม่ระบุ</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <strong>หน่วย:</strong> {{ $product->unit }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>รายการทั้งหมด:</strong> 
                                        <span class="badge badge-info">{{ $stockItems->count() }} รายการ</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>พร้อมใช้งาน:</strong> 
                                        <span class="badge badge-success">{{ $stockItems->where('status', 'available')->count() }} รายการ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Form -->
    <form action="{{ route('admin.barcode-labels.print') }}" method="POST" id="printForm">
        @csrf
        <div class="row">
            <!-- Stock Items List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> รายการสินค้าแต่ละชิ้น
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-primary" id="selectAll">
                                <i class="fas fa-check-square"></i> เลือกทั้งหมด
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" id="clearAll">
                                <i class="fas fa-square"></i> ยกเลิกทั้งหมด
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($stockItems->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50px">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                                    <label class="form-check-label" for="checkAll"></label>
                                                </div>
                                            </th>
                                            <th>Barcode</th>
                                            <th>Serial Number</th>
                                            <th>คลัง</th>
                                            <th>สถานะ</th>
                                            <th>ตำแหน่ง</th>
                                            <th>วันที่เข้า</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stockItems as $stockItem)
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                               class="form-check-input stock-item-checkbox" 
                                                               name="stock_item_ids[]" 
                                                               value="{{ $stockItem->id }}"
                                                               id="item_{{ $stockItem->id }}">
                                                        <label class="form-check-label" for="item_{{ $stockItem->id }}"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code class="text-primary">{{ $stockItem->barcode }}</code>
                                                </td>
                                                <td>{{ $stockItem->serial_number ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ $stockItem->warehouse->name }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $stockItem->status_color }}">
                                                        {{ $stockItem->status_text }}
                                                    </span>
                                                </td>
                                                <td>{{ $stockItem->location_code ?? '-' }}</td>
                                                <td>{{ $stockItem->received_date ? $stockItem->received_date->format('d/m/Y') : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                <h5>ไม่มีรายการสินค้าพร้อมพิมพ์</h5>
                                <p>สินค้านี้ไม่มี StockItem ที่มี Barcode แยกแต่ละชิ้น</p>
                                <p class="text-info">ต้องสั่งผลิตก่อน เพื่อสร้าง StockItem ที่มี Barcode เฉพาะแต่ละชิ้น</p>
                                
                                <div class="mt-3">
                                    <a href="{{ route('admin.production-orders.create') }}" class="btn btn-primary">
                                        <i class="fas fa-industry"></i> สั่งผลิตสินค้านี้
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Print Settings -->
            <div class="col-md-4">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cog"></i> การตั้งค่าการพิมพ์
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="label_size">ขนาด Label:</label>
                            <select class="form-control" name="label_size" id="label_size" required>
                                <option value="small">เล็ก (4x2 ซม.)</option>
                                <option value="medium" selected>กลาง (6x3 ซม.)</option>
                                <option value="large">ใหญ่ (8x4 ซม.)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="copies_per_item">จำนวนสำเนาต่อรายการ:</label>
                            <select class="form-control" name="copies_per_item" id="copies_per_item" required>
                                <option value="1" selected>1 สำเนา</option>
                                <option value="2">2 สำเนา</option>
                                <option value="3">3 สำเนา</option>
                                <option value="5">5 สำเนา</option>
                                <option value="10">10 สำเนา</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>สรุปการพิมพ์:</label>
                            <div class="alert alert-info">
                                <div>รายการที่เลือก: <span id="selectedCount">0</span></div>
                                <div>จำนวนสำเนา: <span id="totalCopies">0</span></div>
                                <div><strong>รวม Label: <span id="totalLabels">0</span> ใบ</strong></div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success btn-block" id="printBtn" disabled>
                                <i class="fas fa-print"></i> พิมพ์ Label
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-eye"></i> ตัวอย่าง Label
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <div id="labelPreview" class="border rounded p-3" style="display: inline-block; min-width: 150px;">
                            <div class="barcode-preview mb-2">
                                <i class="fas fa-barcode fa-2x"></i>
                            </div>
                            <div class="text-sm">
                                <div><strong>{{ $product->name }}</strong></div>
                                <div>{{ $product->sku }}</div>
                                <div><small>Barcode จะแสดงที่นี่</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Back Button -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-footer">
                    <a href="{{ route('admin.barcode-labels.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> กลับไปเลือกสินค้า
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Check All functionality
            $('#checkAll').change(function() {
                $('.stock-item-checkbox').prop('checked', this.checked);
                updateSummary();
            });

            $('#selectAll').click(function() {
                $('.stock-item-checkbox').prop('checked', true);
                $('#checkAll').prop('checked', true);
                updateSummary();
            });

            $('#clearAll').click(function() {
                $('.stock-item-checkbox').prop('checked', false);
                $('#checkAll').prop('checked', false);
                updateSummary();
            });

            // Individual checkbox change
            $('.stock-item-checkbox').change(function() {
                updateCheckAllState();
                updateSummary();
            });

            // Copies change
            $('#copies_per_item').change(function() {
                updateSummary();
            });

            function updateCheckAllState() {
                const total = $('.stock-item-checkbox').length;
                const checked = $('.stock-item-checkbox:checked').length;
                
                $('#checkAll').prop('checked', total === checked);
                $('#checkAll').prop('indeterminate', checked > 0 && checked < total);
            }

            function updateSummary() {
                const selectedCount = $('.stock-item-checkbox:checked').length;
                const copiesPerItem = parseInt($('#copies_per_item').val());
                const totalLabels = selectedCount * copiesPerItem;

                $('#selectedCount').text(selectedCount);
                $('#totalCopies').text(copiesPerItem);
                $('#totalLabels').text(totalLabels);

                // Enable/disable print button
                $('#printBtn').prop('disabled', selectedCount === 0);
            }

            // Form validation
            $('#printForm').on('submit', function(e) {
                if ($('.stock-item-checkbox:checked').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'กรุณาเลือกรายการที่ต้องการพิมพ์อย่างน้อย 1 รายการ',
                        icon: 'warning',
                        confirmButtonText: 'ตกลง'
                    });
                    return false;
                }

                // แสดง loading
                $('#printBtn').html('<i class="fas fa-spinner fa-spin"></i> กำลังเตรียมการพิมพ์...');
                $('#printBtn').prop('disabled', true);
            });

            // Initialize
            updateSummary();
        });
    </script>
@stop