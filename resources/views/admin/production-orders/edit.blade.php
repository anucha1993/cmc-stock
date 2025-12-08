@extends('adminlte::page')

@section('title', 'แก้ไขใบสั่งผลิต')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>แก้ไขใบสั่งผลิต</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.production-orders.index') }}">ใบสั่งผลิต</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.production-orders.show', $productionOrder) }}">{{ $productionOrder->order_code }}</a></li>
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
                    <h3 class="card-title">แก้ไขใบสั่งผลิต: {{ $productionOrder->order_code }}</h3>
                </div>
                <form action="{{ route('admin.production-orders.update', $productionOrder) }}" method="POST" id="production-form">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- ข้อมูลพื้นฐาน -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="target_warehouse_id">คลังปลายทาง <span class="text-danger">*</span></label>
                                    <select class="form-control @error('target_warehouse_id') is-invalid @enderror" 
                                            id="target_warehouse_id" 
                                            name="target_warehouse_id" 
                                            required>
                                        <option value="">เลือกคลังปลายทาง</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" 
                                                    {{ old('target_warehouse_id', $productionOrder->target_warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }} ({{ $warehouse->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('target_warehouse_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="storage_location">
                                        <i class="fas fa-map-marker-alt"></i> ตำแหน่งเก็บในคลัง
                                        <small class="text-muted">(ไม่บังคับ)</small>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('storage_location') is-invalid @enderror" 
                                           id="storage_location" 
                                           name="storage_location" 
                                           value="{{ old('storage_location', $productionOrder->storage_location) }}"
                                           placeholder="เช่น A1-01, SHELF-A-001, ZONE-B-05">
                                    <small class="form-text text-muted">
                                        ระบุตำแหน่งที่จะจัดเก็บสินค้าเมื่อผลิตเสร็จ
                                    </small>
                                    @error('storage_location')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="due_date">วันที่ต้องการ <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" 
                                           name="due_date" 
                                           value="{{ old('due_date', $productionOrder->due_date->format('Y-m-d')) }}" 
                                           min="{{ now()->format('Y-m-d') }}"
                                           required>
                                    @error('due_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="priority">ความสำคัญ <span class="text-danger">*</span></label>
                                    <select class="form-control @error('priority') is-invalid @enderror" 
                                            id="priority" 
                                            name="priority" 
                                            required>
                                        <option value="normal" {{ old('priority', $productionOrder->priority) == 'normal' ? 'selected' : '' }}>ปกติ</option>
                                        <option value="low" {{ old('priority', $productionOrder->priority) == 'low' ? 'selected' : '' }}>ต่ำ</option>
                                        <option value="high" {{ old('priority', $productionOrder->priority) == 'high' ? 'selected' : '' }}>สูง</option>
                                        <option value="urgent" {{ old('priority', $productionOrder->priority) == 'urgent' ? 'selected' : '' }}>เร่งด่วน</option>
                                    </select>
                                    @error('priority')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- แสดงข้อมูลประเภทการสั่งผลิต (ไม่สามารถแก้ไขได้) -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">ข้อมูลการสั่งผลิต (ไม่สามารถแก้ไขได้)</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <th width="30%">ประเภท:</th>
                                                        <td>
                                                            @if($productionOrder->order_type === 'package')
                                                                <i class="fas fa-box-open text-primary"></i> สั่งผลิตจากแพ
                                                            @elseif($productionOrder->order_type === 'multiple')
                                                                <i class="fas fa-cubes text-success"></i> สั่งผลิตหลายรายการ
                                                            @else
                                                                <i class="fas fa-cube text-info"></i> สั่งผลิตสินค้าเดี่ยว
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @if($productionOrder->order_type === 'package' && $productionOrder->package)
                                                        <tr>
                                                            <th>แพสินค้า:</th>
                                                            <td>
                                                                <strong>{{ $productionOrder->package->name }}</strong><br>
                                                                <small class="text-muted">{{ $productionOrder->package->code }}</small>
                                                            </td>
                                                        </tr>
                                                    @elseif($productionOrder->order_type === 'single' && $productionOrder->product)
                                                        <tr>
                                                            <th>สินค้า:</th>
                                                            <td>
                                                                <strong>{{ $productionOrder->product->name }}</strong><br>
                                                                <small class="text-muted">SKU: {{ $productionOrder->product->sku }}</small>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <th>จำนวนรวม:</th>
                                                        <td>
                                                            <div class="form-group">
                                                                <input type="number" 
                                                                       class="form-control @error('quantity') is-invalid @enderror" 
                                                                       name="quantity" 
                                                                       value="{{ old('quantity', $productionOrder->quantity) }}" 
                                                                       min="{{ $productionOrder->produced_quantity }}"
                                                                       required>
                                                                <small class="form-text text-muted">
                                                                    ผลิตแล้ว: {{ number_format($productionOrder->produced_quantity) }}
                                                                </small>
                                                                @error('quantity')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <th width="30%">สถานะ:</th>
                                                        <td>
                                                            <span class="badge badge-{{ $productionOrder->status_color }}">
                                                                {{ $productionOrder->status_text }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>ผู้ขอ:</th>
                                                        <td>{{ $productionOrder->requestedBy->name ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>วันที่สร้าง:</th>
                                                        <td>{{ $productionOrder->requested_at->format('d/m/Y H:i') }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- แสดงรายการสินค้าในกรณี multiple หรือ package -->
                        @if($productionOrder->order_type === 'multiple' || $productionOrder->order_type === 'package')
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card card-secondary">
                                        <div class="card-header">
                                            <h3 class="card-title">รายการสินค้าที่สั่งผลิต</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>สินค้า</th>
                                                            <th width="15%">จำนวนสั่งผลิต</th>
                                                            <th width="15%">ผลิตแล้ว</th>
                                                            <th width="15%">ความคืบหน้า</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($productionOrder->items as $item)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $item->product->name }}</strong><br>
                                                                    <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-primary">{{ number_format($item->quantity) }}</span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-success">{{ number_format($item->produced_quantity) }}</span>
                                                                </td>
                                                                <td>
                                                                    <div class="progress progress-sm">
                                                                        <div class="progress-bar bg-success" 
                                                                             style="width: {{ $item->progress_percentage }}%">
                                                                        </div>
                                                                    </div>
                                                                    <small>{{ round($item->progress_percentage, 1) }}%</small>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted">ไม่มีรายการสินค้า</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>หมายเหตุ:</strong> ไม่สามารถแก้ไขรายการสินค้าได้ หากต้องการเปลี่ยนแปลง กรุณาสร้างใบสั่งผลิตใหม่
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- หมายเหตุ -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">หมายเหตุ</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3" 
                                              placeholder="หมายเหตุเพิ่มเติมเกี่ยวกับการผลิต">{{ old('description', $productionOrder->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.production-orders.show', $productionOrder) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> บันทึกการแก้ไข
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
        .table th {
            background-color: #f1f1f1;
        }
        
        .form-group label {
            font-weight: 600;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
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

            // Form validation
            $('#production-form').on('submit', function(e) {
                const quantity = parseInt($('input[name="quantity"]').val());
                const producedQuantity = {{ $productionOrder->produced_quantity }};
                
                if (quantity < producedQuantity) {
                    e.preventDefault();
                    alert('จำนวนที่สั่งผลิตไม่สามารถน้อยกว่าจำนวนที่ผลิตแล้ว (' + producedQuantity.toLocaleString() + ')');
                    return false;
                }
            });
        });
    </script>
@stop