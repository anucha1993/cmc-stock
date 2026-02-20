@extends('adminlte::page')

@section('title', 'Dashboard - CMC Stock')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>แดชบอร์ด</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">หน้าหลัก</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Alert Messages --}}
    @if(($expiredItems ?? 0) > 0)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>สินค้าหมดอายุ!</strong> มีสต็อกสินค้าที่หมดอายุแล้ว <strong>{{ $expiredItems }}</strong> รายการ
        <a href="{{ route('admin.stock-items.index') }}" class="alert-link ml-2">ดูรายละเอียด &raquo;</a>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <strong>สต็อกต่ำ!</strong> มีสินค้า <strong>{{ $lowStockProducts->count() }}</strong> รายการที่สต็อกต่ำกว่าขั้นต่ำ
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    {{-- สถิติหลัก --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalProducts ?? 0 }}</h3>
                    <p>สินค้าทั้งหมด</p>
                </div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
                <a href="{{ route('admin.products.index') }}" class="small-box-footer">ดูทั้งหมด <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalStockItems ?? 0 }}</h3>
                    <p>สต็อกพร้อมใช้งาน</p>
                </div>
                <div class="icon"><i class="fas fa-cubes"></i></div>
                <a href="{{ route('admin.stock-items.index') }}" class="small-box-footer">ดูทั้งหมด <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalPendingApprovals ?? 0 }}</h3>
                    <p>รออนุมัติ</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <a href="#" class="small-box-footer">รายละเอียดด้านล่าง <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalWarehouses ?? 0 }}</h3>
                    <p>คลังสินค้า</p>
                </div>
                <div class="icon"><i class="fas fa-warehouse"></i></div>
                <a href="{{ route('admin.warehouses.index') }}" class="small-box-footer">ดูทั้งหมด <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- งานรออนุมัติ --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">งานรออนุมัติ</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <a href="{{ route('admin.delivery-notes.index') }}" class="btn btn-outline-primary btn-block mb-2">
                                <i class="fas fa-file-invoice"></i> ใบตัดสต็อก
                                <span class="badge badge-{{ ($pendingDeliveryNotes ?? 0) > 0 ? 'danger' : 'secondary' }}">{{ $pendingDeliveryNotes ?? 0 }}</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="{{ route('admin.transfers.index') }}" class="btn btn-outline-success btn-block mb-2">
                                <i class="fas fa-exchange-alt"></i> โอนย้าย
                                <span class="badge badge-{{ ($pendingTransfers ?? 0) > 0 ? 'danger' : 'secondary' }}">{{ $pendingTransfers ?? 0 }}</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-outline-warning btn-block mb-2">
                                <i class="fas fa-clipboard-list"></i> ปรับสต็อก
                                <span class="badge badge-{{ ($pendingAdjustments ?? 0) > 0 ? 'danger' : 'secondary' }}">{{ $pendingAdjustments ?? 0 }}</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="{{ route('admin.claims.index') }}" class="btn btn-outline-danger btn-block mb-2">
                                <i class="fas fa-shield-alt"></i> เคลม
                                <span class="badge badge-{{ ($pendingClaims ?? 0) > 0 ? 'danger' : 'secondary' }}">{{ $pendingClaims ?? 0 }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ทางลัด --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ทางลัด</h3>
                </div>
                <div class="card-body p-2">
                    <a href="{{ route('admin.delivery-notes.create') }}" class="btn btn-primary btn-sm btn-block mb-1">
                        <i class="fas fa-plus"></i> สร้างใบตัดสต็อก
                    </a>
                    <a href="{{ route('admin.transfers.create') }}" class="btn btn-success btn-sm btn-block mb-1">
                        <i class="fas fa-exchange-alt"></i> สร้างใบโอนย้าย
                    </a>
                    <a href="{{ route('admin.claims.create') }}" class="btn btn-warning btn-sm btn-block mb-1">
                        <i class="fas fa-shield-alt"></i> สร้างใบเคลม
                    </a>
                    <a href="{{ route('admin.stock-checks.create') }}" class="btn btn-info btn-sm btn-block mb-1">
                        <i class="fas fa-clipboard-check"></i> ตรวจนับสต็อก
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ใบตัดสต็อกล่าสุด --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ใบตัดสต็อกล่าสุด</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.delivery-notes.index') }}" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>เลขที่</th>
                                <th>ลูกค้า</th>
                                <th class="text-center">สถานะ</th>
                                <th>วันที่</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDeliveryNotes ?? [] as $dn)
                            <tr>
                                <td><a href="{{ route('admin.delivery-notes.show', $dn) }}">{{ $dn->delivery_number }}</a></td>
                                <td>{{ $dn->customer_name }}</td>
                                <td class="text-center">
                                    @php
                                        $dnColor = match($dn->status) {
                                            'pending' => 'secondary', 'confirmed' => 'info',
                                            'scanned' => 'warning', 'completed' => 'success',
                                            default => 'secondary'
                                        };
                                        $dnText = match($dn->status) {
                                            'pending' => 'รอยืนยัน', 'confirmed' => 'ยืนยันแล้ว',
                                            'scanned' => 'สแกนแล้ว', 'completed' => 'เสร็จ',
                                            default => $dn->status
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $dnColor }}">{{ $dnText }}</span>
                                </td>
                                <td><small>{{ $dn->created_at->format('d/m H:i') }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">ยังไม่มีรายการ</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- การโอนย้ายล่าสุด --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">การโอนย้ายล่าสุด</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.transfers.index') }}" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>รหัส</th>
                                <th>สินค้า</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransfers ?? [] as $tr)
                            <tr>
                                <td><a href="{{ route('admin.transfers.show', $tr) }}">{{ $tr->transfer_code }}</a></td>
                                <td>{{ $tr->product->full_name ?? '-' }}</td>
                                <td class="text-center">{{ $tr->quantity }}</td>
                                <td class="text-center">
                                    @php
                                        $trColor = match($tr->status) {
                                            'pending' => 'warning', 'in_transit' => 'info',
                                            'completed' => 'success', 'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                        $trText = match($tr->status) {
                                            'pending' => 'รอ', 'in_transit' => 'ขนส่ง',
                                            'completed' => 'เสร็จ', 'cancelled' => 'ยกเลิก',
                                            default => $tr->status
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $trColor }}">{{ $trText }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">ยังไม่มีรายการ</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- สินค้าสต็อกต่ำ --}}
    @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title">สินค้าสต็อกต่ำ</h3>
            <div class="card-tools">
                <span class="badge badge-danger">{{ $lowStockProducts->count() }} รายการ</span>
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>สินค้า</th>
                        <th>หมวดหมู่</th>
                        <th class="text-center">คงเหลือ</th>
                        <th class="text-center">ขั้นต่ำ</th>
                        <th class="text-center">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $product)
                    <tr>
                        <td><a href="{{ route('admin.products.show', $product) }}">{{ $product->full_name }}</a></td>
                        <td>{{ $product->category->name ?? '-' }}</td>
                        <td class="text-center">
                            <strong class="{{ $product->stock_quantity <= 0 ? 'text-danger' : 'text-warning' }}">{{ $product->stock_quantity }}</strong>
                        </td>
                        <td class="text-center">{{ $product->min_stock }}</td>
                        <td class="text-center">
                            @if($product->stock_quantity <= 0)
                                <span class="badge badge-danger">หมด</span>
                            @else
                                <span class="badge badge-warning">ต่ำ</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@stop

@section('js')
<script>
    setTimeout(function() { $('.alert-dismissible').fadeOut('slow'); }, 5000);
</script>
@stop
