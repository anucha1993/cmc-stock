@extends('adminlte::page')

@section('title', 'ตรวจนับสต๊อก - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="mb-0"><i class="fas fa-clipboard-check text-primary"></i> ตรวจนับสต๊อก</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right d-none d-sm-flex">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item active">ตรวจนับสต๊อก</li>
            </ol>
        </div>
    </div>
@stop

@section('css')
<style>
    /* ===== Shared ===== */
    .status-badge { font-size: .75rem; font-weight: 600; padding: 3px 10px; border-radius: 10px; display: inline-block; }
    .status-active  { background: #d4edda; color: #155724; }
    .status-completed { background: #cce5ff; color: #004085; }
    .status-cancelled { background: #f8d7da; color: #721c24; }

    .filter-bar { background: #fff; border: 1px solid #dee2e6; border-radius: .5rem; padding: .75rem 1rem; margin-bottom: 1rem; }
    .filter-bar .form-control { font-size: .85rem; }

    .action-btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; font-size: .8rem; border: 1px solid #dee2e6; background: #fff; color: #555; transition: all .15s; cursor: pointer; }
    .action-btn:hover { background: #f0f0f0; color: #333; text-decoration: none; }
    .action-btn.btn-scan { background: #28a745; color: #fff; border-color: #28a745; }
    .action-btn.btn-scan:hover { background: #218838; color: #fff; }
    .action-btn.btn-report { background: #007bff; color: #fff; border-color: #007bff; }
    .action-btn.btn-report:hover { background: #0069d9; color: #fff; }
    .action-btn.btn-del { color: #dc3545; }
    .action-btn.btn-del:hover { background: #dc3545; color: #fff; }

    /* ===== Desktop Table (lg+) ===== */
    .desktop-table th { font-size: .78rem; font-weight: 600; color: #666; text-transform: uppercase; letter-spacing: .3px; white-space: nowrap; border-bottom: 2px solid #dee2e6; }
    .desktop-table td { vertical-align: middle; font-size: .87rem; }
    .desktop-table tbody tr { transition: background .15s; cursor: pointer; }
    .desktop-table tbody tr:hover { background: #f8f9fa; }
    .session-code { font-weight: 700; color: #007bff; font-size: .84rem; }
    .session-title { font-weight: 600; font-size: .88rem; color: #333; }
    .session-desc { font-size: .78rem; color: #888; margin-top: 1px; }
    .meta-text { font-size: .82rem; color: #666; }

    /* ===== Mobile Cards (< lg) ===== */
    .mobile-card { border-left: 4px solid #007bff; border-radius: .5rem; transition: all .15s; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .mobile-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.1); }
    .mobile-card.border-active { border-left-color: #28a745; }
    .mobile-card.border-completed { border-left-color: #007bff; }
    .mobile-card.border-cancelled { border-left-color: #dc3545; }
    .mc-code { font-weight: 700; color: #007bff; font-size: .84rem; }
    .mc-title { font-weight: 600; font-size: .95rem; margin-top: 2px; }
    .mc-meta { font-size: .8rem; color: #777; }
    .mc-meta i { width: 16px; text-align: center; }
    .mc-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .mc-actions .action-btn { width: auto; height: 36px; padding: 0 12px; font-size: .82rem; display: inline-flex; align-items: center; gap: 4px; }

    /* ===== Responsive Toggle ===== */
    .desktop-view { display: none; }
    .mobile-view { display: block; }
    @media (min-width: 992px) {
        .desktop-view { display: block; }
        .mobile-view { display: none; }
    }

    /* ===== Mobile touch ===== */
    @media (max-width: 991.98px) {
        .filter-bar .form-control { height: 44px; font-size: 16px; }
        .filter-bar .btn { height: 44px; }
    }
</style>
@stop

@section('content')
    {{-- Alerts --}}
    @foreach(['success' => 'check-circle', 'error' => 'exclamation-triangle'] as $type => $icon)
        @if(session($type))
            <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show">
                <i class="fas fa-{{ $icon }}"></i> {!! session($type) !!}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif
    @endforeach

    {{-- Filter Bar --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.stock-checks.index') }}">
            <div class="row align-items-end">
                <div class="col-6 col-lg-3 mb-2 mb-lg-0">
                    <label class="mb-1 small font-weight-bold">สถานะ</label>
                    <select name="status" class="form-control">
                        <option value="">ทั้งหมด</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                </div>
                <div class="col-6 col-lg-3 mb-2 mb-lg-0">
                    <label class="mb-1 small font-weight-bold">คลัง</label>
                    <select name="warehouse_id" class="form-control">
                        <option value="">ทั้งหมด</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2 mb-2 mb-lg-0">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                </div>
                @can('create-edit')
                <div class="col-6 col-lg-2 mb-2 mb-lg-0 ml-lg-auto">
                    <a href="{{ route('admin.stock-checks.create') }}" class="btn btn-success btn-block">
                        <i class="fas fa-plus"></i> <span class="d-none d-md-inline">เริ่ม</span>ตรวจนับใหม่
                    </a>
                </div>
                @endcan
            </div>
        </form>
    </div>

    @if($sessions->count() > 0)
        {{-- ===== Desktop Table View (lg+) ===== --}}
        <div class="desktop-view">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover desktop-table mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>รหัส</th>
                                    <th>ชื่อรายการ</th>
                                    <th>คลัง</th>
                                    <th>หมวด</th>
                                    <th class="text-center">สถานะ</th>
                                    <th>เริ่ม</th>
                                    <th>เสร็จ</th>
                                    <th>ผู้สร้าง</th>
                                    <th class="text-center" style="width:140px">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sessions as $session)
                                <tr onclick="window.location='{{ route('admin.stock-checks.show', $session) }}'">
                                    <td><span class="session-code">{{ $session->session_code }}</span></td>
                                    <td>
                                        <div class="session-title">{{ $session->title }}</div>
                                        @if($session->description)
                                            <div class="session-desc">{{ Str::limit($session->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td class="meta-text">{{ optional($session->warehouse)->name ?? '-' }}</td>
                                    <td class="meta-text">{{ $session->category->name ?? 'ทั้งหมด' }}</td>
                                    <td class="text-center">
                                        @switch($session->status)
                                            @case('active')
                                                <span class="status-badge status-active">กำลังดำเนินการ</span>
                                                @break
                                            @case('completed')
                                                <span class="status-badge status-completed">เสร็จสิ้น</span>
                                                @break
                                            @case('cancelled')
                                                <span class="status-badge status-cancelled">ยกเลิก</span>
                                                @break
                                            @default
                                                <span class="status-badge">{{ $session->status }}</span>
                                        @endswitch
                                    </td>
                                    <td class="meta-text">{{ $session->started_at ? $session->started_at->format('d/m/y H:i') : '-' }}</td>
                                    <td class="meta-text">{{ $session->completed_at ? $session->completed_at->format('d/m/y H:i') : '-' }}</td>
                                    <td class="meta-text">{{ Str::limit(optional($session->creator)->name ?? '-', 15) }}</td>
                                    <td class="text-center" onclick="event.stopPropagation()">
                                        <div class="d-inline-flex" style="gap:4px">
                                            <a href="{{ route('admin.stock-checks.show', $session) }}" class="action-btn" title="ดูรายละเอียด"><i class="fas fa-eye"></i></a>
                                            @if($session->status === 'active')
                                                <a href="{{ route('admin.stock-checks.scan', $session) }}" class="action-btn btn-scan" title="สแกน"><i class="fas fa-barcode"></i></a>
                                            @endif
                                            @if($session->status === 'completed')
                                                <a href="{{ route('admin.stock-checks.report', $session) }}" class="action-btn btn-report" title="รายงาน"><i class="fas fa-chart-bar"></i></a>
                                            @endif
                                            @can('create-edit')
                                                <a href="{{ route('admin.stock-checks.edit', $session) }}" class="action-btn" title="แก้ไข"><i class="fas fa-edit text-warning"></i></a>
                                            @endcan
                                            @can('delete')
                                                <button class="action-btn btn-del" title="ลบ" onclick="confirmDelete('{{ $session->id }}', '{{ addslashes($session->title) }}')"><i class="fas fa-trash"></i></button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Mobile Card View (< lg) ===== --}}
        <div class="mobile-view">
            @foreach($sessions as $session)
            <div class="card mobile-card mb-3 border-{{ $session->status }}" onclick="window.location='{{ route('admin.stock-checks.show', $session) }}'">
                <div class="card-body p-3">
                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center flex-wrap" style="gap:6px">
                                <span class="mc-code">{{ $session->session_code }}</span>
                                @switch($session->status)
                                    @case('active')
                                        <span class="status-badge status-active">ดำเนินการ</span>
                                        @break
                                    @case('completed')
                                        <span class="status-badge status-completed">เสร็จสิ้น</span>
                                        @break
                                    @case('cancelled')
                                        <span class="status-badge status-cancelled">ยกเลิก</span>
                                        @break
                                @endswitch
                            </div>
                            <div class="mc-title">{{ $session->title }}</div>
                            @if($session->description)
                                <div class="mc-meta mt-1">{{ Str::limit($session->description, 80) }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Meta --}}
                    <div class="row mb-2">
                        <div class="col-6 mc-meta mb-1"><i class="fas fa-warehouse"></i> {{ Str::limit(optional($session->warehouse)->name ?? '-', 18) }}</div>
                        <div class="col-6 mc-meta mb-1"><i class="fas fa-tags"></i> {{ Str::limit($session->category->name ?? 'ทั้งหมด', 15) }}</div>
                        <div class="col-6 mc-meta"><i class="fas fa-clock"></i> {{ $session->started_at ? $session->started_at->format('d/m H:i') : '-' }}</div>
                        <div class="col-6 mc-meta"><i class="fas fa-user"></i> {{ Str::limit(optional($session->creator)->name ?? '-', 12) }}</div>
                        @if($session->completed_at)
                        <div class="col-12 mc-meta mt-1 text-success"><i class="fas fa-check-circle"></i> เสร็จ {{ $session->completed_at->format('d/m H:i') }}</div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="mc-actions" onclick="event.stopPropagation()">
                        <a href="{{ route('admin.stock-checks.show', $session) }}" class="action-btn"><i class="fas fa-eye"></i> ดู</a>
                        @if($session->status === 'active')
                            <a href="{{ route('admin.stock-checks.scan', $session) }}" class="action-btn btn-scan"><i class="fas fa-barcode"></i> สแกน</a>
                        @endif
                        @if($session->status === 'completed')
                            <a href="{{ route('admin.stock-checks.report', $session) }}" class="action-btn btn-report"><i class="fas fa-chart-bar"></i> รายงาน</a>
                        @endif
                        @can('create-edit')
                            <a href="{{ route('admin.stock-checks.edit', $session) }}" class="action-btn" title="แก้ไข"><i class="fas fa-edit text-warning"></i></a>
                        @endcan
                        @can('delete')
                            <button class="action-btn btn-del" onclick="confirmDelete('{{ $session->id }}', '{{ addslashes($session->title) }}')"><i class="fas fa-trash"></i></button>
                        @endcan
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $sessions->withQueryString()->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-check fa-4x text-muted mb-3"></i>
                <h5 class="text-muted mb-2">ไม่พบข้อมูลการตรวจนับสต๊อก</h5>
                <p class="text-muted mb-4">เริ่มต้นการตรวจนับสต๊อกครั้งแรกของคุณ</p>
                @can('create-edit')
                <a href="{{ route('admin.stock-checks.create') }}" class="btn btn-success btn-lg">
                    <i class="fas fa-plus"></i> เริ่มตรวจนับใหม่
                </a>
                @endcan
            </div>
        </div>
    @endif
@stop

@section('js')
<script>
function confirmDelete(id, title) {
    Swal.fire({
        title: 'ลบรายการตรวจนับ?',
        html: '<p class="mb-1">ต้องการลบ <strong>' + title + '</strong> หรือไม่?</p><p class="text-muted small mb-0">ข้อมูลการสแกนทั้งหมดจะถูกลบด้วย</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-trash"></i> ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then(r => {
        if (r.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/stock-checks/' + id;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function shareSession(code) {
    if (navigator.share) {
        navigator.share({ title: 'ตรวจนับสต๊อก', text: 'รหัส: ' + code, url: window.location.href });
    } else {
        navigator.clipboard.writeText(code).then(function() {
            Swal.fire({ icon:'success', title:'คัดลอกรหัสแล้ว', text: code, timer: 1500, showConfirmButton: false });
        });
    }
}
</script>
@stop
