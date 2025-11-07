@extends('adminlte::page')

@section('title', 'รายละเอียดหมวดหมู่: ' . $category->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดหมวดหมู่</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">หมวดหมู่สินค้า</a></li>
                <li class="breadcrumb-item active">{{ $category->name }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Category Information -->
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> ข้อมูลหมวดหมู่
                    </h3>
                    <div class="card-tools">
                        <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $category->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">ชื่อหมวดหมู่:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge" style="background-color: {{ $category->color }}; color: {{ $category->getTextColor() }}; font-size: 14px; padding: 8px 12px;">
                                        {{ $category->name }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">รหัสหมวดหมู่:</dt>
                                <dd class="col-sm-8">
                                    <code>{{ $category->code }}</code>
                                </dd>
                                
                                <dt class="col-sm-4">ลำดับแสดง:</dt>
                                <dd class="col-sm-8">{{ $category->sort_order }}</dd>
                                
                                <dt class="col-sm-4">สีหมวดหมู่:</dt>
                                <dd class="col-sm-8">
                                    <div class="d-flex align-items-center">
                                        <div style="width: 20px; height: 20px; background-color: {{ $category->color }}; border: 1px solid #ddd; border-radius: 3px; margin-right: 8px;"></div>
                                        <span>{{ $category->color }}</span>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">สถานะ:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        <i class="fas {{ $category->is_active ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $category->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">จำนวนสินค้า:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-info">{{ $category->products->count() }} รายการ</span>
                                </dd>
                                
                                <dt class="col-sm-4">สร้างเมื่อ:</dt>
                                <dd class="col-sm-8">
                                    {{ $category->created_at->format('d/m/Y H:i') }}
                                    <small class="text-muted">({{ $category->created_at->diffForHumans() }})</small>
                                </dd>
                                
                                <dt class="col-sm-4">อัปเดตล่าสุด:</dt>
                                <dd class="col-sm-8">
                                    {{ $category->updated_at->format('d/m/Y H:i') }}
                                    <small class="text-muted">({{ $category->updated_at->diffForHumans() }})</small>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    @if($category->description)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <dl class="row">
                                    <dt class="col-sm-2">คำอธิบาย:</dt>
                                    <dd class="col-sm-10">
                                        <div class="border rounded p-3" style="background-color: #f8f9fa;">
                                            {{ $category->description }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> ย้อนกลับ
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            @can('categories.edit')
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> แก้ไข
                                </a>
                            @endcan
                            @can('categories.delete')
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> ลบ
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> สถิติ
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-boxes"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">สินค้าทั้งหมด</span>
                            <span class="info-box-number">{{ $category->products->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">สินค้าที่เปิดใช้งาน</span>
                            <span class="info-box-number">{{ $category->products->where('is_active', true)->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">สินค้าใกล้หมด</span>
                            <span class="info-box-number">{{ $category->products->where('stock_quantity', '<=', 'min_stock_quantity')->count() }}</span>
                        </div>
                    </div>

                    @php
                        $totalValue = $category->products->sum(function($product) {
                            return $product->cost_price * $product->stock_quantity;
                        });
                    @endphp
                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-dollar-sign"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">มูลค่าสต็อกรวม</span>
                            <span class="info-box-number">{{ number_format($totalValue, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products List -->
    @if($category->products->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-boxes"></i> สินค้าในหมวดหมู่นี้
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>รหัสสินค้า</th>
                                    <th>ชื่อสินค้า</th>
                                    <th>ราคาซื้อ</th>
                                    <th>ราคาขาย</th>
                                    <th>จำนวนในสต็อก</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->products->take(10) as $product)
                                    <tr>
                                        <td>
                                            <code>{{ $product->code }}</code>
                                        </td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ number_format($product->cost_price, 2) }}</td>
                                        <td>{{ number_format($product->selling_price, 2) }}</td>
                                        <td>
                                            @if($product->stock_quantity <= $product->min_stock_quantity)
                                                <span class="badge badge-danger">{{ $product->stock_quantity }}</span>
                                            @elseif($product->stock_quantity <= $product->min_stock_quantity * 2)
                                                <span class="badge badge-warning">{{ $product->stock_quantity }}</span>
                                            @else
                                                <span class="badge badge-success">{{ $product->stock_quantity }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $product->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                            </span>
                                        </td>
                                        <td>
                                            @can('products.view')
                                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($category->products->count() > 10)
                            <div class="card-footer text-center">
                                <a href="{{ route('admin.products.index', ['category_id' => $category->id]) }}" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> ดูสินค้าทั้งหมด ({{ $category->products->count() }} รายการ)
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Form -->
    @can('categories.delete')
        <form id="delete-form" action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan
@stop

@section('css')
    <style>
        .info-box-number {
            font-size: 24px;
            font-weight: bold;
        }
        .info-box-text {
            font-size: 13px;
        }
        dl.row dt {
            font-weight: 600;
        }
        .table th {
            border-top: none;
        }
    </style>
@stop

@section('js')
    <script>
        function confirmDelete() {
            Swal.fire({
                title: 'ยืนยันการลบ',
                text: 'คุณต้องการลบหมวดหมู่ "{{ $category->name }}" หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก',
                html: `
                    <div class="text-left">
                        <p>คุณต้องการลบหมวดหมู่ <strong>"{{ $category->name }}"</strong> หรือไม่?</p>
                        @if($category->products->count() > 0)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>คำเตือน:</strong> หมวดหมู่นี้มีสินค้า {{ $category->products->count() }} รายการ<br>
                                การลบหมวดหมู่จะส่งผลต่อสินค้าที่เกี่ยวข้อง
                            </div>
                        @endif
                    </div>
                `
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form').submit();
                }
            });
        }
    </script>
@stop