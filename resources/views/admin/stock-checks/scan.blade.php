@extends('adminlte::page')

@section('title', 'สแกน Barcode - ' . $stockCheck->name)

@section('adminlte_css_pre')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
@stop

@section('content')
    <!-- PDA-Optimized Scanning Interface -->
    <div class="scan-wrapper">
        
        <!-- Session Info Bar -->
        <div class="session-bar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="session-title">{{ $stockCheck->title }}</div>
                    <div class="session-sub"><i class="fas fa-warehouse"></i> {{ $stockCheck->warehouse->name }}</div>
                </div>
                <div class="text-right">
                    <div class="scan-counter" id="total-scanned">0</div>
                    <div class="scan-counter-label">รายการ</div>
                </div>
            </div>
        </div>

        <!-- Barcode Input -->
        <div class="scan-input-area">
            <div class="input-group">
                <input type="text" 
                       id="barcode-input" 
                       class="form-control mobile-input" 
                       placeholder="สแกน / พิมพ์ Barcode"
                       autocomplete="off"
                       autocapitalize="off"
                       autocorrect="off"
                       spellcheck="false"
                       inputmode="none"
                       autofocus>
                <div class="input-group-append">
                    <button type="button" id="manual-scan-btn" class="btn btn-success">
                        <i class="fas fa-plus fa-lg"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Scanned Items List -->
        <div class="scan-list-area">
            <div class="scan-list-header">
                <i class="fas fa-list"></i> รายการที่สแกนแล้ว
            </div>
            <div class="scan-list-body" id="scanned-items-list">
                <div class="empty-state">
                    <i class="fas fa-barcode"></i>
                    <p>ยังไม่มีรายการ</p>
                    <small>เริ่มสแกนสินค้าเพื่อเพิ่มรายการ</small>
                </div>
            </div>
        </div>

        <!-- Action Buttons - Fixed Bottom -->
        <div class="action-buttons-fixed">
            <a href="{{ route('admin.stock-checks.show', $stockCheck) }}" class="btn-action btn-cancel">
                <i class="fas fa-times"></i> ยกเลิก
            </a>
            <button type="button" id="save-btn" class="btn-action btn-save" onclick="saveAndReturn()" disabled>
                <i class="fas fa-save"></i> บันทึก
            </button>
        </div>

    </div>

    <!-- Alert for scan results -->
    <div id="scan-alert" class="alert" style="display: none; position: fixed; top: 10px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 280px; max-width: 90vw;">
    </div>
@stop

