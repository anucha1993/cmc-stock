@extends('adminlte::page')

@section('title', 'แก้ไขการตรวจนับสต๊อก')

@section('content_header')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-2">
        <div>
            <h1 class="h4 h-sm-2 mb-1 mb-sm-0">แก้ไขการตรวจนับสต๊อก</h1>
        </div>
        <div class="d-none d-sm-block">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-checks.index') }}">ตรวจนับสต๊อก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-checks.show', $stockCheck) }}">{{ $stockCheck->session_code }}</a></li>
                <li class="breadcrumb-item active">แก้ไข</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Back Button for Mobile -->
        <div class="d-block d-sm-none mb-3">
            <a href="{{ route('admin.stock-checks.show', $stockCheck) }}" class="btn btn-secondary btn-block btn-lg">
                <i class="fas fa-arrow-left"></i> กลับไปยังรายละเอียด
            </a>
        </div>

        <!-- Edit Form -->
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title text-white">
                    <i class="fas fa-edit"></i> แก้ไขข้อมูลการตรวจนับ
                </h3>
            </div>
            
            <form method="POST" action="{{ route('admin.stock-checks.update', $stockCheck) }}">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <!-- Session Code Display -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>รหัส Session:</strong> {{ $stockCheck->session_code }}
                        <br><small>รหัสนี้ไม่สามารถเปลี่ยนแปลงได้</small>
                    </div>

                    <!-- Title Field -->
                    <div class="form-group">
                        <label for="title" class="font-weight-bold">ชื่อการตรวจนับ <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               class="form-control form-control-lg @error('title') is-invalid @enderror" 
                               value="{{ old('title', $stockCheck->title) }}" 
                               placeholder="เช่น ตรวจนับประจำเดือน มิ.ย. 2567"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description Field -->
                    <div class="form-group">
                        <label for="description" class="font-weight-bold">คำอธิบาย</label>
                        <textarea name="description" 
                                  id="description" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  rows="3" 
                                  placeholder="รายละเอียดเพิ่มเติม (ไม่บังคับ)">{{ old('description', $stockCheck->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Warehouse Selection -->
                    <div class="form-group">
                        <label for="warehouse_id" class="font-weight-bold">คลังสินค้า <span class="text-danger">*</span></label>
                        <select name="warehouse_id" 
                                id="warehouse_id" 
                                class="form-control form-control-lg @error('warehouse_id') is-invalid @enderror" 
                                required>
                            <option value="">เลือกคลังสินค้า</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" 
                                        {{ (old('warehouse_id', $stockCheck->warehouse_id) == $warehouse->id) ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category Selection -->
                    <div class="form-group">
                        <label for="category_id" class="font-weight-bold">หมวดหมู่สินค้า</label>
                        <select name="category_id" 
                                id="category_id" 
                                class="form-control form-control-lg @error('category_id') is-invalid @enderror">
                            <option value="">ทั้งหมด (ไม่จำกัดหมวดหมู่)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ (old('category_id', $stockCheck->category_id) == $category->id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">หากเลือกหมวดหมู่ จะตรวจสอบเฉพาะสินค้าในหมวดหมู่นั้น</small>
                    </div>

                    <!-- Warning about scanned items -->
                    @if($stockCheck->checkItems()->count() > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>คำเตือน:</strong> การเปลี่ยนคลังสินค้าหรือหมวดหมู่อาจส่งผลต่อข้อมูลการสแกนที่มีอยู่
                            <br><small>มีรายการที่สแกนแล้ว {{ $stockCheck->checkItems()->count() }} รายการ</small>
                        </div>
                    @endif
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-6 mb-2 mb-sm-0">
                            <a href="{{ route('admin.stock-checks.show', $stockCheck) }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-times"></i> ยกเลิก
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Additional Info Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ข้อมูลเพิ่มเติม</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>สร้างเมื่อ:</strong> {{ $stockCheck->created_at->format('d/m/Y H:i:s') }}<br>
                        <strong>โดย:</strong> {{ $stockCheck->creator->name }}<br>
                        <strong>สถานะ:</strong> 
                        @if($stockCheck->status === 'active')
                            <span class="badge badge-success">กำลังดำเนินการ</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>รายการที่สแกนแล้ว:</strong> {{ $stockCheck->checkItems()->count() }} รายการ<br>
                        <strong>สแกนล่าสุด:</strong> 
                        @if($stockCheck->checkItems()->count() > 0)
                            {{ $stockCheck->checkItems()->max('last_scanned_at') ? \Carbon\Carbon::parse($stockCheck->checkItems()->max('last_scanned_at'))->format('d/m/Y H:i:s') : 'ไม่มีข้อมูล' }}
                        @else
                            ยังไม่มีการสแกน
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <style>
        /* Mobile-First Design */
        body {
            font-size: 16px;
        }
        
        .form-control {
            min-height: 44px;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .form-control-lg {
            min-height: 50px;
            font-size: 18px;
        }
        
        .btn {
            min-height: 44px;
            border-radius: 8px;
        }
        
        @media (max-width: 576px) {
            .container-fluid {
                padding: 10px;
            }
            
            .card {
                border-radius: 12px;
                margin-bottom: 15px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Form validation
            $('form').on('submit', function() {
                var submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true)
                         .html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...');
            });

            // Show confirmation if there are scanned items
            @if($stockCheck->checkItems()->count() > 0)
                var originalWarehouse = $('#warehouse_id').val();
                var originalCategory = $('#category_id').val();
                
                $('form').on('submit', function(e) {
                    var currentWarehouse = $('#warehouse_id').val();
                    var currentCategory = $('#category_id').val();
                    
                    if (currentWarehouse !== originalWarehouse || currentCategory !== originalCategory) {
                        if (!confirm('การเปลี่ยนคลังสินค้าหรือหมวดหมู่อาจส่งผลต่อข้อมูลการสแกนที่มีอยู่\nต้องการดำเนินการต่อหรือไม่?')) {
                            e.preventDefault();
                            $(this).find('button[type="submit"]').prop('disabled', false)
                                   .html('<i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง');
                            return false;
                        }
                    }
                });
            @endif
        });
    </script>
@stop