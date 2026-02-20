@extends('adminlte::page')

@section('title', 'พิมพ์ Label Barcode')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>พิมพ์ Label Barcode</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">พิมพ์ Label Barcode</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-print"></i> เลือกสินค้าที่ต้องการพิมพ์ Label
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.barcode-labels.verify') }}" class="btn btn-success btn-sm mr-1">
                            <i class="fas fa-barcode"></i> สแกนยืนยัน
                        </a>
                        <a href="{{ route('admin.barcode-labels.history') }}" class="btn btn-info btn-sm mr-1">
                            <i class="fas fa-history"></i> ประวัติ
                        </a>
                        <a href="{{ route('admin.barcode-labels.docs') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-book"></i> คู่มือ
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- สรุปสั้นๆ + ค้นหา --}}
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text"
                                       class="form-control"
                                       id="productSearch"
                                       placeholder="ค้นหาสินค้า (ชื่อ, รหัส, barcode)">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 text-right text-sm text-muted">
                            <span class="mr-3"><i class="fas fa-boxes text-primary"></i> สินค้า <strong>{{ $products->count() }}</strong></span>
                            <span class="mr-3"><i class="fas fa-barcode text-success"></i> StockItems <strong>{{ $products->sum(fn($p) => $p->stockItems->count()) }}</strong></span>
                            <span class="mr-3"><i class="fas fa-print text-warning"></i> พิมพ์แล้ว <strong>{{ $products->sum(fn($p) => $p->stockItems->whereNotNull('label_printed_at')->count()) }}</strong></span>
                            @if($todayStats['unverified'] > 0)
                                <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> รอยืนยัน <strong>{{ $todayStats['unverified'] }}</strong></span>
                            @endif
                        </div>
                    </div>

                    <div class="row" id="productGrid">
                        @forelse($products as $product)
                            <div class="col-md-4 col-lg-3 mb-3 product-card" 
                                 data-name="{{ strtolower($product->name) }}" 
                                 data-sku="{{ strtolower($product->sku) }}" 
                                 data-barcode="{{ strtolower($product->barcode) }}">
                                <div class="card h-100 card-outline card-primary">
                                    <div class="card-header text-center">
                                        <h5 class="card-title mb-0">{{ $product->full_name }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            @if($product->images && count($product->images) > 0)
                                                <img src="{{ asset('storage/' . $product->images[0]) }}" 
                                                     alt="{{ $product->name }}" 
                                                     class="img-fluid rounded"
                                                     style="max-height: 120px; width: auto;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="height: 120px;">
                                                    <i class="fas fa-box fa-3x text-muted"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="text-sm">
                                            <div class="mb-1">
                                                <strong>รหัส:</strong> {{ $product->sku }}
                                            </div>
                                            <div class="mb-1">
                                                <strong>Barcode:</strong> 
                                                <code>{{ $product->barcode }}</code>
                                            </div>
                                            <div class="mb-1">
                                                <strong>หมวดหมู่:</strong> 
                                                @if($product->category)
                                                    <span class="badge" style="background-color: {{ $product->category->color }}; color: {{ $product->category->getTextColor() }};">
                                                        {{ $product->category->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">ไม่ระบุ</span>
                                                @endif
                                            </div>
                                            <div class="mb-2">
                                                <strong>รายการพิมพ์ได้:</strong> 
                                                <span class="badge badge-success">
                                                    {{ $product->stockItems->count() }} รายการ
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center">
                                        <a href="{{ route('admin.barcode-labels.show', $product) }}" 
                                           class="btn btn-primary btn-block">
                                            <i class="fas fa-print"></i> เลือกรายการที่จะพิมพ์
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-box-open fa-4x mb-3"></i>
                                    <h4>ไม่มีสินค้าที่มี StockItem พร้อมพิมพ์</h4>
                                    <p>ต้องมีการสั่งผลิตก่อน เพื่อสร้าง StockItem ที่มี Barcode แยกแต่ละชิ้น</p>
                                    <div class="mt-3">
                                        <a href="{{ route('admin.production-orders.index') }}" class="btn btn-primary me-2">
                                            <i class="fas fa-industry"></i> ไปสั่งผลิตสินค้า
                                        </a>
                                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-box"></i> จัดการสินค้า
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

   
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // ค้นหาสินค้า
            $('#productSearch').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                
                $('.product-card').each(function() {
                    const card = $(this);
                    const name = card.data('name');
                    const sku = card.data('sku');
                    const barcode = card.data('barcode');
                    
                    if (name.includes(searchTerm) || 
                        sku.includes(searchTerm) || 
                        barcode.includes(searchTerm)) {
                        card.show();
                    } else {
                        card.hide();
                    }
                });
                
                // แสดงข้อความหากไม่พบ
                const visibleCards = $('.product-card:visible').length;
                if (visibleCards === 0 && searchTerm.length > 0) {
                    if ($('#no-results').length === 0) {
                        $('#productGrid').append(`
                            <div id="no-results" class="col-12">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <h5>ไม่พบสินค้าที่ค้นหา</h5>
                                    <p>ลองใช้คำค้นหาอื่น หรือตรวจสอบการสะกดคำ</p>
                                </div>
                            </div>
                        `);
                    }
                } else {
                    $('#no-results').remove();
                }
            });
        });
    </script>
@stop