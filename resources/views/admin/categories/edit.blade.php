@extends('adminlte::page')

@section('title', 'แก้ไขหมวดหมู่: ' . $category->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>แก้ไขหมวดหมู่สินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">หมวดหมู่สินค้า</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.categories.show', $category) }}">{{ $category->name }}</a></li>
                <li class="breadcrumb-item active">แก้ไข</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">แก้ไขข้อมูลหมวดหมู่สินค้า</h3>
                    <div class="card-tools">
                        <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $category->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                        </span>
                    </div>
                </div>
                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Info Alert -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>ข้อมูลปัจจุบัน:</strong> 
                            สร้างเมื่อ {{ $category->created_at->format('d/m/Y H:i') }} 
                            | อัปเดตล่าสุด {{ $category->updated_at->format('d/m/Y H:i') }}
                            @if($category->products->count() > 0)
                                | มีสินค้า {{ $category->products->count() }} รายการ
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $category->name) }}" 
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
                                           value="{{ old('code', $category->code) }}" 
                                           placeholder="รหัสหมวดหมู่">
                                    @error('code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">รหัสปัจจุบัน: <code>{{ $category->code }}</code></small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">คำอธิบาย</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="คำอธิบายเกี่ยวกับหมวดหมู่สินค้า">{{ old('description', $category->description) }}</textarea>
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
                                           value="{{ old('sort_order', $category->sort_order) }}" 
                                           placeholder="ลำดับการแสดง"
                                           min="0">
                                    @error('sort_order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">ลำดับปัจจุบัน: {{ $category->sort_order }}</small>
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
                                               value="{{ old('color', $category->color) }}" 
                                               style="height: 38px;">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" onclick="randomColor()">
                                                <i class="fas fa-random"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info" onclick="resetColor()">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('color')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">สีปัจจุบัน: {{ $category->color }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">เปิดใช้งาน</label>
                            </div>
                            @if($category->products->count() > 0)
                                <small class="form-text text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    การปิดใช้งานหมวดหมู่จะส่งผลต่อสินค้า {{ $category->products->count() }} รายการ
                                </small>
                            @endif
                        </div>

                        <!-- Preview -->
                        <div class="form-group">
                            <label>ตัวอย่างการแสดงผล</label>
                            <div class="border rounded p-3" style="background-color: #f8f9fa;">
                                <div class="d-flex align-items-center">
                                    <span class="mr-3">เดิม:</span>
                                    <span class="badge" style="background-color: {{ $category->color }}; color: {{ $category->getTextColor() }};">
                                        {{ $category->name }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center mt-2">
                                    <span class="mr-3">ใหม่:</span>
                                    <span id="preview-badge" class="badge" style="background-color: {{ old('color', $category->color) }}; color: white;">
                                        <span id="preview-text">{{ old('name', $category->name) }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Products Warning -->
                        @if($category->products->count() > 0)
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle"></i> คำเตือน</h5>
                                หมวดหมู่นี้มีสินค้า <strong>{{ $category->products->count() }} รายการ</strong> การเปลี่ยนแปลงอาจส่งผลต่อ:
                                <ul class="mb-0 mt-2">
                                    <li>การแสดงผลสินค้าในระบบ</li>
                                    <li>การค้นหาและจัดกลุ่มสินค้า</li>
                                    <li>รายงานและสถิติต่าง ๆ</li>
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-secondary">
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
        .form-group label {
            font-weight: 600;
        }
        .custom-control-label {
            font-weight: 500;
        }
        .badge {
            font-size: 14px;
            padding: 8px 12px;
        }
        .alert ul {
            padding-left: 20px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            var originalColor = '{{ $category->color }}';
            
            // Auto-focus first input
            $('#name').focus();

            // Update preview on input changes
            $('#name, #color').on('input change', function() {
                updatePreview();
            });

            // Update preview function
            function updatePreview() {
                var name = $('#name').val() || '{{ $category->name }}';
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

        // Random color function
        function randomColor() {
            var colors = [
                '#007bff', '#6c757d', '#28a745', '#dc3545', '#ffc107', 
                '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14', '#20c997'
            ];
            var randomColor = colors[Math.floor(Math.random() * colors.length)];
            $('#color').val(randomColor).trigger('change');
        }

        // Reset to original color
        function resetColor() {
            $('#color').val('{{ $category->color }}').trigger('change');
        }

        // Preview changes function
        function previewChanges() {
            var name = $('#name').val();
            var color = $('#color').val();
            var isActive = $('#is_active').prop('checked');
            var description = $('#description').val();
            var sortOrder = $('#sort_order').val();

            var changes = [];
            
            if (name !== '{{ $category->name }}') {
                changes.push(`ชื่อ: "{{ $category->name }}" → "${name}"`);
            }
            
            if (color !== '{{ $category->color }}') {
                changes.push(`สี: {{ $category->color }} → ${color}`);
            }
            
            if (isActive !== {{ $category->is_active ? 'true' : 'false' }}) {
                changes.push(`สถานะ: {{ $category->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }} → ${isActive ? 'เปิดใช้งาน' : 'ปิดใช้งาน'}`);
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
                            <div class="mt-3 p-3 border rounded">
                                <strong>ตัวอย่างแสดงผล:</strong><br>
                                <span class="badge mt-2" style="background-color: ${color}; color: ${getContrastColor(color)}; font-size: 14px; padding: 8px 12px;">
                                    ${name}
                                </span>
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: 'เข้าใจแล้ว'
                });
            }
        }

        function getContrastColor(hexcolor) {
            var r = parseInt(hexcolor.substr(1,2),16);
            var g = parseInt(hexcolor.substr(3,2),16);
            var b = parseInt(hexcolor.substr(5,2),16);
            var brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000;
            return brightness > 155 ? '#000000' : '#ffffff';
        }
    </script>
@stop