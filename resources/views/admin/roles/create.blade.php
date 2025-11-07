@extends('adminlte::page')

@section('title', 'สร้างบทบาทใหม่ - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>สร้างบทบาทใหม่</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">บทบาท</a></li>
                <li class="breadcrumb-item active">สร้างบทบาทใหม่</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">เพิ่มบทบาทใหม่</h3>
        </div>
        
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">ชื่อบทบาท (ภาษาอังกฤษ) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">เช่น: admin, member (ใช้ตัวพิมพ์เล็ก ไม่มีช่องว่าง)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="display_name">ชื่อแสดง <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                                   id="display_name" name="display_name" value="{{ old('display_name') }}" required>
                            @error('display_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="level">ระดับบทบาท <span class="text-danger">*</span></label>
                            <select class="form-control @error('level') is-invalid @enderror" id="level" name="level" required>
                                <option value="">เลือกระดับบทบาท</option>
                                <option value="1" {{ old('level') == 1 ? 'selected' : '' }}>1 - Master Admin</option>
                                <option value="2" {{ old('level') == 2 ? 'selected' : '' }}>2 - Admin</option>
                                <option value="3" {{ old('level') == 3 ? 'selected' : '' }}>3 - Member</option>
                            </select>
                            @error('level')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="is_active">สถานะ</label>
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', 1) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    เปิดใช้งาน
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">คำอธิบาย</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3" placeholder="คำอธิบายเกี่ยวกับบทบาทนี้">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> สร้างบทบาท
                </button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> ยกเลิก
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .text-danger {
            color: #dc3545 !important;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto generate name from display_name
            $('#display_name').on('input', function() {
                let displayName = $(this).val();
                let name = displayName.toLowerCase()
                    .replace(/[^a-z0-9\s]/g, '')
                    .replace(/\s+/g, '-')
                    .trim('-');
                $('#name').val(name);
            });
            
            // Form validation
            $('form').on('submit', function(e) {
                let level = $('#level').val();
                let name = $('#name').val();
                let displayName = $('#display_name').val();
                
                if (!level || !name || !displayName) {
                    e.preventDefault();
                    alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                    return false;
                }
            });
        });
    </script>
@stop