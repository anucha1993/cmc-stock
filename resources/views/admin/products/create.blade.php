สินค้าหลัก@extends('adminlte::page')

@section('title', 'เพิ่มสินค้าใหม่')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>เพิ่มสินค้าใหม่</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">สินค้า</a></li>
                <li class="breadcrumb-item active">เพิ่มสินค้า</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลสินค้า</h3>
                </div>
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
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
                                                           value="{{ old('name') }}" 
                                                           placeholder="ชื่อสินค้า"
                                                           required>
                                                    @error('name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="sku">รหัสสินค้า (SKU)</label>
                                                    <input type="text" 
                                                           class="form-control @error('sku') is-invalid @enderror" 
                                                           id="sku" 
                                                           name="sku" 
                                                           value="{{ old('sku') }}" 
                                                           placeholder="จะสร้างอัตโนมัติถ้าไม่ระบุ">
                                                    @error('sku')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="barcode">บาร์โค้ด</label>
                                                    <div class="input-group">
                                                        <input type="text" 
                                                               class="form-control @error('barcode') is-invalid @enderror" 
                                                               id="barcode" 
                                                               name="barcode" 
                                                               value="{{ old('barcode') }}" 
                                                               placeholder="จะสร้างอัตโนมัติถ้าไม่ระบุ">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode()">
                                                                <i class="fas fa-barcode"></i> สร้าง
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @error('barcode')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
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
                                                        <option value="ชิ้น" {{ old('unit') == 'ชิ้น' ? 'selected' : '' }}>ชิ้น</option>
                                                        <option value="แผ่น" {{ old('unit') == 'แผ่น' ? 'selected' : '' }}>แผ่น</option>
                                                        <option value="ต้น" {{ old('unit') == 'ต้น' ? 'selected' : '' }}>ต้น</option>
                                                        <option value="หน่อย" {{ old('unit') == 'หน่อย' ? 'selected' : '' }}>หน่อย</option>
                                                        <option value="กิโลกรัม" {{ old('unit') == 'กิโลกรัม' ? 'selected' : '' }}>กิโลกรัม</option>
                                                        <option value="กรัม" {{ old('unit') == 'กรัม' ? 'selected' : '' }}>กรัม</option>
                                                        <option value="ลิตร" {{ old('unit') == 'ลิตร' ? 'selected' : '' }}>ลิตร</option>
                                                        <option value="มิลลิลิตร" {{ old('unit') == 'มิลลิลิตร' ? 'selected' : '' }}>มิลลิลิตร</option>
                                                        <option value="กล่อง" {{ old('unit') == 'กล่อง' ? 'selected' : '' }}>กล่อง</option>
                                                        <option value="แพ็ค" {{ old('unit') == 'แพ็ค' ? 'selected' : '' }}>แพ็ค</option>
                                                        <option value="เมตร" {{ old('unit') == 'เมตร' ? 'selected' : '' }}>เมตร</option>
                                                    </select>
                                                    @error('unit')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="description">รายละเอียดสินค้า</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" 
                                                      name="description" 
                                                      rows="4" 
                                                      placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับสินค้า">{{ old('description') }}</textarea>
                                            @error('description')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Product Details -->
                                <div class="card">
                                    <div class="card-header">
                                        <h4>ข้อมูลรายละเอียดสินค้า</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="length">ความยาว</label>
                                                    <input type="number" 
                                                           step="0.01"
                                                           class="form-control @error('length') is-invalid @enderror" 
                                                           id="length" 
                                                           name="length" 
                                                           value="{{ old('length') }}" 
                                                           placeholder="ระบุความยาวของสินค้า">
                                                    @error('length')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
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
                                                           value="{{ old('thickness') }}" 
                                                           placeholder="ระบุความหนาของสินค้า">
                                                    @error('thickness')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
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
                                                        <option value="not_specified" {{ old('steel_type') == 'not_specified' ? 'selected' : '' }}>ไม่ระบุ</option>
                                                        <option value="wire_4" {{ old('steel_type') == 'wire_4' ? 'selected' : '' }}>ลวด 4 เส้น</option>
                                                        <option value="wire_5" {{ old('steel_type') == 'wire_5' ? 'selected' : '' }}>ลวด 5 เส้น</option>
                                                        <option value="wire_6" {{ old('steel_type') == 'wire_6' ? 'selected' : '' }}>ลวด 6 เส้น</option>
                                                        <option value="wire_7" {{ old('steel_type') == 'wire_7' ? 'selected' : '' }}>ลวด 7 เส้น</option>
                                                    </select>
                                                    @error('steel_type')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="side_steel_type">ประเภทเหล็กข้าง <span class="text-danger">*</span></label>
                                                    <select class="form-control @error('side_steel_type') is-invalid @enderror" 
                                                            id="side_steel_type" 
                                                            name="side_steel_type">
                                                        <option value="not_specified" {{ old('side_steel_type') == 'not_specified' ? 'selected' : '' }}>ไม่ระบุ</option>
                                                        <option value="no_side_steel" {{ old('side_steel_type') == 'no_side_steel' ? 'selected' : '' }}>ไม่ Show เหล็กข้าง</option>
                                                        <option value="show_side_steel" {{ old('side_steel_type') == 'show_side_steel' ? 'selected' : '' }}>Show เหล็กข้าง</option>
                                                    </select>
                                                    @error('side_steel_type')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="measurement_unit">มาตราวัด <span class="text-danger">*</span></label>
                                                    <select class="form-control @error('measurement_unit') is-invalid @enderror" 
                                                            id="measurement_unit" 
                                                            name="measurement_unit">
                                                        <option value="meter" {{ old('measurement_unit') == 'meter' ? 'selected' : '' }}>เมตร</option>
                                                        <option value="centimeter" {{ old('measurement_unit') == 'centimeter' ? 'selected' : '' }}>เซ็นติเมตร</option>
                                                        <option value="millimeter" {{ old('measurement_unit') == 'millimeter' ? 'selected' : '' }}>มิลลิเมตร</option>
                                                    </select>
                                                    @error('measurement_unit')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Size Type -->
                                <div class="card">
                                    <div class="card-header">
                                        <h4>ประเภทไซส์</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="size_type">ประเภทไซส์ <span class="text-danger">*</span></label>
                                                    <select class="form-control @error('size_type') is-invalid @enderror" 
                                                            id="size_type" 
                                                            name="size_type" 
                                                            required>
                                                        <option value="">เลือกประเภทไซส์</option>
                                                        <option value="standard" {{ old('size_type') == 'standard' ? 'selected' : '' }}>ไซส์มาตรฐาน</option>
                                                        <option value="custom" {{ old('size_type') == 'custom' ? 'selected' : '' }}>ไซส์กำหนดเอง</option>
                                                    </select>
                                                    @error('size_type')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="icheck-primary">
                                                        <input type="checkbox" 
                                                               id="allow_custom_order" 
                                                               name="allow_custom_order" 
                                                               value="1" 
                                                               {{ old('allow_custom_order') ? 'checked' : '' }}>
                                                        <label for="allow_custom_order">
                                                            <i class="fas fa-tools"></i> รับผลิตตามสั่ง
                                                        </label>
                                                    </div>
                                                    <small class="text-muted">เปิดใช้งานการรับออเดอร์ผลิตตามขนาดที่ลูกค้าต้องการ</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="custom_size_options_container" style="display: none;">
                                            <div class="form-group">
                                                <label for="custom_size_options">ตัวเลือกไซส์กำหนดเอง</label>
                                                <textarea class="form-control @error('custom_size_options') is-invalid @enderror" 
                                                          id="custom_size_options" 
                                                          name="custom_size_options" 
                                                          rows="6" 
                                                          placeholder='ระบุตัวเลือกไซส์ในรูปแบบ JSON เช่น:
{
  "widths": ["100 ซม.", "120 ซม.", "150 ซม."],
  "lengths": ["200 ซม.", "250 ซม.", "300 ซม."],
  "thicknesses": ["12 ซม.", "15 ซม.", "18 ซม."]
}'>{{ old('custom_size_options') }}</textarea>
                                                @error('custom_size_options')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                                <small class="text-muted">
                                                    ระบุตัวเลือกในรูปแบบ JSON หรือใช้ปุ่มสร้างแม่แบบด้านล่าง
                                                </small>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="generateSizeTemplate('concrete')">
                                                            <i class="fas fa-cube"></i> แม่แบบคอนกรีต
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="generateSizeTemplate('pipe')">
                                                            <i class="fas fa-circle"></i> แม่แบบท่อ/กลม
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="generateSizeTemplate('beam')">
                                                            <i class="fas fa-minus"></i> แม่แบบคาน
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSizeOptions()">
                                                            <i class="fas fa-eraser"></i> ล้าง
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock Management -->
                                <div class="card">
                                    <div class="card-header">
                                        <h4>การจัดการสต็อก</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="stock_quantity">จำนวนสต็อกปัจจุบัน</label>
                                                    <input type="number" 
                                                           class="form-control @error('stock_quantity') is-invalid @enderror" 
                                                           id="stock_quantity" 
                                                           name="stock_quantity" 
                                                           value="{{ old('stock_quantity', 0) }}" 
                                                           min="0"
                                                           placeholder="0">
                                                    @error('stock_quantity')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">สำหรับสินค้าที่มีอยู่แล้ว</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="min_stock_quantity">สต็อกต่ำสุด <span class="text-danger">*</span></label>
                                                    <input type="number" 
                                                           class="form-control @error('min_stock_quantity') is-invalid @enderror" 
                                                           id="min_stock_quantity" 
                                                           name="min_stock_quantity" 
                                                           value="{{ old('min_stock_quantity', 100) }}" 
                                                           min="0"
                                                           required>
                                                    @error('min_stock_quantity')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">แจ้งเตือนเมื่อสต็อกต่ำกว่านี้</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="max_stock_quantity">สต็อกมากสุด <span class="text-danger">*</span></label>
                                                    <input type="number" 
                                                           class="form-control @error('max_stock_quantity') is-invalid @enderror" 
                                                           id="max_stock_quantity" 
                                                           name="max_stock_quantity" 
                                                           value="{{ old('max_stock_quantity', 500) }}" 
                                                           min="1"
                                                           required>
                                                    @error('max_stock_quantity')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">จำนวนสต็อกสูงสุดที่เก็บได้</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="location">ตำแหน่งเก็บ</label>
                                                    <input type="text" 
                                                           class="form-control @error('location') is-invalid @enderror" 
                                                           id="location" 
                                                           name="location" 
                                                           value="{{ old('location') }}" 
                                                           placeholder="เช่น A1101, B2-05">
                                                    @error('location')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">ระบุตำแหน่งจัดเก็บในคลังสินค้า</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>ขนาดแสดงใน Index</label>
                                                    <div class="form-control-plaintext" id="size_display">
                                                        <span class="text-muted">จะแสดงเมื่อกรอกขนาดแล้ว</span>
                                                    </div>
                                                    <small class="text-muted">แสดงขนาดในรูปแบบ ยาวxหนา หน่วย</small>
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
                                                            {{ old('category_id') == $category->id ? 'selected' : '' }}
                                                            data-color="{{ $category->color }}">
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="supplier_id">ผู้จำหน่าย</label>
                                            <select class="form-control @error('supplier_id') is-invalid @enderror" 
                                                    id="supplier_id" 
                                                    name="supplier_id">
                                                <option value="">เลือกผู้จำหน่าย</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" 
                                                            {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                        {{ $supplier->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('supplier_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
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
                                                <label class="custom-file-label" for="image">เลือกรูปภาพ</label>
                                            </div>
                                            @error('image')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">ไฟล์ที่รองรับ: JPG, PNG, GIF (ขนาดไม่เกิน 2MB)</small>
                                            
                                            <!-- Image Preview -->
                                            <div id="imagePreview" class="mt-3" style="display: none;">
                                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="is_active" 
                                                       name="is_active" 
                                                       {{ old('is_active', true) ? 'checked' : '' }}>
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
                                                    <h6 id="previewName">ชื่อสินค้า</h6>
                                                    <p id="previewCategory" class="mb-1">
                                                        <span class="badge badge-secondary">หมวดหมู่</span>
                                                    </p>
                                                    <p id="previewSupplier" class="mb-1">
                                                        <small class="text-muted">ผู้จำหน่าย: <span id="supplierName">-</span></small>
                                                    </p>
                                                    <p id="previewSize" class="mb-1">
                                                        <small><span id="sizeInfo">-</span></small>
                                                    </p>
                                                    <p id="previewStock" class="mb-0">
                                                        <span class="badge badge-success">สต็อก: <span id="stockInfo">0</span></span>
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
                            <div class="col-md-6">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> บันทึกสินค้า
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
        #productPreview {
            min-height: 80px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-focus first input
            $('#name').focus();

            // Update preview
            $('#name, #category_id, #supplier_id, #length, #thickness, #measurement_unit, #stock_quantity').on('input change', function() {
                updatePreview();
                updateSizeDisplay();
            });

            // Auto generate SKU and Barcode
            $('#name').on('input', function() {
                var name = $(this).val();
                if (name && !$('#sku').val()) {
                    var sku = generateSKU(name);
                    $('#sku').val(sku);
                }
                if (name && !$('#barcode').val()) {
                    generateBarcode();
                }
            });

            // Size type handler
            $('#size_type').on('change', function() {
                var sizeType = $(this).val();
                if (sizeType === 'custom') {
                    $('#custom_size_options_container').slideDown();
                } else {
                    $('#custom_size_options_container').slideUp();
                    $('#custom_size_options').val('');
                }
            });

            // Initialize size type display
            if ($('#size_type').val() === 'custom') {
                $('#custom_size_options_container').show();
            }

            // Initialize preview
            updatePreview();
        });

        function updatePreview() {
            var name = $('#name').val() || 'ชื่อสินค้า';
            var categoryText = $('#category_id option:selected').text() || 'หมวดหมู่';
            var categoryColor = $('#category_id option:selected').data('color') || '#6c757d';
            var supplierText = $('#supplier_id option:selected').text() || '-';
            var stock = $('#stock_quantity').val() || '0';
            
            // Update preview
            $('#previewName').text(name);
            $('#previewCategory .badge').text(categoryText).css('background-color', categoryColor);
            $('#supplierName').text(supplierText);
            $('#stockInfo').text(stock);
            
            // Update size info
            updateSizeDisplay();
        }

        function updateSizeDisplay() {
            var length = $('#length').val();
            var thickness = $('#thickness').val();
            var unit = $('#measurement_unit option:selected').text() || 'เมตร';
            var unitShort = unit === 'เมตร' ? 'ม.' : (unit === 'เซ็นติเมตร' ? 'ซม.' : 'มม.');
            
            var sizeText = '';
            if (length && thickness) {
                sizeText = length + 'x' + thickness + ' ' + unitShort;
            } else if (length) {
                sizeText = length + ' ' + unitShort;
            } else {
                sizeText = '-';
            }
            
            $('#sizeInfo').text(sizeText);
            $('#size_display').html('<span class="text-info">' + sizeText + '</span>');
        }

        function generateSKU(name) {
            // Generate SKU from product name
            var sku = name.toLowerCase()
                         .replace(/[^a-z0-9ก-๙\s]/gi, '')
                         .replace(/\s+/g, '')
                         .substring(0, 6)
                         .toUpperCase();
            
            // Add random numbers
            var timestamp = Date.now().toString().slice(-4);
            return sku + timestamp;
        }

        function generateBarcode() {
            // Generate CMC barcode
            var timestamp = Date.now().toString();
            var random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            var barcode = 'CMC' + timestamp.slice(-5) + random;
            $('#barcode').val(barcode);
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    $('#previewImg').attr('src', e.target.result);
                    $('#imagePreview').show();
                }
                
                                reader.readAsDataURL(input.files[0]);
            }
        }

        // Size template functions
        function generateSizeTemplate(type) {
            var template = '';
            
            switch(type) {
                case 'concrete':
                    template = JSON.stringify({
                        "widths": ["100 ซม.", "120 ซม.", "150 ซม.", "200 ซม."],
                        "lengths": ["200 ซม.", "250 ซม.", "300 ซม.", "400 ซม."],
                        "thicknesses": ["12 ซม.", "15 ซม.", "18 ซม.", "20 ซม."],
                        "finishes": ["เรียบ", "ขัดผิว", "ลายนูน"]
                    }, null, 2);
                    break;
                    
                case 'pipe':
                    template = JSON.stringify({
                        "diameters": ["30 ซม.", "35 ซม.", "40 ซม.", "45 ซม.", "50 ซม."],
                        "lengths": ["6 ม.", "8 ม.", "10 ม.", "12 ม.", "15 ม."],
                        "strengths": ["280 กก./ตร.ซม.", "350 กก./ตร.ซม.", "400 กก./ตร.ซม."],
                        "head_types": ["แบน", "มุ่ง", "สี่เหลี่ยม"]
                    }, null, 2);
                    break;
                    
                case 'beam':
                    template = JSON.stringify({
                        "web_widths": ["20 ซม.", "25 ซม.", "30 ซม.", "35 ซม."],
                        "heights": ["40 ซม.", "50 ซม.", "60 ซม.", "70 ซม."],
                        "lengths": ["600 ซม.", "800 ซม.", "1000 ซม.", "1200 ซม."],
                        "flange_widths": ["40 ซม.", "50 ซม.", "60 ซม.", "80 ซม."]
                    }, null, 2);
                    break;
            }
            
            $('#custom_size_options').val(template);
        }

        function clearSizeOptions() {
            $('#custom_size_options').val('');
        }
                
                // Update file label
                var fileName = input.files[0].name;
                $(input).next('.custom-file-label').text(fileName);
            }
        }

        // Bootstrap file input
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
@stop