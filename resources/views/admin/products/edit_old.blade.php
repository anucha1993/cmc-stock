@extends('adminlte::page')

@section('title', 'แก้ไขสินค้า: ' . $product->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>แก้ไขสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">สินค้า</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a></li>
                <li class="breadcrumb-item active">แก้ไข</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">แก้ไขข้อมูลสินค้า</h3>
                    <div class="card-tools">
                        <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $product->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                        </span>
                    </div>
                </div>
                <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Info Alert -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>ข้อมูลปัจจุบัน:</strong> 
                            สร้างเมื่อ {{ $product->created_at->format('d/m/Y H:i') }} 
                            | อัปเดตล่าสุด {{ $product->updated_at->format('d/m/Y H:i') }}
                            | สต็อกปัจจุบัน {{ $product->total_stock }} {{ $product->unit }}
                        </div>

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>ข้อมูลพื้นฐาน</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">ชื่อสินค้า <span class="text-danger">*</span></label>
                                                    <input type="text" 
                                                           class="form-control @error('name') is-invalid @enderror" 
                                                           id="name" 
                                                           name="name" 
                                                           value="{{ old('name', $product->name) }}" 
                                                           placeholder="ชื่อสินค้า"
                                                           required>
                                                    @error('name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="sku">รหัสสินค้า (SKU) <span class="text-danger">*</span></label>
                                                    <input type="text" 
                                                           class="form-control @error('sku') is-invalid @enderror" 
                                                           id="sku" 
                                                           name="sku" 
                                                           value="{{ old('sku', $product->sku) }}" 
                                                           placeholder="รหัสสินค้า"
                                                           required>
                                                    @error('sku')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">รหัสปัจจุบัน: <code>{{ $product->sku }}</code></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="barcode">บาร์โค้ด <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" 
                                                               class="form-control @error('barcode') is-invalid @enderror" 
                                                               id="barcode" 
                                                               name="barcode" 
                                                               value="{{ old('barcode', $product->barcode) }}" 
                                                               placeholder="บาร์โค้ด"
                                                               required>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode()">
                                                                <i class="fas fa-barcode"></i> สร้างใหม่
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @error('barcode')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">บาร์โค้ดปัจจุบัน: <code>{{ $product->barcode }}</code></small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="unit">หน่วยนับ <span class="text-danger">*</span></label>
                                                    <select class="form-control @error('unit') is-invalid @enderror" 
                                                            id="unit" 
                                                            name="unit" 
                                                            required>
                                                        <option value="">เลือกหน่วยนับ</option>
                                                        <option value="ชิ้น" {{ old('unit', $product->unit) == 'ชิ้น' ? 'selected' : '' }}>ชิ้น</option>
                                                        <option value="แผ่น" {{ old('unit', $product->unit) == 'แผ่น' ? 'selected' : '' }}>แผ่น</option>
                                                        <option value="ต้น" {{ old('unit', $product->unit) == 'ต้น' ? 'selected' : '' }}>ต้น</option>
                                                        <option value="หน่อย" {{ old('unit', $product->unit) == 'หน่อย' ? 'selected' : '' }}>หน่อย</option>
                                                        <option value="กิโลกรัม" {{ old('unit', $product->unit) == 'กิโลกรัม' ? 'selected' : '' }}>กิโลกรัม</option>
                                                        <option value="กรัม" {{ old('unit', $product->unit) == 'กรัม' ? 'selected' : '' }}>กรัม</option>
                                                        <option value="ลิตร" {{ old('unit', $product->unit) == 'ลิตร' ? 'selected' : '' }}>ลิตร</option>
                                                        <option value="มิลลิลิตร" {{ old('unit', $product->unit) == 'มิลลิลิตร' ? 'selected' : '' }}>มิลลิลิตร</option>
                                                        <option value="กล่อง" {{ old('unit', $product->unit) == 'กล่อง' ? 'selected' : '' }}>กล่อง</option>
                                                        <option value="แพ็ค" {{ old('unit', $product->unit) == 'แพ็ค' ? 'selected' : '' }}>แพ็ค</option>
                                                        <option value="เมตร" {{ old('unit', $product->unit) == 'เมตร' ? 'selected' : '' }}>เมตร</option>
                                                    </select>
                                                    @error('unit')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">หน่วยปัจจุบัน: {{ $product->unit }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="description">รายละเอียดสินค้า</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" 
                                                      name="description" 
                                                      rows="4" 
                                                      placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับสินค้า">{{ old('description', $product->description) }}</textarea>
                                            @error('description')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Product Details Section -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="length">ความยาว</label>
                                                    <input type="number" 
                                                           step="0.01"
                                                           class="form-control @error('length') is-invalid @enderror" 
                                                           id="length" 
                                                           name="length" 
                                                           value="{{ old('length', $product->length) }}" 
                                                           placeholder="ระบุความยาวของสินค้า">
                                                    @error('length')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">เดิม: {{ $product->length ? number_format($product->length, 2) : 'ไม่ระบุ' }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="thickness">ความหนา</label>
                                                    <input type="number" 
                                                           step="0.01"
                                                           class="form-control @error('thickness') is-invalid @enderror" 
                                                           id="thickness" 
                                                           name="thickness" 
                                                           value="{{ old('thickness', $product->thickness) }}" 
                                                           placeholder="ระบุความหนาของสินค้า">
                                                    @error('thickness')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">เดิม: {{ $product->thickness ? number_format($product->thickness, 2) : 'ไม่ระบุ' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="steel_type">ประเภทเหล็ก <span class="text-danger">*</span></label>
                                                    <select class="form-control @error('steel_type') is-invalid @enderror" 
                                                            id="steel_type" 
                                                            name="steel_type">
                                                        <option value="not_specified" {{ old('steel_type', $product->steel_type) == 'not_specified' ? 'selected' : '' }}>ไม่ระบุ</option>
                                                        <option value="wire_4" {{ old('steel_type', $product->steel_type) == 'wire_4' ? 'selected' : '' }}>ลวด 4 เส้น</option>
                                                        <option value="wire_5" {{ old('steel_type', $product->steel_type) == 'wire_5' ? 'selected' : '' }}>ลวด 5 เส้น</option>
                                                        <option value="wire_6" {{ old('steel_type', $product->steel_type) == 'wire_6' ? 'selected' : '' }}>ลวด 6 เส้น</option>
                                                        <option value="wire_7" {{ old('steel_type', $product->steel_type) == 'wire_7' ? 'selected' : '' }}>ลวด 7 เส้น</option>
                                                    </select>
                                                    @error('steel_type')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">เดิม: {{ $product->steel_type_text ?? 'ไม่ระบุ' }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="side_steel_type">ประเภทเหล็กข้าง <span class="text-danger">*</span></label>
                                                    <select class="form-control @error('side_steel_type') is-invalid @enderror" 
                                                            id="side_steel_type" 
                                                            name="side_steel_type">
                                                        <option value="not_specified" {{ old('side_steel_type', $product->side_steel_type) == 'not_specified' ? 'selected' : '' }}>ไม่ระบุ</option>
                                                        <option value="no_side_steel" {{ old('side_steel_type', $product->side_steel_type) == 'no_side_steel' ? 'selected' : '' }}>ไม่ Show เหล็กข้าง</option>
                                                        <option value="show_side_steel" {{ old('side_steel_type', $product->side_steel_type) == 'show_side_steel' ? 'selected' : '' }}>Show เหล็กข้าง</option>
                                                    </select>
                                                    @error('side_steel_type')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">เดิม: {{ $product->side_steel_type_text ?? 'ไม่ระบุ' }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="measurement_unit">มาตราวัด <span class="text-danger">*</span></label>
                                                    <select class="form-control @error('measurement_unit') is-invalid @enderror" 
                                                            id="measurement_unit" 
                                                            name="measurement_unit">
                                                        <option value="meter" {{ old('measurement_unit', $product->measurement_unit) == 'meter' ? 'selected' : '' }}>เมตร</option>
                                                        <option value="centimeter" {{ old('measurement_unit', $product->measurement_unit) == 'centimeter' ? 'selected' : '' }}>เซ็นติเมตร</option>
                                                        <option value="millimeter" {{ old('measurement_unit', $product->measurement_unit) == 'millimeter' ? 'selected' : '' }}>มิลลิเมตร</option>
                                                    </select>
                                                    @error('measurement_unit')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">เดิม: {{ $product->measurement_unit_text ?? 'เมตร' }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Size Type Section -->
                                        <div class="form-group">
                                            <label for="size_type">ประเภทไซส์ <span class="text-danger">*</span></label>
                                            <select class="form-control @error('size_type') is-invalid @enderror" 
                                                    id="size_type" 
                                                    name="size_type" 
                                                    required
                                                    onchange="handleSizeTypeChange()">
                                                <option value="">เลือกประเภทไซส์</option>
                                                <option value="standard" {{ old('size_type', $product->size_type) == 'standard' ? 'selected' : '' }}>ไซส์มาตรฐาน</option>
                                                <option value="custom" {{ old('size_type', $product->size_type) == 'custom' ? 'selected' : '' }}>ไซส์กำหนดเอง</option>
                                            </select>
                                            @error('size_type')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">ปัจจุบัน: {{ $product->size_type_text }}</small>
                                        </div>

                                        <!-- Custom Size Options -->
                                        <div class="form-group" id="customSizeGroup" style="{{ old('size_type', $product->size_type) === 'custom' ? '' : 'display: none;' }}">
                                            <label for="custom_size_options">ตัวเลือกไซส์กำหนดเอง</label>
                                            <textarea class="form-control @error('custom_size_options') is-invalid @enderror" 
                                                      id="custom_size_options" 
                                                      name="custom_size_options" 
                                                      rows="6" 
                                                      placeholder="กรุณาใส่ข้อมูล JSON สำหรับตัวเลือกไซส์กำหนดเอง">{{ old('custom_size_options', $product->custom_size_options ? json_encode($product->custom_size_options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                                            @error('custom_size_options')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">
                                                รูปแบบ JSON สำหรับการกำหนดไซส์ที่ยืดหยุ่น
                                                <br>
                                                <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="generateSizeTemplate('concrete')">
                                                    <i class="fas fa-cube"></i> คอนกรีต
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="generateSizeTemplate('pipe')">
                                                    <i class="fas fa-circle"></i> ท่อ
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-info mt-1" onclick="generateSizeTemplate('beam')">
                                                    <i class="fas fa-minus"></i> คาน
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="clearSizeOptions()">
                                                    <i class="fas fa-trash"></i> ล้างข้อมูล
                                                </button>
                                            </small>
                                        </div>

                                        <!-- Allow Custom Order Checkbox -->
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="allow_custom_order" 
                                                       name="allow_custom_order" 
                                                       {{ old('allow_custom_order', $product->allow_custom_order) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="allow_custom_order">อนุญาตการสั่งผลิตพิเศษ</label>
                                            </div>
                                            <small class="text-muted">เปิดใช้งานเมื่อลูกค้าต้องการสั่งผลิตสินค้าตามขนาดที่กำหนด</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing -->
                                <div class="card">
                                    <div class="card-header">
                                        <h4>ราคาและสต็อก</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="cost_price">ราคาต้นทุน <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('cost_price') is-invalid @enderror" 
                                                               id="cost_price" 
                                                               name="cost_price" 
                                                               value="{{ old('cost_price', $product->cost_price) }}" 
                                                               step="0.01" 
                                                               min="0"
                                                               placeholder="0.00"
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">บาท</span>
                                                        </div>
                                                    </div>
                                                    @error('cost_price')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">เดิม: {{ number_format($product->cost_price, 2) }} บาท</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="selling_price">ราคาขาย <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('selling_price') is-invalid @enderror" 
                                                               id="selling_price" 
                                                               name="selling_price" 
                                                               value="{{ old('selling_price', $product->selling_price) }}" 
                                                               step="0.01" 
                                                               min="0"
                                                               placeholder="0.00"
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">บาท</span>
                                                        </div>
                                                    </div>
                                                    @error('selling_price')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">เดิม: {{ number_format($product->selling_price, 2) }} บาท</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>กำไร</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="profit" readonly>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">บาท</span>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">คำนวณอัตโนมัติ</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="min_stock_quantity">สต็อกต่ำสุด <span class="text-danger">*</span></label>
                                                    <input type="number" 
                                                           class="form-control @error('min_stock_quantity') is-invalid @enderror" 
                                                           id="min_stock_quantity" 
                                                           name="min_stock_quantity" 
                                                           value="{{ old('min_stock_quantity', $product->min_stock) }}" 
                                                           min="0"
                                                           required>
                                                    @error('min_stock_quantity')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">เดิม: {{ $product->min_stock }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="max_stock_quantity">สต็อกมากสุด <span class="text-danger">*</span></label>
                                                    <input type="number" 
                                                           class="form-control @error('max_stock_quantity') is-invalid @enderror" 
                                                           id="max_stock_quantity" 
                                                           name="max_stock_quantity" 
                                                           value="{{ old('max_stock_quantity', $product->max_stock) }}" 
                                                           min="1"
                                                           required>
                                                    @error('max_stock_quantity')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">เดิม: {{ $product->max_stock }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="location">ตำแหน่งเก็บ</label>
                                                    <input type="text" 
                                                           class="form-control @error('location') is-invalid @enderror" 
                                                           id="location" 
                                                           name="location" 
                                                           value="{{ old('location', $product->location) }}" 
                                                           placeholder="เช่น A1-01, ชั้น 2">
                                                    @error('location')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">เดิม: {{ $product->location ?: 'ไม่ระบุ' }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>สต็อกปัจจุบัน</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" value="{{ $product->total_stock }}" readonly>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">{{ $product->unit }}</span>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">สต็อกรวมจากคลังทั้งหมด - ใช้ฟอร์มปรับสต็อกแยกต่างหาก</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categories and Image -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>หมวดหมู่และรูปภาพ</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="category_id">หมวดหมู่ <span class="text-danger">*</span></label>
                                            <select class="form-control @error('category_id') is-invalid @enderror" 
                                                    id="category_id" 
                                                    name="category_id" 
                                                    required>
                                                <option value="">เลือกหมวดหมู่</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" 
                                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}
                                                            data-color="{{ $category->color }}">
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">เดิม: {{ $product->category->name ?? 'ไม่ระบุ' }}</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="supplier_id">ผู้จำหน่าย</label>
                                            <select class="form-control @error('supplier_id') is-invalid @enderror" 
                                                    id="supplier_id" 
                                                    name="supplier_id">
                                                <option value="">เลือกผู้จำหน่าย</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" 
                                                            {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                        {{ $supplier->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('supplier_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">เดิม: {{ $product->supplier->name ?? 'ไม่ระบุ' }}</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="image">รูปภาพสินค้า</label>
                                            <div class="custom-file">
                                                <input type="file" 
                                                       class="custom-file-input @error('image') is-invalid @enderror" 
                                                       id="image" 
                                                       name="image"
                                                       accept="image/*"
                                                       onchange="previewImage(this)">
                                                <label class="custom-file-label" for="image">เลือกรูปภาพใหม่</label>
                                            </div>
                                            @error('image')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">ไฟล์ที่รองรับ: JPG, PNG, GIF (ขนาดไม่เกิน 2MB)</small>
                                            
                                            <!-- Current Image -->
                                            @if($product->image)
                                                <div class="mt-3">
                                                    <label>รูปภาพปัจจุบัน:</label>
                                                    <div>
                                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                                             alt="{{ $product->name }}" 
                                                             class="img-thumbnail" 
                                                             style="max-width: 150px;">
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <!-- New Image Preview -->
                                            <div id="imagePreview" class="mt-3" style="display: none;">
                                                <label>รูปภาพใหม่:</label>
                                                <div>
                                                    <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 150px;">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="is_active" 
                                                       name="is_active" 
                                                       {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">เปิดใช้งาน</label>
                                            </div>
                                        </div>

                                        <!-- Preview Card -->
                                        <div class="card bg-light">
                                            <div class="card-header">
                                                <h6>ตัวอย่างการแสดงผล</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="productPreview">
                                                    <h6 id="previewName">{{ $product->name }}</h6>
                                                    <p id="previewCategory" class="mb-1">
                                                        <span class="badge" style="background-color: {{ $product->category->color ?? '#6c757d' }};">
                                                            {{ $product->category->name ?? 'หมวดหมู่' }}
                                                        </span>
                                                    </p>
                                                    <p id="previewPrice" class="mb-0">
                                                        <strong class="text-success" id="previewPriceText">{{ number_format($product->selling_price, 2) }} บาท</strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ยกเลิก
                                </a>
                            </div>
                            <div class="col-md-8 text-right">
                                <button type="button" class="btn btn-outline-primary" onclick="previewChanges()">
                                    <i class="fas fa-eye"></i> ดูตัวอย่าง
                                </button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
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
        .card .card-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        .form-group label {
            font-weight: 600;
        }
        .custom-control-label {
            font-weight: 500;
        }
        .alert ul {
            padding-left: 20px;
        }
        .img-thumbnail {
            border: 1px solid #dee2e6;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Calculate profit
            $('#cost_price, #selling_price').on('input', function() {
                calculateProfit();
                updatePreview();
            });

            // Update preview
            $('#name, #category_id, #selling_price').on('input change', function() {
                updatePreview();
            });

            // Initialize
            calculateProfit();
            updatePreview();

            // Warn about unsaved changes
            var formChanged = false;
            $('input, textarea, select').on('change', function() {
                formChanged = true;
            });

            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // Clear warning on form submit
            $('form').on('submit', function() {
                formChanged = false;
            });
        });

        function calculateProfit() {
            var cost = parseFloat($('#cost_price').val()) || 0;
            var selling = parseFloat($('#selling_price').val()) || 0;
            var profit = selling - cost;
            
            $('#profit').val(profit.toFixed(2));
            
            // Change color based on profit
            if (profit > 0) {
                $('#profit').removeClass('text-danger').addClass('text-success');
            } else if (profit < 0) {
                $('#profit').removeClass('text-success').addClass('text-danger');
            } else {
                $('#profit').removeClass('text-success text-danger');
            }
        }

        function updatePreview() {
            var name = $('#name').val() || '{{ $product->name }}';
            var categoryId = $('#category_id').val();
            var categoryText = $('#category_id option:selected').text() || '{{ $product->category->name ?? "หมวดหมู่" }}';
            var categoryColor = $('#category_id option:selected').data('color') || '{{ $product->category->color ?? "#6c757d" }}';
            var price = $('#selling_price').val() || '{{ $product->selling_price }}';

            $('#previewName').text(name);
            $('#previewCategory .badge').text(categoryText).css('background-color', categoryColor);
            $('#previewPriceText').text(parseFloat(price).toFixed(2) + ' บาท');
        }

        function generateBarcode() {
            // Generate new CMC barcode
            var timestamp = Date.now().toString();
            var random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            var barcode = 'CMC' + timestamp.slice(-5) + random;
            $('#barcode').val(barcode);
        }

        function previewChanges() {
            var changes = [];
            
            // Check for changes
            if ($('#name').val() !== '{{ $product->name }}') {
                changes.push(`ชื่อ: "{{ $product->name }}" → "${$('#name').val()}"`);
            }
            
            if (parseFloat($('#cost_price').val()) !== {{ $product->cost_price }}) {
                changes.push(`ราคาต้นทุน: {{ number_format($product->cost_price, 2) }} → ${parseFloat($('#cost_price').val()).toFixed(2)} บาท`);
            }
            
            if (parseFloat($('#selling_price').val()) !== {{ $product->selling_price }}) {
                changes.push(`ราคาขาย: {{ number_format($product->selling_price, 2) }} → ${parseFloat($('#selling_price').val()).toFixed(2)} บาท`);
            }

            if ($('#is_active').prop('checked') !== {{ $product->is_active ? 'true' : 'false' }}) {
                changes.push(`สถานะ: {{ $product->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }} → ${$('#is_active').prop('checked') ? 'เปิดใช้งาน' : 'ปิดใช้งาน'}`);
            }

            if (changes.length === 0) {
                Swal.fire({
                    title: 'ไม่มีการเปลี่ยนแปลง',
                    text: 'ข้อมูลยังคงเหมือนเดิม',
                    icon: 'info'
                });
            } else {
                Swal.fire({
                    title: 'ตัวอย่างการเปลี่ยนแปลง',
                    html: `
                        <div class="text-left">
                            <h6>การเปลี่ยนแปลงที่จะเกิดขึ้น:</h6>
                            <ul>
                                ${changes.map(change => `<li>${change}</li>`).join('')}
                            </ul>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: 'เข้าใจแล้ว'
                });
            }
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    $('#previewImg').attr('src', e.target.result);
                    $('#imagePreview').show();
                }
                
                reader.readAsDataURL(input.files[0]);
                
                // Update file label
                var fileName = input.files[0].name;
                $(input).next('.custom-file-label').text(fileName);
            }
        }

        // Size Type Functions
        function handleSizeTypeChange() {
            const sizeType = document.getElementById('size_type').value;
            const customSizeGroup = document.getElementById('customSizeGroup');
            
            if (sizeType === 'custom') {
                customSizeGroup.style.display = 'block';
            } else {
                customSizeGroup.style.display = 'none';
                document.getElementById('custom_size_options').value = '';
            }
        }

        function generateSizeTemplate(type) {
            let template = {};
            
            switch(type) {
                case 'concrete':
                    template = {
                        "dimensions": {
                            "length": {"min": 100, "max": 6000, "unit": "cm", "step": 10},
                            "width": {"min": 10, "max": 100, "unit": "cm", "step": 5},
                            "height": {"min": 10, "max": 100, "unit": "cm", "step": 5}
                        },
                        "weight_capacity": {"min": 50, "max": 5000, "unit": "kg"},
                        "reinforcement": ["เหล็กเส้น", "เหล็กตาข่าย", "เส้นใยสังเคราะห์"],
                        "concrete_grade": ["C20/25", "C25/30", "C30/37", "C35/45"],
                        "surface_finish": ["เรียบ", "หยาบ", "ลายไม้", "ลายหิน"]
                    };
                    break;
                case 'pipe':
                    template = {
                        "diameter": {"min": 5, "max": 200, "unit": "cm", "step": 1},
                        "length": {"min": 50, "max": 1200, "unit": "cm", "step": 10},
                        "thickness": {"min": 0.5, "max": 10, "unit": "cm", "step": 0.5},
                        "pressure_rating": ["Class 1", "Class 2", "Class 3", "Class 4"],
                        "joint_type": ["มอร์แทร์", "ยางรอง", "เชื่อมต่อ"]
                    };
                    break;
                case 'beam':
                    template = {
                        "dimensions": {
                            "length": {"min": 200, "max": 1200, "unit": "cm", "step": 10},
                            "width": {"min": 20, "max": 80, "unit": "cm", "step": 5},
                            "height": {"min": 30, "max": 120, "unit": "cm", "step": 5}
                        },
                        "load_capacity": {"min": 1000, "max": 50000, "unit": "kg"},
                        "reinforcement_type": ["PC", "PSC", "RC"],
                        "end_condition": ["แบบเรียบ", "แบบเสียบ", "แบบหัวค้อน"]
                    };
                    break;
            }
            
            document.getElementById('custom_size_options').value = JSON.stringify(template, null, 2);
        }

        function clearSizeOptions() {
            if (confirm('คุณต้องการล้างข้อมูลตัวเลือกไซส์ทั้งหมดหรือไม่?')) {
                document.getElementById('custom_size_options').value = '';
            }
        }

        // Bootstrap file input
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
@stop