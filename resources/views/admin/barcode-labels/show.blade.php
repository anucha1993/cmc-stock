@extends('adminlte::page')

@section('title', 'เลือกรายการพิมพ์ Label - ' . $product->full_name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>เลือกรายการพิมพ์ Label</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.barcode-labels.index') }}">พิมพ์ Label Barcode</a></li>
                <li class="breadcrumb-item active">{{ $product->full_name }}</li>
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
                                    <h4>{{ $product->full_name }}</h4>
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
                            <button type="button" class="btn btn-sm btn-outline-success" id="selectUnprinted" title="เลือกเฉพาะที่ยังไม่พิมพ์">
                                <i class="fas fa-filter"></i> ยังไม่พิมพ์
                            </button>
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
                                            <th>สถานะพิมพ์</th>
                                            <th>ตำแหน่ง</th>
                                            <th>วันที่เข้า</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stockItems as $stockItem)
                                            <tr class="{{ $stockItem->label_printed_at ? 'table-light' : '' }}">
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                               class="form-check-input stock-item-checkbox" 
                                                               name="stock_item_ids[]" 
                                                               value="{{ $stockItem->id }}"
                                                               data-printed="{{ $stockItem->label_printed_at ? '1' : '0' }}"
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
                                                <td>
                                                    @if($stockItem->label_printed_at)
                                                        <span class="badge badge-success" title="พิมพ์แล้ว {{ $stockItem->label_print_count }} ครั้ง&#10;ล่าสุด: {{ $stockItem->label_printed_at->format('d/m/Y H:i') }}">
                                                            <i class="fas fa-check"></i> พิมพ์แล้ว ({{ $stockItem->label_print_count }})
                                                        </span>
                                                        @php $lastLog = $stockItem->printLogs->first(); @endphp
                                                        @if($lastLog && !$lastLog->verified)
                                                            <br><span class="badge badge-warning mt-1"><i class="fas fa-exclamation-triangle"></i> ยังไม่ยืนยัน</span>
                                                        @elseif($lastLog && $lastLog->verified)
                                                            <br><span class="badge badge-info mt-1"><i class="fas fa-check-double"></i> ยืนยันแล้ว</span>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-times"></i> ยังไม่พิมพ์
                                                        </span>
                                                    @endif
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
                            <div class="alert alert-info mb-2">
                                <div>รายการที่เลือก: <span id="selectedCount">0</span></div>
                                <div class="text-warning" id="reprintWarning" style="display:none;">
                                    <i class="fas fa-exclamation-triangle"></i> พิมพ์ซ้ำ: <span id="reprintCount">0</span> รายการ
                                </div>
                                <div>จำนวนสำเนา: <span id="totalCopies">0</span></div>
                                <div><strong>รวม Label: <span id="totalLabels">0</span> ใบ</strong></div>
                            </div>
                        </div>

                        <!-- เหตุผลพิมพ์ซ้ำ (แสดงเมื่อมีรายการพิมพ์ซ้ำ) -->
                        <div class="form-group" id="reprintReasonGroup" style="display:none;">
                            <label for="reprint_reason"><i class="fas fa-comment"></i> เหตุผลที่พิมพ์ซ้ำ:</label>
                            <textarea class="form-control" name="reprint_reason" id="reprint_reason" rows="2" placeholder="เช่น สติกเกอร์เสีย, หลุด, อ่านไม่ออก"></textarea>
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
                                <div><strong>{{ $product->full_name }}</strong></div>
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

            // เลือกเฉพาะที่ยังไม่พิมพ์
            $('#selectUnprinted').click(function() {
                $('.stock-item-checkbox').each(function() {
                    $(this).prop('checked', $(this).data('printed') == '0');
                });
                updateCheckAllState();
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

                // นับรายการที่เคยพิมพ์แล้ว
                let reprintCount = 0;
                $('.stock-item-checkbox:checked').each(function() {
                    if ($(this).data('printed') == '1') reprintCount++;
                });

                $('#selectedCount').text(selectedCount);
                $('#totalCopies').text(copiesPerItem);
                $('#totalLabels').text(totalLabels);

                // แสดง/ซ่อน warning พิมพ์ซ้ำ
                if (reprintCount > 0) {
                    $('#reprintCount').text(reprintCount);
                    $('#reprintWarning').show();
                    $('#reprintReasonGroup').show();
                } else {
                    $('#reprintWarning').hide();
                    $('#reprintReasonGroup').hide();
                }

                // Enable/disable print button
                $('#printBtn').prop('disabled', selectedCount === 0);
            }

            // Form submission with reprint confirmation
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

                // เช็คว่ามีรายการพิมพ์ซ้ำไหม
                let reprintCount = 0;
                let reprintItems = [];
                $('.stock-item-checkbox:checked').each(function() {
                    if ($(this).data('printed') == '1') {
                        reprintCount++;
                        reprintItems.push($(this).closest('tr').find('code').text());
                    }
                });

                if (reprintCount > 0) {
                    e.preventDefault();
                    const reason = $('#reprint_reason').val();

                    let html = `<div class="text-left">`;
                    html += `<p class="text-danger"><strong><i class="fas fa-exclamation-triangle"></i> มี ${reprintCount} รายการที่เคยพิมพ์แล้ว:</strong></p>`;
                    html += `<ul class="text-sm">`;
                    reprintItems.forEach(item => {
                        html += `<li><code>${item}</code></li>`;
                    });
                    html += `</ul>`;
                    if (!reason) {
                        html += `<p class="text-warning"><i class="fas fa-info-circle"></i> กรุณาระบุเหตุผลที่พิมพ์ซ้ำในช่องด้านซ้ายก่อนพิมพ์</p>`;
                    }
                    html += `</div>`;

                    if (!reason) {
                        Swal.fire({
                            title: 'กรุณาระบุเหตุผล',
                            html: html,
                            icon: 'warning',
                            confirmButtonText: 'ตกลง',
                        });
                        $('#reprint_reason').focus();
                        return false;
                    }

                    Swal.fire({
                        title: 'ยืนยันการพิมพ์ซ้ำ?',
                        html: html + `<p><strong>เหตุผล:</strong> ${reason}</p>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-print"></i> ยืนยันพิมพ์',
                        cancelButtonText: 'ยกเลิก',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#printBtn').html('<i class="fas fa-spinner fa-spin"></i> กำลังเตรียมการพิมพ์...');
                            $('#printBtn').prop('disabled', true);
                            $('#printForm')[0].submit();
                        }
                    });
                    return false;
                }

                // กรณีพิมพ์ใหม่ทั้งหมด — ไม่ต้อง confirm
                $('#printBtn').html('<i class="fas fa-spinner fa-spin"></i> กำลังเตรียมการพิมพ์...');
                $('#printBtn').prop('disabled', true);
            });

            // Initialize
            updateSummary();
        });
    </script>
@stop