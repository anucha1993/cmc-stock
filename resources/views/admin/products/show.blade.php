@extends('adminlte::page')

@section('title', 'รายละเอียดสินค้า: ' . $product->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดสินค้า</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">สินค้า</a></li>
                <li class="breadcrumb-item active">{{ $product->name }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Product Information -->
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> ข้อมูลสินค้า
                    </h3>
                    <div class="card-tools">
                        <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $product->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <dl class="row">
                                <dt class="col-sm-4">ชื่อสินค้า:</dt>
                                <dd class="col-sm-8">
                                    <h5>{{ $product->name }}</h5>
                                </dd>
                                
                                <dt class="col-sm-4">รหัสสินค้า:</dt>
                                <dd class="col-sm-8">
                                    <code>{{ $product->sku }}</code>
                                </dd>
                                
                                <dt class="col-sm-4">บาร์โค้ด:</dt>
                                <dd class="col-sm-8">
                                    <div class="d-flex align-items-center">
                                        <code class="mr-2">{{ $product->barcode }}</code>
                                        <button class="btn btn-sm btn-outline-primary" onclick="printBarcode()">
                                            <i class="fas fa-print"></i> พิมพ์
                                        </button>
                                    </div>
                                </dd>
                                
                                <dt class="col-sm-4">หน่วยนับ:</dt>
                                <dd class="col-sm-8">{{ $product->unit }}</dd>
                                
                                <dt class="col-sm-4">ประเภทไซส์:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-{{ $product->size_type_color }}">
                                        {{ $product->size_type_text }}
                                    </span>
                                    @if($product->allow_custom_order)
                                        <span class="badge badge-success ml-1">
                                            <i class="fas fa-tools"></i> รับผลิตตามสั่ง
                                        </span>
                                    @endif
                                </dd>
                                
                                @if($product->isCustomSize() && $product->custom_size_options_array)
                                <dt class="col-sm-4">ตัวเลือกไซส์:</dt>
                                <dd class="col-sm-8">
                                    <div class="border rounded p-2" style="background-color: #f8f9fa;">
                                        @foreach($product->custom_size_options_array as $optionKey => $optionValues)
                                            <div class="mb-2">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $optionKey)) }}:</strong>
                                                <div class="mt-1">
                                                    @foreach($optionValues as $value)
                                                        <span class="badge badge-light mr-1">{{ $value }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </dd>
                                @endif
                                
                                <dt class="col-sm-4">หมวดหมู่:</dt>
                                <dd class="col-sm-8">
                                    @if($product->category)
                                        <span class="badge" style="background-color: {{ $product->category->color }}; color: {{ $product->category->getTextColor() }};">
                                            {{ $product->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">ผู้จำหน่าย:</dt>
                                <dd class="col-sm-8">
                                    @if($product->supplier)
                                        <a href="{{ route('admin.suppliers.show', $product->supplier) }}">
                                            {{ $product->supplier->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">ตำแหน่งเก็บ:</dt>
                                <dd class="col-sm-8">{{ $product->location ?: '-' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-4">
                            @if($product->image)
                                <div class="text-center">
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="img-fluid img-thumbnail"
                                         style="max-height: 200px;">
                                </div>
                            @else
                                <div class="text-center">
                                    <div class="bg-light d-flex align-items-center justify-content-center img-thumbnail" 
                                         style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($product->description)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <dl class="row">
                                    <dt class="col-sm-2">รายละเอียด:</dt>
                                    <dd class="col-sm-10">
                                        <div class="border rounded p-3" style="background-color: #f8f9fa;">
                                            {{ $product->description }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    @endif

                    <!-- Product Details -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h5><i class="fas fa-info-circle"></i> รายละเอียดสินค้า</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-3">ความยาว:</dt>
                                        <dd class="col-sm-3">
                                            @if($product->length)
                                                {{ number_format($product->length, 2) }} {{ $product->measurement_unit_text }}
                                            @else
                                                <span class="text-muted">ไม่ระบุ</span>
                                            @endif
                                        </dd>
                                        
                                        <dt class="col-sm-3">ความหนา:</dt>
                                        <dd class="col-sm-3">
                                            @if($product->thickness)
                                                {{ number_format($product->thickness, 2) }} {{ $product->measurement_unit_text }}
                                            @else
                                                <span class="text-muted">ไม่ระบุ</span>
                                            @endif
                                        </dd>
                                        
                                        <dt class="col-sm-3">ประเภทเหล็ก:</dt>
                                        <dd class="col-sm-3">
                                            <span class="badge badge-secondary">{{ $product->steel_type_text }}</span>
                                        </dd>
                                        
                                        <dt class="col-sm-3">ประเภทเหล็กข้าง:</dt>
                                        <dd class="col-sm-3">
                                            <span class="badge badge-{{ $product->side_steel_type === 'show_side_steel' ? 'info' : 'secondary' }}">
                                                {{ $product->side_steel_type_text }}
                                            </span>
                                        </dd>
                                        
                                        <dt class="col-sm-3">มาตราวัด:</dt>
                                        <dd class="col-sm-9">
                                            <span class="badge badge-primary">{{ $product->measurement_unit_text }}</span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warehouse Stock Details -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h5><i class="fas fa-warehouse"></i> สต็อกในคลัง</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    @if($product->warehouseProducts->count() > 0)
                                        <div class="row">
                                            @foreach($product->warehouseProducts as $warehouseProduct)
                                                <div class="col-md-4 mb-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon {{ $warehouseProduct->quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                                            <i class="fas fa-warehouse"></i>
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">{{ $warehouseProduct->warehouse->name }}</span>
                                                            <span class="info-box-number">
                                                                {{ number_format($warehouseProduct->quantity) }} {{ $product->unit }}
                                                            </span>
                                                            @if($warehouseProduct->available_quantity != $warehouseProduct->quantity)
                                                                <span class="info-box-more">
                                                                    พร้อมใช้: {{ number_format($warehouseProduct->available_quantity) }} 
                                                                    (จอง: {{ number_format($warehouseProduct->reserved_quantity) }})
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <strong>รวมสต็อกทั้งหมด: {{ number_format($product->total_stock) }} {{ $product->unit }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-box-open fa-2x"></i>
                            <p class="mt-2">ยังไม่มีสต็อกในคลังใดๆ</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Items -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-barcode"></i> รายการสินค้าแต่ละชิ้น
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.stock-items.create', ['product_id' => $product->id]) }}" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> เพิ่มรายการ
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($product->stockItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Barcode</th>
                                        <th>Serial Number</th>
                                        <th>คลัง</th>
                                        <th>สถานะ</th>
                                        <th>ตำแหน่ง</th>
                                        <th>วันที่เข้า</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->stockItems->take(10) as $stockItem)
                                        <tr>
                                            <td>
                                                <code>{{ $stockItem->barcode }}</code>
                                            </td>
                                            <td>{{ $stockItem->serial_number }}</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $stockItem->warehouse->name }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $stockItem->status_color }}">
                                                    {{ $stockItem->status_text }}
                                                </span>
                                            </td>
                                            <td>{{ $stockItem->location_code ?? '-' }}</td>
                                            <td>{{ $stockItem->received_date ? $stockItem->received_date->format('d/m/Y') : '-' }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.stock-items.show', $stockItem) }}" 
                                                       class="btn btn-info" title="ดูรายละเอียด">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.stock-items.edit', $stockItem) }}" 
                                                       class="btn btn-warning" title="แก้ไข">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($product->stockItems->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('admin.stock-items.index', ['product_id' => $product->id]) }}" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-list"></i> ดูทั้งหมด ({{ $product->stockItems->count() }} รายการ)
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <h5>ยังไม่มีรายการสินค้าแต่ละชิ้น</h5>
                            <p>เริ่มต้นโดยการเพิ่มรายการสินค้าแต่ละชิ้นสำหรับสินค้านี้</p>
                            <a href="{{ route('admin.stock-items.create', ['product_id' => $product->id]) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-plus"></i> เพิ่มรายการแรก
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>                    <!-- Timestamps -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <dl class="row">
                                <dt class="col-sm-2">สร้างเมื่อ:</dt>
                                <dd class="col-sm-4">
                                    {{ $product->created_at->format('d/m/Y H:i') }}
                                    <small class="text-muted">({{ $product->created_at->diffForHumans() }})</small>
                                </dd>
                                
                                <dt class="col-sm-2">อัปเดตล่าสุด:</dt>
                                <dd class="col-sm-4">
                                    {{ $product->updated_at->format('d/m/Y H:i') }}
                                    <small class="text-muted">({{ $product->updated_at->diffForHumans() }})</small>
                                </dd>
                            </dl>
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
                            @can('products.edit')
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> แก้ไข
                                </a>
                            @endcan
                            @can('products.delete')
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> ลบ
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics and Stock -->
        <div class="col-md-4">
            <!-- Product Details -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> รายละเอียดสินค้า
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-industry"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">ผู้จำหน่าย</span>
                            <span class="info-box-number">{{ $product->supplier->name ?? 'ไม่ระบุ' }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-ruler-combined"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">ขนาด</span>
                            <span class="info-box-number">
                                @if($product->length && $product->thickness)
                                    {{ number_format($product->length, 0) }}x{{ number_format($product->thickness, 0) }} {{ $product->measurement_unit_short }}
                                @elseif($product->length)
                                    {{ number_format($product->length, 2) }} {{ $product->measurement_unit_short }}
                                @else
                                    ไม่ระบุขนาด
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-tools"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">ประเภทเหล็ก</span>
                            <span class="info-box-number">{{ $product->steel_type_text }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">ตำแหน่งเก็บ</span>
                            <span class="info-box-number">{{ $product->location ?: 'ไม่ระบุ' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Info -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-warehouse"></i> สถานะสต็อก
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box mb-3">
                        <span class="info-box-icon {{ $product->total_stock > $product->min_stock ? 'bg-success' : ($product->total_stock > 0 ? 'bg-warning' : 'bg-danger') }}">
                            <i class="fas fa-boxes"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">สต็อกปัจจุบัน</span>
                            <span class="info-box-number">{{ $product->total_stock }} {{ $product->unit }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">สต็อกต่ำสุด</span>
                            <span class="info-box-number">{{ $product->min_stock }} {{ $product->unit }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-arrow-up"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">สต็อกมากสุด</span>
                            <span class="info-box-number">{{ $product->max_stock }} {{ $product->unit }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-secondary">
                            <i class="fas fa-barcode"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">บาร์โค้ด</span>
                            <span class="info-box-number font-monospace">{{ $product->barcode }}</span>
                        </div>
                    </div>

                    <!-- Stock Update Form -->
                    @can('products.edit')
                        <div class="mt-3">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                <strong>หมายเหตุ:</strong> การปรับปรุงสต็อกต้องผ่านระบบคำขอปรับปรุงสต็อก เพื่อให้มีการควบคุมและตรวจสอบที่เหมาะสม
                            </div>
                            <a href="{{ route('admin.stock-adjustments.create', ['product_id' => $product->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle"></i> สร้างคำขอปรับปรุงสต็อก
                            </a>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement History -->
    @if($product->inventoryTransactions && $product->inventoryTransactions->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i> ประวัติการเคลื่อนไหวสต็อก
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
                                    <th>วันที่</th>
                                    <th>รหัสทำรายการ</th>
                                    <th>ประเภท</th>
                                    <th>จำนวน</th>
                                    <th>สต็อกก่อน/หลัง</th>
                                    <th>หมายเหตุ</th>
                                    <th>ผู้ทำรายการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->inventoryTransactions->take(10) as $transaction)
                                    <tr>
                                        <td>{{ $transaction->transaction_date->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <code>{{ $transaction->transaction_code }}</code>
                                        </td>
                                        <td>
                                            @php
                                                $typeClass = match($transaction->type) {
                                                    'in' => 'badge-success',
                                                    'out' => 'badge-danger',
                                                    'adjustment' => 'badge-warning',
                                                    default => 'badge-secondary'
                                                };
                                                $typeText = match($transaction->type) {
                                                    'in' => 'เพิ่มสต็อก',
                                                    'out' => 'ลดสต็อก',
                                                    'adjustment' => 'ปรับปรุง',
                                                    default => $transaction->type
                                                };
                                            @endphp
                                            <span class="badge {{ $typeClass }}">{{ $typeText }}</span>
                                        </td>
                                        <td>
                                            <span class="{{ $transaction->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->quantity >= 0 ? '+' : '' }}{{ $transaction->quantity }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $transaction->before_quantity }} → {{ $transaction->after_quantity }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $transaction->notes ?: '-' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $transaction->user->name ?? '-' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($product->inventoryTransactions->count() > 10)
                            <div class="card-footer text-center">
                                <small class="text-muted">แสดง 10 รายการล่าสุด จากทั้งหมด {{ $product->inventoryTransactions->count() }} รายการ</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Form -->
    @can('products.delete')
        <form id="delete-form" action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan
@stop

@section('css')
    <style>
        .info-box-number {
            font-size: 18px;
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
        .img-thumbnail {
            border: 1px solid #dee2e6;
        }
    </style>
@stop

@section('js')
    <script>
        function confirmDelete() {
            Swal.fire({
                title: 'ยืนยันการลบ',
                html: `
                    <div class="text-left">
                        <p>คุณต้องการลบสินค้า <strong>"{{ $product->name }}"</strong> หรือไม่?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>คำเตือน:</strong> การลบสินค้าจะส่งผลต่อ:
                            <ul class="mt-2 mb-0">
                                <li>ประวัติการทำรายการทั้งหมด</li>
                                <li>รายงานสต็อกและการเงิน</li>
                                <li>ข้อมูลในคลังสินค้า</li>
                            </ul>
                            @if($product->total_stock > 0)
                                <p class="mb-2">
                                    <strong>สต็อกปัจจุบัน:</strong> {{ $product->total_stock }} {{ $product->unit }}
                                </div>
                            @endif
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form').submit();
                }
            });
        }

        function printBarcode() {
            // สร้าง popup สำหรับพิมพ์บาร์โค้ด
            var printWindow = window.open('', '_blank', 'width=400,height=300');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Barcode - {{ $product->name }}</title>
                    <style>
                        body { 
                            font-family: Arial, sans-serif; 
                            text-align: center; 
                            padding: 20px; 
                        }
                        .barcode {
                            font-family: 'Courier New', monospace;
                            font-size: 24px;
                            letter-spacing: 2px;
                            margin: 20px 0;
                        }
                        .product-name {
                            font-size: 14px;
                            margin-top: 10px;
                        }
                        .product-info {
                            font-size: 12px;
                            margin-top: 5px;
                            color: #666;
                        }
                    </style>
                </head>
                <body>
                    <div class="barcode">{{ $product->barcode }}</div>
                    <div class="product-name">{{ $product->name }}</div>
                    <div class="product-info">{{ $product->category->name ?? '' }} | {{ $product->unit }}</div>
                    <script>
                        window.print();
                        window.onafterprint = function() {
                            window.close();
                        }
                    </script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
@stop