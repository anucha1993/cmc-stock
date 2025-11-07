@extends('adminlte::page')

@section('title', 'รายละเอียดคลังสินค้า')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดคลังสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">คลังสินค้า</a></li>
                <li class="breadcrumb-item active">{{ $warehouse->name }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Warehouse Info -->
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลคลังสินค้า</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>ชื่อคลัง:</strong>
                            <p class="text-muted">{{ $warehouse->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>รหัสคลัง:</strong>
                            <p class="text-muted">{{ $warehouse->code }}</p>
                        </div>
                    </div>
                    
                    @if($warehouse->location)
                        <div class="row">
                            <div class="col-md-12">
                                <strong>ที่อยู่/สถานที่:</strong>
                                <p class="text-muted">{{ $warehouse->location }}</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($warehouse->description)
                        <div class="row">
                            <div class="col-md-12">
                                <strong>รายละเอียด:</strong>
                                <p class="text-muted">{{ $warehouse->description }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <strong>สถานะ:</strong>
                            @if($warehouse->is_active)
                                <span class="badge badge-success">เปิดใช้งาน</span>
                            @else
                                <span class="badge badge-danger">ปิดใช้งาน</span>
                            @endif
                            
                            @if($warehouse->is_main)
                                <span class="badge badge-primary">คลังหลัก</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>สร้างเมื่อ:</strong>
                            <p class="text-muted">{{ $warehouse->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">สถิติคลัง</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">จำนวนสินค้า</span>
                            <span class="info-box-number">{{ $warehouse->warehouseProducts->count() }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-cubes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">สต็อกรวม</span>
                            <span class="info-box-number">{{ $warehouse->warehouseProducts->sum('quantity') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">การจัดการ</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100" role="group">
                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> แก้ไขข้อมูล
                        </a>
                        <a href="{{ route('admin.warehouses.stock', $warehouse) }}" class="btn btn-info">
                            <i class="fas fa-list"></i> ดูสต็อกสินค้า
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="fas fa-trash"></i> ลบคลัง
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stock Movements -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">สินค้าในคลัง (ล่าสุด 10 รายการ)</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.warehouses.stock', $warehouse) }}" class="btn btn-sm btn-info">
                            ดูทั้งหมด
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>สินค้า</th>
                                <th>หมวดหมู่</th>
                                <th>จำนวน</th>
                                <th>หน่วย</th>
                                <th>วันที่อัพเดต</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouse->warehouseProducts->take(10) as $warehouseProduct)
                                <tr>
                                    <td>{{ $warehouseProduct->product->name }}</td>
                                    <td>
                                        @if($warehouseProduct->product->category)
                                            <span class="badge" style="background-color: {{ $warehouseProduct->product->category->color }}; color: {{ $warehouseProduct->product->category->getTextColor() }};">
                                                {{ $warehouseProduct->product->category->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $warehouseProduct->quantity > 0 ? 'badge-success' : 'badge-danger' }}">
                                            {{ number_format($warehouseProduct->quantity) }}
                                        </span>
                                    </td>
                                    <td>{{ $warehouseProduct->product->unit }}</td>
                                    <td>{{ $warehouseProduct->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">ไม่มีสินค้าในคลัง</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="delete-form" action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('css')
    <style>
        .info-box {
            margin-bottom: 15px;
        }
        .btn-group-vertical .btn {
            margin-bottom: 5px;
        }
    </style>
@stop

@section('js')
    <script>
        function confirmDelete() {
            Swal.fire({
                title: 'ยืนยันการลบ',
                text: 'คุณแน่ใจหรือไม่ที่จะลบคลัง "{{ $warehouse->name }}"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form').submit();
                }
            });
        }
    </script>
@stop