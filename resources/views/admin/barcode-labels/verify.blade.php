@extends('adminlte::page')

@section('title', 'สแกนยืนยัน Label - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-qrcode"></i> สแกนยืนยันติด Label</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.barcode-labels.index') }}">พิมพ์ Label Barcode</a></li>
                <li class="breadcrumb-item active">สแกนยืนยัน</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Scan Input -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-barcode"></i> สแกนบาร์โค้ดที่ติดแล้ว</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <i class="fas fa-info-circle"></i> สแกนบาร์โค้ดบน Label ที่ติดบนสินค้าแล้ว เพื่อยืนยันว่าติดถูกตัว
                    </p>
                    <div class="form-group">
                        <label for="barcodeInput"><i class="fas fa-keyboard"></i> สแกนหรือพิมพ์บาร์โค้ด:</label>
                        <input type="text" class="form-control form-control-lg text-center" 
                               id="barcodeInput" 
                               placeholder="สแกนบาร์โค้ดที่นี่..." 
                               autofocus autocomplete="off">
                    </div>

                    <!-- Result Area -->
                    <div id="scanResult" style="display:none;">
                        <hr>
                        <div id="resultContent"></div>
                    </div>
                </div>
            </div>

            <!-- Scan Stats -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> สรุปการยืนยันครั้งนี้</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-success">
                                <h3 id="verifiedCount">0</h3>
                                <small>ยืนยันสำเร็จ</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-danger">
                                <h3 id="failedCount">0</h3>
                                <small>ไม่พบ/ผิด</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-info">
                                <h3 id="totalScans">0</h3>
                                <small>สแกนทั้งหมด</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scan History -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> ประวัติสแกนครั้งนี้</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="clearHistory">
                            <i class="fas fa-trash"></i> ล้าง
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th width="30">#</th>
                                <th>บาร์โค้ด</th>
                                <th>สินค้า</th>
                                <th>ผลลัพธ์</th>
                            </tr>
                        </thead>
                        <tbody id="scanHistoryBody">
                            <tr id="emptyRow">
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-barcode fa-2x mb-2"></i><br>
                                    ยังไม่มีการสแกน
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pending Verification -->
            @if($recentLogs->count() > 0)
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clock"></i> รายการรอยืนยัน ({{ $recentLogs->count() }})</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>บาร์โค้ด</th>
                                <th>สินค้า</th>
                                <th>พิมพ์เมื่อ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLogs as $log)
                            <tr id="pending-{{ $log->stock_item_id }}">
                                <td><code>{{ $log->barcode }}</code></td>
                                <td>{{ $log->stockItem?->product?->full_name ?? '-' }}</td>
                                <td>{{ $log->created_at->format('d/m H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let scanCount = 0;
    let verifiedCount = 0;
    let failedCount = 0;

    // Focus on input
    $('#barcodeInput').focus();

    // Process barcode scan (Enter key or auto-detect scanner)
    $('#barcodeInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const barcode = $(this).val().trim();
            if (barcode) {
                processScan(barcode);
                $(this).val('').focus();
            }
        }
    });

    function processScan(barcode) {
        scanCount++;
        $('#totalScans').text(scanCount);
        $('#emptyRow').hide();

        // Show loading
        $('#scanResult').show();
        $('#resultContent').html(`
            <div class="text-center text-muted">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>กำลังตรวจสอบ...</p>
            </div>
        `);

        $.ajax({
            url: '{{ route("admin.barcode-labels.verify-scan") }}',
            method: 'POST',
            data: {
                barcode: barcode,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    verifiedCount++;
                    $('#verifiedCount').text(verifiedCount);
                    
                    $('#resultContent').html(`
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> ${response.message}</h5>
                            <div class="row mt-2">
                                <div class="col-6"><strong>สินค้า:</strong> ${response.data.product_name}</div>
                                <div class="col-6"><strong>บาร์โค้ด:</strong> <code>${response.data.barcode}</code></div>
                                <div class="col-6"><strong>SN:</strong> ${response.data.serial_number || '-'}</div>
                                <div class="col-6"><strong>คลัง:</strong> ${response.data.warehouse}</div>
                            </div>
                        </div>
                    `);

                    // Remove from pending list
                    addToHistory(scanCount, barcode, response.data.product_name, 'success', response.message);
                    
                    // Play success sound (optional beep)
                    playBeep(true);
                } else {
                    handleFailure(barcode, response);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON || { message: 'เกิดข้อผิดพลาดในการเชื่อมต่อ' };
                handleFailure(barcode, response);
            }
        });
    }

    function handleFailure(barcode, response) {
        failedCount++;
        $('#failedCount').text(failedCount);

        let icon = response.already_verified ? 'info-circle' : 'times-circle';
        let alertClass = response.already_verified ? 'warning' : 'danger';

        $('#resultContent').html(`
            <div class="alert alert-${alertClass}">
                <h5><i class="fas fa-${icon}"></i> ${response.message}</h5>
                <div><strong>บาร์โค้ดที่สแกน:</strong> <code>${barcode}</code></div>
            </div>
        `);

        addToHistory(scanCount, barcode, '-', response.already_verified ? 'warning' : 'danger', response.message);
        playBeep(false);
    }

    function addToHistory(num, barcode, product, type, message) {
        const badgeClass = type === 'success' ? 'badge-success' : (type === 'warning' ? 'badge-warning' : 'badge-danger');
        const icon = type === 'success' ? 'check' : (type === 'warning' ? 'exclamation-triangle' : 'times');
        const label = type === 'success' ? 'สำเร็จ' : (type === 'warning' ? 'ซ้ำ' : 'ไม่พบ');

        const row = `
            <tr class="table-${type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')}">
                <td>${num}</td>
                <td><code>${barcode}</code></td>
                <td>${product}</td>
                <td><span class="badge ${badgeClass}"><i class="fas fa-${icon}"></i> ${label}</span></td>
            </tr>
        `;
        $('#scanHistoryBody').prepend(row);
    }

    function playBeep(success) {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = success ? 800 : 300;
            gain.gain.value = 0.1;
            osc.start();
            osc.stop(ctx.currentTime + (success ? 0.15 : 0.4));
        } catch(e) {}
    }

    // Clear history
    $('#clearHistory').click(function() {
        scanCount = 0;
        verifiedCount = 0;
        failedCount = 0;
        $('#totalScans, #verifiedCount, #failedCount').text('0');
        $('#scanHistoryBody').html(`
            <tr id="emptyRow">
                <td colspan="4" class="text-center text-muted py-4">
                    <i class="fas fa-barcode fa-2x mb-2"></i><br>
                    ยังไม่มีการสแกน
                </td>
            </tr>
        `);
        $('#scanResult').hide();
        $('#barcodeInput').focus();
    });

    // Keep focus on input
    $(document).on('click', function() {
        $('#barcodeInput').focus();
    });
});
</script>
@stop
