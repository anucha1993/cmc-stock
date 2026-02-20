@extends('adminlte::page')

@section('title', 'ใบตัดสต็อก {{ $deliveryNote->delivery_number }} - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-invoice text-primary"></i> {{ $deliveryNote->delivery_number }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.delivery-notes.index') }}">ใบตัดสต็อก</a></li>
                <li class="breadcrumb-item active">{{ $deliveryNote->delivery_number }}</li>
            </ol>
        </div>
    </div>
@stop

@section('css')
<style>
    .info-label { font-size: .78rem; color: #888; margin-bottom: 1px; }
    .info-value { font-weight: 600; font-size: .92rem; }
    .result-banner { border-radius: .5rem; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; }
    .result-banner .icon { font-size: 2rem; flex-shrink: 0; }
    .result-banner h5 { margin: 0; font-size: 1rem; }
    .result-banner p { margin: 0; font-size: .85rem; opacity: .85; }
    .qty-box { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 28px; border-radius: 6px; font-weight: 700; font-size: .85rem; padding: 0 8px; }
    .qty-planned { background: #e3f2fd; color: #1565c0; }
    .qty-scanned { background: #e8f5e9; color: #2e7d32; }
    .qty-over { background: #fff3e0; color: #e65100; }
    .qty-under { background: #fce4ec; color: #c62828; }
    .diff-badge { font-size: .75rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
    .action-card { border: 2px solid #dee2e6; border-radius: .75rem; padding: 1.25rem; text-align: center; transition: border-color .2s, box-shadow .2s; cursor: pointer; height: 100%; }
    .action-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); }
    .action-card.green:hover { border-color: #28a745; }
    .action-card.yellow:hover { border-color: #ffc107; }
    .action-card.red:hover { border-color: #dc3545; }
    .action-card.blue:hover { border-color: #007bff; }
    .action-card .action-icon { font-size: 2rem; margin-bottom: .5rem; }
    .action-card .action-title { font-weight: 700; font-size: .95rem; }
    .action-card .action-desc { font-size: .8rem; color: #888; margin-top: .25rem; }
    .scan-tag { display: inline-block; background: #e8f5e9; color: #2e7d32; font-size: .7rem; font-weight: 600; padding: 2px 7px; border-radius: 10px; margin: 1px 2px; }
    .tl-item { display: flex; align-items: flex-start; gap: .75rem; padding: .5rem 0; border-bottom: 1px solid #f0f0f0; }
    .tl-item:last-child { border-bottom: none; }
    .tl-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .8rem; color: #fff; flex-shrink: 0; }
    .tl-body { flex: 1; min-width: 0; }
    .tl-title { font-weight: 600; font-size: .85rem; }
    .tl-desc { font-size: .78rem; color: #666; }
    .tl-time { font-size: .72rem; color: #aaa; white-space: nowrap; }
    @media (max-width: 767.98px) {
        .action-card { margin-bottom: .75rem; }
    }
</style>
@stop

@section('content')
    @php
        $totalPlanned = $deliveryNote->items->sum('quantity');
        $totalScanned = $deliveryNote->items->sum('scanned_quantity');
        $hasIssues = !empty($discrepancies);
        $matchCount = $deliveryNote->items->count() - count($discrepancies);
        $hasScanned = $totalScanned > 0;
        $underCount = collect($discrepancies)->where('status', 'under')->count();
        $overCount = collect($discrepancies)->where('status', 'over')->count();
    @endphp

    {{-- Alert Messages --}}
    @foreach(['success' => 'check-circle', 'error' => 'exclamation-triangle', 'warning' => 'exclamation-circle'] as $type => $icon)
        @if(session($type))
            <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show">
                <i class="fas fa-{{ $icon }}"></i> {!! session($type) !!}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif
    @endforeach

    {{-- ===== Row 1: ข้อมูลใบ + สรุปผลสแกน ===== --}}
    <div class="row">
        {{-- ข้อมูลใบตัดสต็อก --}}
        <div class="col-lg-5 mb-3">
            <div class="card card-outline card-primary mb-0 h-100">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-file-invoice"></i> ข้อมูลใบตัดสต็อก</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $deliveryNote->status_color }}">{{ $deliveryNote->status_text }}</span>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <div class="info-label">ลูกค้า</div>
                            <div class="info-value">{{ $deliveryNote->customer_name }}</div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="info-label">วันที่จัดส่ง</div>
                            <div class="info-value">{{ $deliveryNote->delivery_date->format('d/m/Y') }}</div>
                        </div>
                        @if($deliveryNote->customer_phone)
                        <div class="col-6 mb-2">
                            <div class="info-label">เบอร์โทร</div>
                            <div class="info-value">{{ $deliveryNote->customer_phone }}</div>
                        </div>
                        @endif
                        @if($deliveryNote->sales_order_number)
                        <div class="col-6 mb-2">
                            <div class="info-label">ใบสั่งขาย</div>
                            <div class="info-value">{{ $deliveryNote->sales_order_number }}</div>
                        </div>
                        @endif
                        @if($deliveryNote->quotation_number)
                        <div class="col-6 mb-2">
                            <div class="info-label">ใบเสนอราคา</div>
                            <div class="info-value">{{ $deliveryNote->quotation_number }}</div>
                        </div>
                        @endif
                    </div>
                    @if($deliveryNote->notes)
                    <div class="mt-1" style="font-size:.82rem; color:#666;">
                        <i class="fas fa-sticky-note"></i> {{ $deliveryNote->notes }}
                    </div>
                    @endif

                    {{-- Timeline --}}
                    <hr class="my-2">
                    <div class="tl-item">
                        <div class="tl-icon bg-success"><i class="fas fa-file"></i></div>
                        <div class="tl-body">
                            <div class="tl-title">สร้างเอกสาร</div>
                            <div class="tl-desc">{{ $deliveryNote->creator->name ?? '-' }}</div>
                        </div>
                        <div class="tl-time">{{ $deliveryNote->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @if($deliveryNote->confirmed_at)
                    <div class="tl-item">
                        <div class="tl-icon bg-info"><i class="fas fa-check"></i></div>
                        <div class="tl-body">
                            <div class="tl-title">ยืนยันเอกสาร</div>
                            <div class="tl-desc">{{ $deliveryNote->confirmer->name ?? '-' }}</div>
                        </div>
                        <div class="tl-time">{{ $deliveryNote->confirmed_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                    @if($deliveryNote->scanned_at)
                    <div class="tl-item">
                        <div class="tl-icon bg-primary"><i class="fas fa-barcode"></i></div>
                        <div class="tl-body">
                            <div class="tl-title">สแกน Barcode</div>
                            <div class="tl-desc">{{ $deliveryNote->scanner->name ?? 'สแกนผ่านลิงก์สาธารณะ' }} — {{ $totalScanned }}/{{ $totalPlanned }} ชิ้น</div>
                        </div>
                        <div class="tl-time">{{ $deliveryNote->scanned_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                    @if($deliveryNote->approved_at)
                    <div class="tl-item">
                        <div class="tl-icon bg-success"><i class="fas fa-check-double"></i></div>
                        <div class="tl-body">
                            <div class="tl-title">อนุมัติและตัดสต็อก</div>
                            <div class="tl-desc">{{ $deliveryNote->approver->name ?? '-' }}
                                @if($deliveryNote->discrepancy_notes)
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> มีความไม่ตรงกัน</span>
                                @endif
                            </div>
                        </div>
                        <div class="tl-time">{{ $deliveryNote->approved_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- สรุปผลสแกน --}}
        <div class="col-lg-7 mb-3">
            <div class="card mb-0 h-100">
                <div class="card-body d-flex flex-column justify-content-center py-3">
                    @if(!$hasScanned)
                        <div class="result-banner bg-light">
                            <div class="icon text-secondary"><i class="fas fa-clock"></i></div>
                            <div>
                                <h5>ยังไม่มีการสแกน</h5>
                                <p>รอสแกน {{ $totalPlanned }} ชิ้น ({{ $deliveryNote->items->count() }} รายการ)</p>
                            </div>
                        </div>
                    @elseif(!$hasIssues)
                        <div class="result-banner bg-success text-white">
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                            <div>
                                <h5>{{ $deliveryNote->status === 'completed' ? 'ตัดสต็อกเรียบร้อย' : 'ตรงทุกรายการ — พร้อมอนุมัติ' }}</h5>
                                <p>สแกนครบ {{ $totalScanned }}/{{ $totalPlanned }} ชิ้น ({{ $deliveryNote->items->count() }} รายการ)</p>
                            </div>
                        </div>
                    @else
                        <div class="result-banner bg-warning">
                            <div class="icon"><i class="fas fa-boxes"></i></div>
                            <div>
                                @if($underCount > 0 && $overCount === 0)
                                    <h5>สแกนยังไม่ครบ {{ $underCount }} รายการ</h5>
                                    <p>สแกนแล้ว {{ $totalScanned }}/{{ $totalPlanned }} ชิ้น — ยังขาดอีก {{ $totalPlanned - $totalScanned }} ชิ้น (สินค้าถูกต้อง แต่จำนวนยังไม่ครบ)</p>
                                @elseif($overCount > 0 && $underCount === 0)
                                    <h5>สแกนเกินจำนวน {{ $overCount }} รายการ</h5>
                                    <p>สแกนแล้ว {{ $totalScanned }}/{{ $totalPlanned }} ชิ้น — เกินมา {{ $totalScanned - $totalPlanned }} ชิ้น</p>
                                @else
                                    <h5>จำนวนสแกนไม่ตรง {{ count($discrepancies) }} รายการ</h5>
                                    <p>สแกนแล้ว {{ $totalScanned }}/{{ $totalPlanned }} ชิ้น — ยังไม่ครบ {{ $underCount }} รายการ / เกิน {{ $overCount }} รายการ</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-around text-center mt-3">
                        <div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: #1565c0;">{{ $totalPlanned }}</div>
                            <div style="font-size: .75rem; color: #888;">กำหนด</div>
                        </div>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: {{ $totalScanned === $totalPlanned ? '#2e7d32' : '#e65100' }};">{{ $totalScanned }}</div>
                            <div style="font-size: .75rem; color: #888;">สแกนจริง</div>
                        </div>
                        <div>
                            @php $diff = $totalScanned - $totalPlanned; @endphp
                            <div style="font-size: 1.5rem; font-weight: 800; color: {{ $diff === 0 ? '#2e7d32' : ($diff > 0 ? '#e65100' : '#c62828') }};">
                                {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                            </div>
                            <div style="font-size: .75rem; color: #888;">ส่วนต่าง</div>
                        </div>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: {{ $matchCount === $deliveryNote->items->count() ? '#2e7d32' : '#e65100' }};">
                                {{ $matchCount }}/{{ $deliveryNote->items->count() }}
                            </div>
                            <div style="font-size: .75rem; color: #888;">ครบจำนวน</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== รายการสินค้า ===== --}}
    <div class="card">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-list"></i> รายการสินค้า</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width:40px" class="text-center">#</th>
                            <th>สินค้า</th>
                            <th class="text-center" style="width:80px">กำหนด</th>
                            <th class="text-center" style="width:80px">สแกน</th>
                            <th class="text-center" style="width:80px">ส่วนต่าง</th>
                            <th class="text-center" style="width:90px">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveryNote->items as $index => $item)
                        @php
                            $d = $item->scanned_quantity - $item->quantity;
                            $isMatch = $d === 0;
                            $isOver = $d > 0;
                            $isUnder = $d < 0;
                        @endphp
                        <tr class="{{ $isUnder ? 'table-danger' : ($isOver ? 'table-warning' : '') }}">
                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->full_name }}</strong>
                                <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                @if($item->scanned_items && count($item->scanned_items) > 0)
                                    <div class="mt-1">
                                        @foreach($item->scanned_items as $si)
                                            <span class="scan-tag">{{ $si['barcode'] }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-center"><span class="qty-box qty-planned">{{ $item->quantity }}</span></td>
                            <td class="text-center"><span class="qty-box {{ $isMatch ? 'qty-scanned' : ($isOver ? 'qty-over' : 'qty-under') }}">{{ $item->scanned_quantity }}</span></td>
                            <td class="text-center">
                                @if($isMatch)
                                    <span class="diff-badge bg-success text-white"><i class="fas fa-check"></i> ครบ</span>
                                @elseif($isOver)
                                    <span class="diff-badge bg-warning text-dark">เกิน +{{ $d }}</span>
                                @else
                                    <span class="diff-badge bg-danger text-white">ขาด {{ abs($d) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($isMatch)
                                    <span class="text-success"><i class="fas fa-check-circle"></i> ครบ</span>
                                @elseif($isOver)
                                    <span class="text-warning"><i class="fas fa-arrow-up"></i> เกิน {{ $d }}</span>
                                @else
                                    <span class="text-danger"><i class="fas fa-clock"></i> รอสแกนอีก {{ abs($d) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== ปุ่มดำเนินการ ===== --}}
    <div class="row mb-4">
        {{-- กลับ --}}
        <div class="col-md-3 col-6 mb-2">
            <a href="{{ route('admin.delivery-notes.index') }}" class="text-decoration-none">
                <div class="action-card yellow">
                    <div class="action-icon text-secondary"><i class="fas fa-arrow-left"></i></div>
                    <div class="action-title">กลับ</div>
                    <div class="action-desc">กลับไปหน้ารายการใบตัดสต็อก</div>
                </div>
            </a>
        </div>

        {{-- Copy URL สแกน --}}
        @if($deliveryNote->status !== 'completed')
        <div class="col-md-3 col-6 mb-2">
            <div class="action-card blue" id="btn-copy-url" style="cursor:pointer"
                 data-url="{{ route('admin.delivery-notes.share-link', $deliveryNote->id) }}">
                <div class="action-icon text-primary"><i class="fas fa-link"></i></div>
                <div class="action-title">Copy URL สแกน</div>
                <div class="action-desc">คัดลอกลิงก์สแกนส่งให้คนขับ</div>
            </div>
        </div>
        @endif

        {{-- ยืนยัน (pending) --}}
        @if($deliveryNote->status === 'pending')
            @can('create-edit')
            <div class="col-md-3 col-6 mb-2">
                <div class="action-card green" onclick="confirmAction()" style="cursor:pointer">
                    <div class="action-icon text-info"><i class="fas fa-check-circle"></i></div>
                    <div class="action-title">ยืนยันเอกสาร</div>
                    <div class="action-desc">ยืนยันเพื่อเปิดให้สแกนสินค้า</div>
                </div>
            </div>
            @endcan
        @endif

        {{-- แก้ไข (pending/confirmed) --}}
        @if(in_array($deliveryNote->status, ['pending', 'confirmed']))
            @can('create-edit')
            <div class="col-md-3 col-6 mb-2">
                <a href="{{ route('admin.delivery-notes.edit', $deliveryNote->id) }}" class="text-decoration-none">
                    <div class="action-card yellow">
                        <div class="action-icon text-warning"><i class="fas fa-edit"></i></div>
                        <div class="action-title">แก้ไข</div>
                        <div class="action-desc">แก้ไขข้อมูลใบตัดสต็อก</div>
                    </div>
                </a>
            </div>
            @endcan
        @endif

        {{-- สแกนใหม่ (scanned) --}}
        @if($deliveryNote->status === 'scanned')
            @can('approve')
            <div class="col-md-3 col-6 mb-2">
                <div class="action-card red" onclick="confirmResetScan()" style="cursor:pointer">
                    <div class="action-icon text-danger"><i class="fas fa-redo-alt"></i></div>
                    <div class="action-title">สแกนใหม่</div>
                    <div class="action-desc">ล้างข้อมูลสแกนทั้งหมดแล้วเริ่มใหม่</div>
                </div>
            </div>
            @endcan
        @endif

        {{-- อนุมัติ (scanned) --}}
        @if($deliveryNote->status === 'scanned')
            @can('approve')
            <div class="col-md-3 col-6 mb-2">
                @if(!$hasIssues)
                    <div class="action-card green" onclick="document.getElementById('approve-form').dispatchEvent(new Event('submit'))">
                        <div class="action-icon text-success"><i class="fas fa-check-double"></i></div>
                        <div class="action-title">อนุมัติ &amp; ตัดสต็อก</div>
                        <div class="action-desc">ตรงทุกรายการ พร้อมตัดสต็อกทันที</div>
                    </div>
                @else
                    <div class="action-card red" onclick="confirmForceApprove()">
                        <div class="action-icon text-danger"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="action-title">บังคับอนุมัติ</div>
                        <div class="action-desc">ตัดสต็อกตามสแกนจริง (สแกนยังไม่ครบ {{ count($discrepancies) }} รายการ)</div>
                    </div>
                @endif
            </div>
            @endcan
        @endif

        {{-- พิมพ์ --}}
        <div class="col-md-3 col-6 mb-2">
            <a href="{{ route('admin.delivery-notes.print', $deliveryNote->id) }}" class="text-decoration-none" target="_blank">
                <div class="action-card yellow">
                    <div class="action-icon text-secondary"><i class="fas fa-print"></i></div>
                    <div class="action-title">พิมพ์</div>
                    <div class="action-desc">พิมพ์ใบตัดสต็อก</div>
                </div>
            </a>
        </div>

        {{-- ลบ (pending) --}}
        @if($deliveryNote->status === 'pending')
            @can('delete')
            <div class="col-md-3 col-6 mb-2">
                <div class="action-card red" onclick="confirmDelete()" style="cursor:pointer">
                    <div class="action-icon text-danger"><i class="fas fa-trash"></i></div>
                    <div class="action-title">ลบ</div>
                    <div class="action-desc">ลบใบตัดสต็อกนี้</div>
                </div>
            </div>
            @endcan
        @endif
    </div>

    {{-- Hidden forms --}}
    @if($deliveryNote->status === 'pending')
    <form action="{{ route('admin.delivery-notes.confirm', $deliveryNote->id) }}" method="POST" id="confirm-form" class="d-none">
        @csrf
    </form>
    @endif

    @if($deliveryNote->status === 'scanned')
    <form action="{{ route('admin.delivery-notes.approve', $deliveryNote->id) }}" method="POST" id="approve-form" class="d-none">
        @csrf
    </form>
    <form action="{{ route('admin.delivery-notes.approve', $deliveryNote->id) }}" method="POST" id="force-approve-form" class="d-none">
        @csrf
        <input type="hidden" name="force_approve" value="1">
    </form>
    <form action="{{ route('admin.delivery-notes.reset-scan', $deliveryNote->id) }}" method="POST" id="reset-scan-form" class="d-none">
        @csrf
    </form>
    @endif

    @if($deliveryNote->status === 'pending')
    <form action="{{ route('admin.delivery-notes.destroy', $deliveryNote->id) }}" method="POST" id="delete-form" class="d-none">
        @csrf
        @method('DELETE')
    </form>
    @endif
@stop

@section('js')
<script>
// ยืนยันเอกสาร
function confirmAction() {
    Swal.fire({
        title: 'ยืนยันเอกสาร?',
        text: 'ยืนยันใบตัดสต็อกเพื่อเปิดให้สแกน Barcode',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        confirmButtonText: '<i class="fas fa-check-circle"></i> ยืนยัน',
        cancelButtonText: 'ยกเลิก'
    }).then(r => { if (r.isConfirmed) document.getElementById('confirm-form').submit(); });
}

// อนุมัติ
$('#approve-form').on('submit', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'ยืนยันการอนุมัติ?',
        text: 'ระบบจะตัดสต็อกสินค้าทันที ไม่สามารถยกเลิกได้',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: '<i class="fas fa-check-double"></i> อนุมัติ',
        cancelButtonText: 'ยกเลิก'
    }).then(r => { if (r.isConfirmed) this.submit(); });
});

// บังคับอนุมัติ
function confirmForceApprove() {
    Swal.fire({
        title: 'บังคับอนุมัติ?',
        html: '<p class="text-danger mb-1"><strong>มีสินค้าสแกนยังไม่ครบจำนวน {{ count($discrepancies) }} รายการ</strong></p>' +
              '<p class="mb-0" style="font-size:.9rem">ระบบจะตัดสต็อกตามจำนวนที่สแกนจริง (ไม่ใช่จำนวนที่กำหนด)</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-exclamation-triangle"></i> บังคับอนุมัติ',
        cancelButtonText: 'ยกเลิก',
        input: 'textarea',
        inputPlaceholder: 'ระบุเหตุผล (ถ้ามี)',
    }).then(r => {
        if (r.isConfirmed) {
            if (r.value) {
                $('<input>').attr({ type: 'hidden', name: 'reason', value: r.value }).appendTo('#force-approve-form');
            }
            $('#force-approve-form').submit();
        }
    });
}

// Copy URL สแกน
$('#btn-copy-url').on('click', function() {
    const card = $(this);
    const origIcon = card.find('.action-icon').html();
    card.css('pointer-events','none');
    card.find('.action-icon').html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url: card.data('url'),
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                navigator.clipboard.writeText(res.url).then(function() {
                    card.find('.action-icon').html('<i class="fas fa-check text-success"></i>');
                    Swal.fire({
                        icon: 'success',
                        title: 'คัดลอก URL แล้ว!',
                        html: '<div class="text-start">' +
                            '<p class="mb-1">ลิงก์สแกน Barcode พร้อมส่งให้คนขับแล้ว</p>' +
                            '<div class="alert alert-info py-2 px-3" style="font-size:.85rem;word-break:break-all">' + res.url + '</div>' +
                            '<p class="mb-0 text-muted" style="font-size:.85rem"><i class="fas fa-clock"></i> หมดอายุ: ' + res.expires_at + ' (3 ชม.)</p></div>',
                        confirmButtonText: 'ตกลง',
                    });
                }).catch(function() {
                    prompt('คัดลอก URL นี้:', res.url);
                });
            } else {
                Swal.fire('ไม่สำเร็จ', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('ผิดพลาด', 'ไม่สามารถสร้างลิงก์ได้', 'error');
        },
        complete: function() {
            setTimeout(() => {
                card.css('pointer-events','auto');
                card.find('.action-icon').html(origIcon);
            }, 2000);
        }
    });
});

// สแกนใหม่
function confirmResetScan() {
    Swal.fire({
        title: 'สแกนใหม่ทั้งหมด?',
        html: '<p class="text-danger mb-1"><strong>ข้อมูลการสแกนทั้งหมดจะถูกลบ</strong></p>' +
              '<p class="mb-0" style="font-size:.9rem">ระบบจะล้างข้อมูลสแกนทุกรายการ แล้วกลับไปหน้าสแกนใหม่</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-redo-alt"></i> ยืนยัน สแกนใหม่',
        cancelButtonText: 'ยกเลิก',
    }).then(r => {
        if (r.isConfirmed) {
            $('#reset-scan-form').submit();
        }
    });
}

// ลบ
function confirmDelete() {
    Swal.fire({
        title: 'ลบใบตัดสต็อก?',
        text: 'คุณต้องการลบใบตัดสต็อกนี้ใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-trash"></i> ลบ',
        cancelButtonText: 'ยกเลิก',
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    });
}
</script>
@stop
