@extends('adminlte::page')

@section('title', 'ประวัติการพิมพ์ Label - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-history"></i> ประวัติการพิมพ์ Label</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.barcode-labels.index') }}">พิมพ์ Label Barcode</a></li>
                <li class="breadcrumb-item active">ประวัติการพิมพ์</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Filter -->
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> กรองข้อมูล</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>จากวันที่</label>
                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ถึงวันที่</label>
                            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ผู้พิมพ์</label>
                            <select class="form-control" name="printed_by">
                                <option value="">ทั้งหมด</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('printed_by') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ตัวกรองพิเศษ</label>
                            <div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="reprint_only" value="1" {{ request('reprint_only') ? 'checked' : '' }}>
                                    <label class="form-check-label">เฉพาะพิมพ์ซ้ำ</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="unverified_only" value="1" {{ request('unverified_only') ? 'checked' : '' }}>
                                    <label class="form-check-label">เฉพาะยังไม่ยืนยัน</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> กรอง</button>
                                <a href="{{ route('admin.barcode-labels.history') }}" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการทั้งหมด ({{ $logs->total() }})</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>วันเวลา</th>
                            <th>ประเภท</th>
                            <th>บาร์โค้ด</th>
                            <th>สินค้า</th>
                            <th>ขนาด</th>
                            <th>จำนวน</th>
                            <th>ผู้พิมพ์</th>
                            <th>พิมพ์ซ้ำ</th>
                            <th>สถานะยืนยัน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="{{ $log->is_reprint ? 'table-warning' : '' }}">
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($log->print_type === 'stock_item')
                                    <span class="badge badge-primary">รายชิ้น</span>
                                @else
                                    <span class="badge badge-info">ระดับสินค้า</span>
                                @endif
                            </td>
                            <td><code>{{ $log->barcode }}</code></td>
                            <td>
                                @if($log->print_type === 'stock_item' && $log->stockItem?->product)
                                    {{ $log->stockItem->product->full_name }}
                                @elseif($log->product)
                                    {{ $log->product->full_name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $log->label_size_text }}</td>
                            <td>{{ $log->copies }} ใบ</td>
                            <td>{{ $log->printer->name ?? '-' }}</td>
                            <td>
                                @if($log->is_reprint)
                                    <span class="badge badge-warning"><i class="fas fa-redo"></i> พิมพ์ซ้ำ</span>
                                    @if($log->reason)
                                        <br><small class="text-muted" title="{{ $log->reason }}">{{ Str::limit($log->reason, 30) }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-success">ครั้งแรก</span>
                                @endif
                            </td>
                            <td>
                                @if($log->print_type === 'product')
                                    <span class="text-muted">-</span>
                                @elseif($log->verified)
                                    <span class="badge badge-success"><i class="fas fa-check-double"></i> ยืนยันแล้ว</span>
                                    <br><small>{{ $log->verified_at?->format('d/m H:i') }} โดย {{ $log->verifier?->name ?? '-' }}</small>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-clock"></i> รอยืนยัน</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-2"></i><br>
                                ไม่มีประวัติการพิมพ์
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer">
            {{ $logs->appends(request()->all())->links() }}
        </div>
        @endif
    </div>
@stop
