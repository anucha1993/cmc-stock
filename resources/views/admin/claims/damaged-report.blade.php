@extends('adminlte::page')

@section('title', 'รายงานสินค้าชำรุด - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-exclamation-triangle text-warning"></i> รายงานสินค้าชำรุดจากเคลม</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.claims.index') }}">การเคลมสินค้า</a></li>
                <li class="breadcrumb-item active">รายงานสินค้าชำรุด</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Stats --}}
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['total_damaged'] ?? 0 }}</h3>
                    <p>ยืนยันชำรุด</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['repairable'] ?? 0 }}</h3>
                    <p>ซ่อมได้</p>
                </div>
                <div class="icon"><i class="fas fa-wrench"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h3>{{ $stats['unrepairable'] ?? 0 }}</h3>
                    <p>ซ่อมไม่ได้</p>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['scrapped'] ?? 0 }}</h3>
                    <p>ทำลายแล้ว</p>
                </div>
                <div class="icon"><i class="fas fa-trash"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['returned_supplier'] ?? 0 }}</h3>
                    <p>คืนผู้จำหน่าย</p>
                </div>
                <div class="icon"><i class="fas fa-undo"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['returned_stock'] ?? 0 }}</h3>
                    <p>คืนเข้าสต็อก</p>
                </div>
                <div class="icon"><i class="fas fa-box"></i></div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> กรองข้อมูล</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.claims.damaged-report') }}">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>สถานะชำรุด</label>
                            <select name="damaged_status" class="form-control form-control-sm">
                                <option value="">-- ทั้งหมด --</option>
                                <option value="confirmed_damaged" {{ request('damaged_status') == 'confirmed_damaged' ? 'selected' : '' }}>ยืนยันชำรุด</option>
                                <option value="repairable" {{ request('damaged_status') == 'repairable' ? 'selected' : '' }}>ซ่อมได้</option>
                                <option value="unrepairable" {{ request('damaged_status') == 'unrepairable' ? 'selected' : '' }}>ซ่อมไม่ได้</option>
                                <option value="scrapped" {{ request('damaged_status') == 'scrapped' ? 'selected' : '' }}>ทำลายแล้ว</option>
                                <option value="returned_to_supplier" {{ request('damaged_status') == 'returned_to_supplier' ? 'selected' : '' }}>คืนผู้จำหน่าย</option>
                                <option value="returned_to_stock" {{ request('damaged_status') == 'returned_to_stock' ? 'selected' : '' }}>คืนเข้าสต็อก</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>การดำเนินการ</label>
                            <select name="action_taken" class="form-control form-control-sm">
                                <option value="">-- ทั้งหมด --</option>
                                <option value="none" {{ request('action_taken') == 'none' ? 'selected' : '' }}>ยังไม่ดำเนินการ</option>
                                <option value="replaced" {{ request('action_taken') == 'replaced' ? 'selected' : '' }}>เปลี่ยนแล้ว</option>
                                <option value="repaired" {{ request('action_taken') == 'repaired' ? 'selected' : '' }}>ซ่อมแล้ว</option>
                                <option value="scrapped" {{ request('action_taken') == 'scrapped' ? 'selected' : '' }}>ทำลายแล้ว</option>
                                <option value="returned" {{ request('action_taken') == 'returned' ? 'selected' : '' }}>ส่งคืนแล้ว</option>
                                <option value="restocked" {{ request('action_taken') == 'restocked' ? 'selected' : '' }}>คืนเข้าสต็อก</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>สินค้า</label>
                            <select name="product_id" class="form-control form-control-sm">
                                <option value="">-- สินค้าทั้งหมด --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>วันที่เริ่ม</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ถึงวันที่</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Damaged Items Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> รายการสินค้าชำรุด</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ใบเคลม</th>
                        <th>สินค้า</th>
                        <th>Barcode</th>
                        <th>จำนวน</th>
                        <th>สาเหตุ</th>
                        <th>สถานะชำรุด</th>
                        <th>การดำเนินการ</th>
                        <th>ผู้ตรวจ</th>
                        <th>วันที่ตรวจ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($damagedItems as $idx => $item)
                        <tr>
                            <td>{{ $damagedItems->firstItem() + $idx }}</td>
                            <td>
                                @if($item->claim)
                                    <a href="{{ route('admin.claims.show', $item->claim) }}">
                                        {{ $item->claim->claim_number }}
                                    </a>
                                    <br><small class="text-muted">{{ $item->claim->claim_date?->format('d/m/Y') }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <strong>{{ $item->product->full_name ?? 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $item->product->sku ?? '' }}</small>
                            </td>
                            <td>
                                @if($item->stockItem)
                                    <code>{{ $item->stockItem->barcode }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td><span class="badge badge-secondary">{{ $item->reason_text }}</span></td>
                            <td>
                                <span class="badge badge-{{ $item->damaged_status_color }}">
                                    {{ $item->damaged_status_text }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $item->action_taken_color }}">
                                    {{ $item->action_taken_text }}
                                </span>
                            </td>
                            <td>{{ $item->inspector->name ?? '-' }}</td>
                            <td>{{ $item->inspected_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">ไม่มีรายการสินค้าชำรุด</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($damagedItems->hasPages())
            <div class="card-footer">
                {{ $damagedItems->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.claims.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> กลับไปรายการเคลม
        </a>
    </div>
@stop