@section('css')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
    <style>
        /* ===== PDA / Mobile Scanner Full-Screen Layout ===== */

        /* Hide content-header & footer only — keep navbar & sidebar accessible */
        .content-header,
        .main-footer {
            display: none !important;
        }

        .content-wrapper {
            min-height: 100vh !important;
            background: #f0f2f5;
        }

        .content {
            padding: 0 !important;
        }

        body {
            font-size: 15px;
            background: #f0f2f5;
            overflow: hidden;
            -webkit-text-size-adjust: 100%;
        }

        /* ===== Main wrapper — flex column fills viewport ===== */
        .scan-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 57px); /* minus navbar height */
            height: calc(100dvh - 57px);
            overflow: hidden;
        }

        /* ===== Session Info Bar ===== */
        .session-bar {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: #fff;
            padding: 10px 14px;
            flex-shrink: 0;
        }

        .session-title {
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.2;
        }

        .session-sub {
            font-size: 0.8rem;
            opacity: 0.85;
            margin-top: 2px;
        }

        .scan-counter {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .scan-counter-label {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        /* ===== Barcode Input Area ===== */
        .scan-input-area {
            padding: 10px 12px;
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            flex-shrink: 0;
        }

        .mobile-input {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            letter-spacing: 1px;
            font-size: 18px !important;
            height: 52px;
            text-align: center;
            border: 2px solid #007bff;
            border-right: none;
            border-radius: 8px 0 0 8px !important;
        }

        .mobile-input:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.15);
        }

        .scan-input-area .btn-success {
            height: 52px;
            width: 56px;
            border-radius: 0 8px 8px 0 !important;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== Scanned Items List ===== */
        .scan-list-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        .scan-list-header {
            padding: 8px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
            background: #e9ecef;
            flex-shrink: 0;
        }

        .scan-list-body {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            background: #fff;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            color: #adb5bd;
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: block;
            opacity: 0.4;
        }

        .empty-state p {
            margin: 0 0 4px;
            font-size: 0.95rem;
        }

        .empty-state small {
            font-size: 0.8rem;
        }

        /* Scanned item row */
        .scanned-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px solid #f0f0f0;
            background: #fff;
        }

        .scanned-item:active {
            background: #f8f9fa;
        }

        .item-barcode {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 0.95rem;
            color: #2c3e50;
            line-height: 1.2;
        }

        .item-product {
            color: #6c757d;
            font-size: 0.82rem;
            margin-top: 2px;
            line-height: 1.2;
        }

        .item-count {
            font-size: 1.4rem;
            font-weight: 800;
            color: #28a745;
            line-height: 1;
        }

        .item-count-label {
            font-size: 0.7rem;
            color: #adb5bd;
        }

        .scan-badge {
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.72rem;
            display: inline-block;
            margin-top: 3px;
        }

        /* ===== Fixed Bottom Buttons ===== */
        .action-buttons-fixed {
            display: flex;
            gap: 8px;
            padding: 8px 12px;
            padding-bottom: max(8px, env(safe-area-inset-bottom));
            background: #fff;
            border-top: 1px solid #dee2e6;
            flex-shrink: 0;
        }

        .btn-action {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 8px 10px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.15s;
        }

        .btn-action:active {
            opacity: 0.8;
        }

        .btn-cancel {
            background: #e9ecef;
            color: #495057;
        }

        .btn-cancel:hover {
            color: #495057;
            text-decoration: none;
        }

        .btn-save {
            background: #28a745;
            color: #fff;
        }

        .btn-save:disabled {
            background: #a8d5b5;
            cursor: not-allowed;
        }

        /* ===== Alert toast ===== */
        #scan-alert {
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            padding: 12px 18px;
        }

        /* ===== Loading animation ===== */
        .loading-pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* ===== Desktop (lg+) ===== */
        @media (min-width: 992px) {
            .scan-wrapper {
                height: calc(100vh - 57px);
            }

            .session-bar {
                padding: 12px 20px;
            }

            .scan-input-area {
                padding: 14px 20px;
            }

            .mobile-input {
                font-size: 20px !important;
                height: 56px;
            }

            .scan-input-area .btn-success {
                height: 56px;
                width: 64px;
            }

            .scan-list-header {
                padding: 10px 20px;
            }

            .scanned-item {
                padding: 12px 20px;
            }

            .action-buttons-fixed {
                padding: 12px 20px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        let sessionId = {{ $stockCheck->id }};
        let isScanning = false;
        let scannedItems = [];

        $(document).ready(function() {
            loadScannedItems();
            
            // Auto-focus on barcode input
            $('#barcode-input').focus();

            // Handle Enter key or scan gun input
            $('#barcode-input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    processBarcode();
                }
            });

            // Handle manual scan button
            $('#manual-scan-btn').on('click', function() {
                processBarcode();
            });
        });

        function processBarcode() {
            const barcode = $('#barcode-input').val().trim();
            
            if (!barcode) {
                showAlert('กรุณาใส่ Barcode', 'warning');
                $('#barcode-input').focus();
                return;
            }

            if (isScanning) {
                return;
            }

            isScanning = true;
            
            $('#barcode-input').addClass('loading-pulse');
            $('#manual-scan-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            showAlert('กำลังบันทึก...', 'info');

            $.ajax({
                url: `{{ route('admin.stock-checks.process-scan', $stockCheck) }}`,
                method: 'POST',
                data: {
                    barcode: barcode,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, getAlertClass(response.status));
                        
                        // Clear input
                        $('#barcode-input').val('').focus();
                        
                        // Reload items
                        loadScannedItems();
                        
                        // Vibrate on success
                        if (navigator.vibrate) {
                            navigator.vibrate(100);
                        }
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function(xhr) {
                    let message = 'เกิดข้อผิดพลาด';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showAlert(message, 'danger');
                    
                    // Vibrate on error
                    if (navigator.vibrate) {
                        navigator.vibrate([100, 100, 100]);
                    }
                },
                complete: function() {
                    isScanning = false;
                    $('#barcode-input').removeClass('loading-pulse');
                    $('#manual-scan-btn').prop('disabled', false).html('<i class="fas fa-plus"></i>');
                }
            });
        }

        function loadScannedItems() {
            $.get(`{{ route('admin.api.stock-checks.recent-scans', $stockCheck) }}`, function(data) {
                scannedItems = data;
                
                // Update total count
                $('#total-scanned').text(data.length);
                
                // Enable/disable save button
                if (data.length > 0) {
                    $('#save-btn').prop('disabled', false);
                } else {
                    $('#save-btn').prop('disabled', true);
                }
                
                // Render items list
                renderScannedItems(data);
            });
        }

        function renderScannedItems(items) {
            let html = '';
            
            if (items.length === 0) {
                html = `<div class="empty-state">
                    <i class="fas fa-barcode"></i>
                    <p>ยังไม่มีรายการ</p>
                    <small>เริ่มสแกนสินค้าเพื่อเพิ่มรายการ</small>
                </div>`;
            } else {
                // Group items by barcode and count
                const groupedItems = {};
                items.forEach(item => {
                    if (!groupedItems[item.barcode]) {
                        groupedItems[item.barcode] = {
                            barcode: item.barcode,
                            product: item.product,
                            count: 0,
                            status: item.status
                        };
                    }
                    groupedItems[item.barcode].count += 1;
                });
                
                // Render grouped items
                Object.values(groupedItems).forEach(item => {
                    const productName = item.product ? item.product.name : 'ไม่พบในระบบ';
                    const statusClass = getStatusClass(item.status);
                    const statusText = getStatusText(item.status);
                    
                    html += `<div class="scanned-item">
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="item-barcode">${item.barcode}</div>
                            <div class="item-product text-truncate">${productName}</div>
                            <span class="badge badge-${statusClass} scan-badge">${statusText}</span>
                        </div>
                        <div class="text-right ml-2" style="flex-shrink:0">
                            <div class="item-count">${item.count}</div>
                            <div class="item-count-label">ชิ้น</div>
                        </div>
                    </div>`;
                });
            }
            
            $('#scanned-items-list').html(html);
        }

        function showAlert(message, type) {
            const alertClass = `alert-${type}`;
            const iconClass = getIconClass(type);
            
            $('#scan-alert')
                .removeClass('alert-success alert-danger alert-warning alert-info')
                .addClass(alertClass)
                .html(`<i class="${iconClass}"></i> ${message}`)
                .fadeIn(300);
            
            setTimeout(function() {
                $('#scan-alert').fadeOut(300);
            }, 3000);
        }

        function getAlertClass(status) {
            switch(status) {
                case 'found': return 'success';
                case 'not_in_system': return 'warning';
                case 'duplicate': return 'info';
                default: return 'info';
            }
        }

        function getStatusClass(status) {
            switch(status) {
                case 'found': return 'success';
                case 'not_in_system': return 'warning';
                case 'duplicate': return 'info';
                default: return 'secondary';
            }
        }

        function getStatusText(status) {
            switch(status) {
                case 'found': return 'พบในระบบ';
                case 'not_in_system': return 'ไม่มีในระบบ';
                case 'duplicate': return 'สแกนซ้ำ';
                default: return 'ไม่ทราบ';
            }
        }

        function getIconClass(type) {
            switch(type) {
                case 'success': return 'fas fa-check-circle';
                case 'danger': return 'fas fa-times-circle';
                case 'warning': return 'fas fa-exclamation-circle';
                case 'info': return 'fas fa-info-circle';
                default: return 'fas fa-info-circle';
            }
        }

        function saveAndReturn() {
            if (scannedItems.length === 0) {
                showAlert('ยังไม่มีรายการที่สแกน', 'warning');
                return;
            }

            // Simply return to show page
            window.location.href = '{{ route("admin.stock-checks.show", $stockCheck) }}';
        }

        // Prevent screen sleep on mobile
        if ('wakeLock' in navigator) {
            navigator.wakeLock.request('screen').catch(err => {
                console.log('Wake lock not supported');
            });
        }
    </script>
@stop