@extends('adminlte::page')

@section('title', 'เริ่มตรวจนับสต๊อกใหม่')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>เริ่มตรวจนับสต๊อกใหม่</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-checks.index') }}">ตรวจนับสต๊อก</a></li>
                <li class="breadcrumb-item active">เริ่มใหม่</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-clipboard-check"></i>
                ตั้งค่าการตรวจนับสต๊อก
            </h3>
        </div>
        <form action="{{ route('admin.stock-checks.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">ชื่อการตรวจนับ <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   placeholder="เช่น ตรวจนับประจำเดือน มกราคม 2025"
                                   required>
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="warehouse_id">คลังที่ต้องการตรวจ <span class="text-danger">*</span></label>
                            <select class="form-control @error('warehouse_id') is-invalid @enderror" 
                                    id="warehouse_id" 
                                    name="warehouse_id" 
                                    required>
                                <option value="">เลือกคลัง</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} ({{ $warehouse->location ?? 'ไม่ระบุตำแหน่ง' }})
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
                            <label for="category_id">หมวดหมู่สินค้า (ถ้าต้องการกรอง)</label>
                            <select class="form-control @error('category_id') is-invalid @enderror" 
                                    id="category_id" 
                                    name="category_id">
                                <option value="">ตรวจทุกหมวดหมู่</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                หากไม่เลือก จะตรวจทุกหมวดหมู่ในคลังนี้
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">คำอธิบาย</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับการตรวจนับนี้">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Information Box -->
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info-circle"></i> วิธีการใช้งาน</h5>
                    <ol>
                        <li>กดปุ่ม "เริ่มตรวจนับ" เพื่อสร้าง Session</li>
                        <li>ใช้เครื่องยิง Barcode หรือกล้องมือถือสแกนสินค้าจริง</li>
                        <li>ระบบจะเปรียบเทียบกับข้อมูลในระบบอัตโนมัติ</li>
                        <li>ดูรายงานความแตกต่างและปรับปรุงสต๊อก</li>
                    </ol>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-play"></i> เริ่มตรวจนับ
                </button>
                <a href="{{ route('admin.stock-checks.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> ยกเลิก
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
    </style>
@stop