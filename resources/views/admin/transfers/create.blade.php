@extends('adminlte::page')

@section('title', 'สร้างใบโอนสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>สร้างใบโอนสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.transfers.index') }}">การโอนสินค้า</a></li>
                <li class="breadcrumb-item active">สร้างใบโอน</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลการโอนสินค้า</h3>
                </div>
                <form action="{{ route('admin.transfers.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from_warehouse_id">คลังต้นทาง <span class="text-danger">*</span></label>
                                    <select class="form-control @error('from_warehouse_id') is-invalid @enderror" 
                                            id="from_warehouse_id" 
                                            name="from_warehouse_id" 
                                            required>
                                        <option value="">เลือกคลังต้นทาง</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" 
                                                    {{ old('from_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }} ({{ $warehouse->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('from_warehouse_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="to_warehouse_id">คลังปลายทาง <span class="text-danger">*</span></label>
                                    <select class="form-control @error('to_warehouse_id') is-invalid @enderror" 
                                            id="to_warehouse_id" 
                                            name="to_warehouse_id" 
                                            required>
                                        <option value="">เลือกคลังปลายทาง</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" 
                                                    {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }} ({{ $warehouse->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('to_warehouse_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="product_id">สินค้า <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('product_id') is-invalid @enderror" 
                                            id="product_id" 
                                            name="product_id" 
                                            required>
                                        <option value="">เลือกสินค้า</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    {{ old('product_id') == $product->id ? 'selected' : '' }}
                                                    data-sku="{{ $product->sku }}"
                                                    data-category="{{ $product->category->name ?? '' }}">
                                                {{ $product->full_name }} ({{ $product->sku }})
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
                                    <label for="quantity">จำนวนที่โอน <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="{{ old('quantity') }}" 
                                           placeholder="จำนวนที่ต้องการโอน"
                                           min="1"
                                           required>
                                    @error('quantity')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        สต็อกที่สามารถโอนได้: <span id="available-stock">เลือกคลังและสินค้า</span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="priority">ความสำคัญ <span class="text-danger">*</span></label>
                            <select class="form-control @error('priority') is-invalid @enderror" 
                                    id="priority" 
                                    name="priority" 
                                    required>
                                <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>ปกติ</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>ต่ำ</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>สูง</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>เร่งด่วน</option>
                            </select>
                            @error('priority')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="4" 
                                      placeholder="หมายเหตุเพิ่มเติมเกี่ยวกับการโอน">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Transfer Summary -->
                        <div id="transfer-summary" class="alert alert-info" style="display: none;">
                            <h5><i class="fas fa-info-circle"></i> สรุปการโอน</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>สินค้า:</strong> <span id="summary-product"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>จำนวน:</strong> <span id="summary-quantity"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>ความสำคัญ:</strong> <span id="summary-priority"></span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <strong>จาก:</strong> <span id="summary-from"></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>ไป:</strong> <span id="summary-to"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Info -->
                        <div id="stock-info" class="row" style="display: none;">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-warehouse"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">สต็อกต้นทาง</span>
                                        <span class="info-box-number" id="source-stock">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-warehouse"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">สต็อกปลายทาง</span>
                                        <span class="info-box-number" id="destination-stock">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.transfers.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                    <i class="fas fa-save"></i> สร้างใบโอน
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .form-group label {
            font-weight: 600;
        }
        .select2-container {
            width: 100% !important;
        }
        #transfer-summary, #stock-info {
            margin-top: 15px;
        }
        .info-box {
            margin-bottom: 0;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: 'เลือกสินค้า',
                allowClear: true
            });

            // Form validation
            function validateForm() {
                var fromWarehouse = $('#from_warehouse_id').val();
                var toWarehouse = $('#to_warehouse_id').val();
                var product = $('#product_id').val();
                var quantity = $('#quantity').val();

                var isValid = fromWarehouse && toWarehouse && product && quantity &&
                             fromWarehouse !== toWarehouse && quantity > 0;

                $('#submit-btn').prop('disabled', !isValid);
                
                if (isValid) {
                    updateSummary();
                    $('#transfer-summary').show();
                } else {
                    $('#transfer-summary').hide();
                }

                return isValid;
            }

            // Update summary
            function updateSummary() {
                var product = $('#product_id option:selected').text();
                var quantity = $('#quantity').val();
                var priority = $('#priority option:selected').text();
                var fromWarehouse = $('#from_warehouse_id option:selected').text();
                var toWarehouse = $('#to_warehouse_id option:selected').text();

                $('#summary-product').text(product);
                $('#summary-quantity').text(quantity + ' ชิ้น');
                $('#summary-priority').text(priority);
                $('#summary-from').text(fromWarehouse);
                $('#summary-to').text(toWarehouse);
            }

            // Warehouse change events
            $('#from_warehouse_id, #to_warehouse_id').change(function() {
                // Prevent same warehouse selection
                var fromWarehouse = $('#from_warehouse_id').val();
                var toWarehouse = $('#to_warehouse_id').val();

                if (fromWarehouse && toWarehouse && fromWarehouse === toWarehouse) {
                    alert('ไม่สามารถเลือกคลังต้นทางและปลายทางเป็นคลังเดียวกันได้');
                    $(this).val('');
                }

                checkStock();
                validateForm();
            });

            // Product change
            $('#product_id').change(function() {
                checkStock();
                validateForm();
            });

            // Quantity change
            $('#quantity').on('input', function() {
                validateForm();
            });

            // Priority change
            $('#priority').change(function() {
                var priority = $(this).val();
                $(this).removeClass('border-secondary border-info border-warning border-danger');
                
                switch(priority) {
                    case 'low':
                        $(this).addClass('border-secondary');
                        break;
                    case 'normal':
                        $(this).addClass('border-info');
                        break;
                    case 'high':
                        $(this).addClass('border-warning');
                        break;
                    case 'urgent':
                        $(this).addClass('border-danger');
                        break;
                }
                validateForm();
            }).trigger('change');

            // Check stock availability
            function checkStock() {
                var fromWarehouse = $('#from_warehouse_id').val();
                var toWarehouse = $('#to_warehouse_id').val();
                var product = $('#product_id').val();

                if (fromWarehouse && product) {
                    // You can implement AJAX call here to get real stock data
                    // For now, showing placeholder
                    $('#available-stock').text('กำลังตรวจสอบ...');
                    $('#stock-info').show();
                    
                    // Simulate stock check
                    setTimeout(function() {
                        $('#available-stock').text('100 ชิ้น (ตัวอย่าง)');
                        $('#source-stock').text('100');
                        $('#destination-stock').text('50');
                        
                        // Update quantity max
                        $('#quantity').attr('max', 100);
                    }, 500);
                } else {
                    $('#available-stock').text('เลือกคลังและสินค้า');
                    $('#stock-info').hide();
                }
            }

            // Auto-focus first input
            $('#from_warehouse_id').focus();

            // Initial validation
            validateForm();
        });
    </script>
@stop