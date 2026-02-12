@extends('adminlte::page')

@section('title', 'สแกน QR/Barcode - ' . $stockCheck->name)

@section('adminlte_css_pre')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <!-- Minimal header for mobile scanning -->
    <div class="row d-none d-md-block">
        <div class="col-12">
            <h1>สแกน Barcode</h1>
            <p class="text-muted">{{ $stockCheck->title }} - {{ $stockCheck->warehouse->name }}</p>
        </div>
    </div>
@stop

@section('content')
    <!-- Simple Mobile-Optimized Scanning Interface -->
    <div class="container-fluid px-2">
        
        <!-- Session Info Bar -->
        <div class="alert alert-primary mb-3" style="border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $stockCheck->title }}</strong>
                    <br>
                    <small>{{ $stockCheck->warehouse->name }}</small>
                </div>
                <div class="text-right">
                    <h3 class="mb-0" id="total-scanned">0</h3>
                    <small>รายการ</small>
                </div>
            </div>
        </div>

        <!-- Barcode Scanner Card -->
        <div class="card card-primary mb-3" style="border-radius: 12px;">
            <div class="card-body">
                <div class="form-group mb-0">
                    <label for="barcode-input" class="form-label mb-2"><strong>สแกน Barcode</strong></label>
                    <div class="input-group input-group-lg">
                        <input type="text" 
                               id="barcode-input" 
                               class="form-control mobile-input" 
                               placeholder="ยิงเลเซอร์ที่นี่"
                               autocomplete="off"
                               autocapitalize="off"
                               spellcheck="false"
                               autofocus>
                        <div class="input-group-append">
                            <button type="button" id="manual-scan-btn" class="btn btn-success btn-lg">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scanned Items List -->
        <div class="card" style="border-radius: 12px;">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="fas fa-list"></i> รายการที่สแกนแล้ว
                </h6>
            </div>
            <div class="card-body p-0" style="max-height: calc(100vh - 400px); overflow-y: auto;">
                <div id="scanned-items-list">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-barcode fa-3x mb-3 opacity-50"></i>
                        <p class="mb-0">ยังไม่มีรายการ</p>
                        <small>เริ่มสแกนสินค้าเพื่อเพิ่มรายการ</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons - Fixed Bottom -->
        <div class="action-buttons-fixed">
            <div class="row">
                <div class="col-6">
                    <a href="{{ route('admin.stock-checks.show', $stockCheck) }}" class="btn btn-secondary btn-lg btn-block">
                        <i class="fas fa-times"></i> ยกเลิก
                    </a>
                </div>
                <div class="col-6">
                    <button type="button" id="save-btn" class="btn btn-success btn-lg btn-block" onclick="saveAndReturn()" disabled>
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Alert for scan results -->
    <div id="scan-alert" class="alert" style="display: none; position: fixed; top: 10px; left: 50%; transform: translateX(-50%); z-index: 1050; min-width: 280px; max-width: 90vw;">
    </div>
@stop

@section('css')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
    <style>
        /* Mobile-First Simple Design */
        body {
            font-size: 16px;
            background: #f4f6f9;
        }
        
        .mobile-input {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            letter-spacing: 1px;
            font-size: 20px !important;
            height: 70px;
            text-align: center;
        }
        
        /* Scanned items list */
        .scanned-item {
            border-bottom: 1px solid #e9ecef;
            padding: 15px;
            background: white;
            transition: background 0.2s;
        }
        
        .scanned-item:hover {
            background: #f8f9fa;
        }
        
        .scanned-item:last-child {
            border-bottom: none;
        }
        
        .item-barcode {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .item-product {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 5px;
        }
        
        .item-count {
            font-size: 1.3rem;
            font-weight: bold;
            color: #28a745;
        }
        
        .scan-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        #scan-alert {
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border-radius: 12px;
            font-size: 18px;
            font-weight: 500;
        }

        /* Fixed bottom buttons */
        .action-buttons-fixed {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 15px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        /* Add bottom padding to content to prevent overlap */
        .container-fluid {
            padding-bottom: 100px !important;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 8px !important;
                padding-bottom: 100px !important;
            }
            
            .card {
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                border: none;
            }
            
            .btn-lg {
                padding: 15px 20px;
                font-size: 18px;
                border-radius: 10px;
                font-weight: 500;
            }
            
            .input-group-lg .form-control {
                border-radius: 10px 0 0 10px;
            }
            
            .input-group-lg .btn {
                border-radius: 0 10px 10px 0;
                min-width: 70px;
            }
            
            /* Hide desktop elements */
            .content-header {
                display: none;
            }
            
            .main-sidebar,
            .main-header,
            .main-footer {
                display: none !important;
            }
            
            .content-wrapper {
                margin-left: 0 !important;
            }
        }
        
        /* Large touch targets */
        .btn {
            min-height: 50px;
        }
        
        .form-control {
            min-height: 50px;
        }
        
        /* Loading animation */
        .loading-pulse {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Empty state */
        .opacity-50 {
            opacity: 0.5;
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
                html = `<div class="text-center text-muted py-5">
                    <i class="fas fa-barcode fa-3x mb-3 opacity-50"></i>
                    <p class="mb-0">ยังไม่มีรายการ</p>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <div class="item-barcode">${item.barcode}</div>
                                <div class="item-product">${productName}</div>
                                <span class="badge badge-${statusClass} scan-badge">${statusText}</span>
                            </div>
                            <div class="text-right ml-3">
                                <div class="item-count">${item.count}</div>
                                <small class="text-muted">ชิ้น</small>
                            </div>
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