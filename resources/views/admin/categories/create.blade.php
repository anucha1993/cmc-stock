@extends('adminlte::page')

@section('title', 'เพิ่มหมวดหมู่สินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>เพิ่มหมวดหมู่สินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">หมวดหมู่สินค้า</a></li>
                <li class="breadcrumb-item active">เพิ่มหมวดหมู่</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลหมวดหมู่สินค้า</h3>
                </div>
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="ชื่อหมวดหมู่สินค้า"
                                           required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">รหัสหมวดหมู่</label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code') }}" 
                                           placeholder="รหัสหมวดหมู่ (จะสร้างอัตโนมัติถ้าไม่ระบุ)">
                                    @error('code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">หากไม่ระบุ จะสร้างจากชื่อหมวดหมู่อัตโนมัติ</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">คำอธิบาย</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="คำอธิบายเกี่ยวกับหมวดหมู่สินค้า">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">ลำดับการแสดง</label>
                                    <input type="number" 
                                           class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" 
                                           name="sort_order" 
                                           value="{{ old('sort_order', 0) }}" 
                                           placeholder="ลำดับการแสดง"
                                           min="0">
                                    @error('sort_order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">หมายเลขที่น้อยกว่าจะแสดงก่อน</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">สีหมวดหมู่</label>
                                    <div class="input-group">
                                        <input type="color" 
                                               class="form-control @error('color') is-invalid @enderror" 
                                               id="color" 
                                               name="color" 
                                               value="{{ old('color', '#007bff') }}" 
                                               style="height: 38px;">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" onclick="randomColor()">
                                                <i class="fas fa-random"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('color')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">สีที่ใช้แสดงในระบบ</small>
                                </div>
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

                        <!-- Preview -->
                        <div class="form-group">
                            <label>ตัวอย่างการแสดงผล</label>
                            <div class="border rounded p-3" style="background-color: #f8f9fa;">
                                <span id="preview-badge" class="badge" style="background-color: #007bff; color: white;">
                                    <span id="preview-text">หมวดหมู่ตัวอย่าง</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> บันทึก
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
        .custom-control-label {
            font-weight: 500;
        }
        #preview-badge {
            font-size: 14px;
            padding: 8px 12px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-focus first input
            $('#name').focus();

            // Auto generate code from name
            $('#name').on('input', function() {
                var name = $(this).val();
                var code = name.toLowerCase()
                              .replace(/[^a-z0-9ก-๙\s]/gi, '') // Keep only letters, numbers, and Thai characters
                              .replace(/\s+/g, '-') // Replace spaces with hyphens
                              .substring(0, 20); // Limit length
                
                if (!$('#code').val() || $('#code').data('auto-generated')) {
                    $('#code').val(code).data('auto-generated', true);
                }

                // Update preview
                updatePreview();
            });

            // Manual code edit
            $('#code').on('input', function() {
                $(this).removeData('auto-generated');
            });

            // Color change
            $('#color').on('change', function() {
                updatePreview();
            });

            // Update preview function
            function updatePreview() {
                var name = $('#name').val() || 'หมวดหมู่ตัวอย่าง';
                var color = $('#color').val();
                
                $('#preview-text').text(name);
                $('#preview-badge').css('background-color', color);
                
                // Calculate text color based on background
                var textColor = getContrastColor(color);
                $('#preview-badge').css('color', textColor);
            }

            // Get contrast color (black or white)
            function getContrastColor(hexcolor) {
                // Convert hex to RGB
                var r = parseInt(hexcolor.substr(1,2),16);
                var g = parseInt(hexcolor.substr(3,2),16);
                var b = parseInt(hexcolor.substr(5,2),16);
                
                // Calculate brightness
                var brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000;
                
                return brightness > 155 ? '#000000' : '#ffffff';
            }

            // Initial preview update
            updatePreview();
        });

        // Random color function
        function randomColor() {
            var colors = [
                '#007bff', '#6c757d', '#28a745', '#dc3545', '#ffc107', 
                '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14', '#20c997'
            ];
            var randomColor = colors[Math.floor(Math.random() * colors.length)];
            $('#color').val(randomColor).trigger('change');
        }
    </script>
@stop