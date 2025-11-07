@extends('adminlte::page')

@section('title', 'สร้างใบสั่งผลิต')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>สร้างใบสั่งผลิต</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.production-orders.index') }}">ใบสั่งผลิต</a></li>
                <li class="breadcrumb-item active">สร้างใบสั่งผลิต</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">สร้างใบสั่งผลิต</h3>
                </div>
                <form action="{{ route('admin.production-orders.store') }}" method="POST" id="production-form">
                    @csrf
                    <div class="card-body">
                        <!-- การเลือกประเภทการสั่งผลิต -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>ประเภทการสั่งผลิต <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio">
                                                <input class="custom-control-input" 
                                                       type="radio" 
                                                       id="type-package" 
                                                       name="production_type" 
                                                       value="package" 
                                                       checked>
                                                <label for="type-package" class="custom-control-label">
                                                    <i class="fas fa-box-open text-primary"></i>
                                                    <strong>สั่งผลิตจากแพ</strong>
                                                    <br><small class="text-muted">เลือกแพที่ต้องการและระบบจะดึงสินค้าทั้งหมดในแพ</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio">
                                                <input class="custom-control-input" 
                                                       type="radio" 
                                                       id="type-product" 
                                                       name="production_type" 
                                                       value="product">
                                                <label for="type-product" class="custom-control-label">
                                                    <i class="fas fa-cubes text-success"></i>
                                                    <strong>สั่งผลิตแบบเลือกสินค้า</strong>
                                                    <br><small class="text-muted">เลือกสินค้าที่ต้องการทีละรายการ (Add Row)</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ข้อมูลพื้นฐาน -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="warehouse_id">คลังสินค้า <span class="text-danger">*</span></label>
                                    <select class="form-control @error('warehouse_id') is-invalid @enderror" 
                                            id="warehouse_id" 
                                            name="warehouse_id" 
                                            required>
                                        <option value="">เลือกคลังสินค้า</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" 
                                                    {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }} ({{ $warehouse->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="required_date">วันที่ต้องการ <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('required_date') is-invalid @enderror" 
                                           id="required_date" 
                                           name="required_date" 
                                           value="{{ old('required_date', now()->addDays(7)->format('Y-m-d')) }}" 
                                           min="{{ now()->format('Y-m-d') }}"
                                           required>
                                    @error('required_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
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
                            </div>
                        </div>

                        <!-- ส่วนการเลือกแพ -->
                        <div id="package-section" class="mt-4">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">เลือกแพสินค้า</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="package_id">เลือกแพ <span class="text-danger">*</span></label>
                                                <select class="form-control select2 @error('package_id') is-invalid @enderror" 
                                                        id="package_id" 
                                                        name="package_id" 
                                                        onchange="loadPackageProducts()">
                                                    <option value="">เลือกแพสินค้า...</option>
                                                    @if(isset($packages))
                                                        @foreach($packages as $package)
                                                            <option value="{{ $package->id }}" 
                                                                    {{ old('package_id') == $package->id ? 'selected' : '' }}
                                                                    data-items="{{ $package->items_per_package }}"
                                                                    data-length="{{ $package->length_per_package }}">
                                                                {{ $package->name }} ({{ $package->items_per_package }} ชิ้น/แพ)
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                @error('package_id')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="package_quantity">จำนวนแพ <span class="text-danger">*</span></label>
                                                <input type="number" 
                                                       class="form-control @error('package_quantity') is-invalid @enderror" 
                                                       id="package_quantity" 
                                                       name="package_quantity" 
                                                       value="{{ old('package_quantity', 1) }}" 
                                                       min="1"
                                                       onchange="calculatePackageTotal()"
                                                       placeholder="จำนวนแพที่ต้องการ">
                                                @error('package_quantity')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- แสดงรายละเอียดสินค้าในแพ -->
                                    <div id="package-products" class="mt-3" style="display: none;">
                                        <h5>สินค้าในแพ:</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>สินค้า</th>
                                                        <th>จำนวน/แพ</th>
                                                        <th>หน่วย</th>
                                                        <th>รวมที่จะผลิต</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="package-products-list">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ส่วนการเลือกสินค้าแบบ Add Row -->
                        <div id="product-section" class="mt-4" style="display: none;">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">เลือกสินค้าที่ต้องการผลิต</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-sm btn-success" onclick="addProduct()">
                                            <i class="fas fa-plus"></i> เพิ่มสินค้า
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="products-container">
                                        <!-- สินค้าจะถูกเพิ่มที่นี่โดย JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- หมายเหตุ -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">หมายเหตุ</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="3" 
                                              placeholder="หมายเหตุเพิ่มเติมเกี่ยวกับการผลิต">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Product Info Display -->
                        <div id="product-info" class="alert alert-info" style="display: none;">
                            <h5><i class="fas fa-info-circle"></i> ข้อมูลสินค้า</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>SKU:</strong> <span id="product-sku"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>หมวดหมู่:</strong> <span id="product-category"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>สต็อกปัจจุบัน:</strong> <span id="product-stock"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.production-orders.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> สร้างใบสั่งผลิต
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
        #product-info {
            margin-top: 15px;
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

            // Product selection change
            $('#product_id').change(function() {
                var selectedOption = $(this).find('option:selected');
                if (selectedOption.val()) {
                    var sku = selectedOption.data('sku');
                    var category = selectedOption.data('category');
                    
                    $('#product-sku').text(sku);
                    $('#product-category').text(category || '-');
                    $('#product-stock').text('กำลังโหลด...');
                    $('#product-info').show();
                    
                    // You can add AJAX call here to get current stock levels
                    // For now, just show placeholder
                    setTimeout(function() {
                        $('#product-stock').text('ดูข้อมูลในหน้าสินค้า');
                    }, 500);
                } else {
                    $('#product-info').hide();
                }
            });

            // Set minimum date to today
            var today = new Date().toISOString().split('T')[0];
            $('#required_date').attr('min', today);

            // Auto-focus first input
            $('#product_id').focus();

            // Priority color coding
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
            }).trigger('change');
        });
    </script>
@stop