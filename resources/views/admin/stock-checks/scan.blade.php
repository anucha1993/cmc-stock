@extends('adminlte::page')

@section('title', 'สแกน QR/Barcode - ' . $stockCheck->name)

@section('adminlte_css_pre')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>สแกน Barcode</h1>
            <p class="text-muted">{{ $stockCheck->title }}</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-checks.index') }}">ตรวจนับสต๊อก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.stock-checks.show', $stockCheck) }}">{{ $stockCheck->session_code }}</a></li>
                <li class="breadcrumb-item active">สแกน</li>
            </ol>
        </div>
    </div>

    <!-- Stock Summary -->
    <div class="row mt-2">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-warehouse fa-lg"></i>
                    </div>
                    <div class="col">
                        <strong>{{ $stockCheck->warehouse->name }}</strong>
                        @if($stockCheck->category)
                            | <strong>{{ $stockCheck->category->name }}</strong>
                        @endif
                    </div>
                    <div class="col-auto">
                        <h5 class="mb-0">
                            <span id="expected-count" class="badge badge-warning badge-lg">
                                <i class="fas fa-hourglass-half"></i> 
                                ควรตรวจ: <strong>0</strong> รายการ
                            </span>
                        </h5>
                    </div>
                </div>
                <div class="row mt-2" id="stock-details" style="display: none;">
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            เฉพาะ StockItem ที่มี status = "พร้อมใช้งาน" เท่านั้น
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Mobile-First Layout -->
    <div class="container-fluid px-2">
        
        <!-- Statistics Cards - Mobile Optimized -->
        <div class="row mb-3" id="stats-cards">
            <div class="col-6 col-md-3 mb-2">
                <div class="card bg-primary text-white text-center mobile-stat-card">
                    <div class="card-body py-2">
                        <h4 id="total-scanned" class="mb-0">0</h4>
                        <small>ทั้งหมด</small>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3 mb-2">
                <div class="card bg-success text-white text-center mobile-stat-card">
                    <div class="card-body py-2">
                        <h4 id="found-in-system" class="mb-0">0</h4>
                        <small>พบในระบบ</small>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3 mb-2">
                <div class="card bg-warning text-white text-center mobile-stat-card">
                    <div class="card-body py-2">
                        <h4 id="not-in-system" class="mb-0">0</h4>
                        <small>ไม่มีในระบบ</small>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3 mb-2">
                <div class="card bg-info text-white text-center mobile-stat-card">
                    <div class="card-body py-2">
                        <h4 id="duplicates" class="mb-0">0</h4>
                        <small>Multi-scan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Scanning Interface - Full Width on Mobile -->
        <div class="card card-primary mb-3">
            <div class="card-header py-2">
                <h5 class="card-title mb-0">
                    <i class="fas fa-qrcode"></i>
                    สแกน QR/Barcode
                </h5>
            </div>
            <div class="card-body">
                <!-- Barcode Input - Large and Touch Friendly -->
                <div class="form-group mb-3">
                    <label for="barcode-input" class="form-label"><strong>Barcode/QR Code</strong></label>
                    <div class="input-group input-group-lg">
                        <input type="text" 
                               id="barcode-input" 
                               class="form-control mobile-input" 
                               placeholder="สแกนหรือพิมพ์ที่นี่"
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
                    <small class="form-text text-primary">
                        <i class="fas fa-info-circle"></i> เครื่องยิงจะป้อนข้อมูลอัตโนมัติ
                    </small>
                </div>

                <!-- Location Input - Collapsible on Mobile -->
                <div class="form-group mb-3">
                    <button class="btn btn-outline-secondary btn-sm mb-2" type="button" data-toggle="collapse" data-target="#location-section">
                        <i class="fas fa-map-marker-alt"></i> เพิ่มตำแหน่ง (ไม่บังคับ)
                    </button>
                    <div class="collapse" id="location-section">
                        <input type="text" 
                               id="location-input" 
                               class="form-control" 
                               placeholder="เช่น A-1-3, ชั้น 2">
                    </div>
                </div>

                <!-- Camera Button - Prominent on Mobile -->
                <div class="text-center mb-3">
                    <button type="button" id="camera-scan-btn" class="btn btn-info btn-lg btn-block">
                        <i class="fas fa-camera"></i> เปิดกล้องสแกน QR
                    </button>
                </div>
            </div>
        </div>

        <!-- Action Buttons - Mobile Friendly -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-4">
                        <a href="{{ route('admin.stock-checks.show', $stockCheck) }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left"></i> กลับ
                        </a>
                    </div>
                    <div class="col-4">
                        @if($stockCheck->checkItems()->count() > 0)
                            <button type="button" class="btn btn-primary btn-block" onclick="submitForApproval()">
                                <i class="fas fa-paper-plane"></i> ส่งตรวจสอบ
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-primary btn-block" disabled>
                                <i class="fas fa-paper-plane"></i> ส่งตรวจสอบ
                            </button>
                        @endif
                    </div>
                    <div class="col-4">
                        @if($stockCheck->checkItems()->count() > 0)
                            <button type="button" class="btn btn-warning btn-block" onclick="completeSession()">
                                <i class="fas fa-stop"></i> ปิด
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-warning btn-block" disabled>
                                <i class="fas fa-stop"></i> ปิด
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Scans - Compact Design -->
        <div class="card">
            <div class="card-header py-2" data-toggle="collapse" data-target="#recent-scans-section">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history"></i>
                        รายการล่าสุด <span id="total-scanned" class="badge badge-info ml-1">0</span>
                    </h6>
                    <div>
                        <small class="text-muted mr-2">แสดง 10 รายการ</small>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </div>
            <div class="collapse" id="recent-scans-section">
                <div class="card-body p-2" style="max-height: 350px; overflow-y: auto;">
                    <div id="recent-scans">
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-qrcode fa-2x mb-2"></i>
                            <p class="mb-0">ยังไม่มีการสแกน</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Alert for scan results -->
    <div id="scan-alert" class="alert" style="display: none; position: fixed; top: 80px; right: 20px; z-index: 1050; min-width: 300px;">
    </div>
@stop

@section('css')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <style>
        /* Mobile-First Responsive Design */
        body {
            font-size: 16px; /* Prevent zoom on iOS */
        }
        
        .mobile-input {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            letter-spacing: 1px;
            font-size: 18px !important; /* Prevent zoom on iOS */
            height: 60px;
        }
        
        .mobile-stat-card {
            min-height: 70px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .mobile-stat-card h4 {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .scan-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 8px 0;
            font-size: 0.9rem;
        }
        
        .scan-item:last-child {
            border-bottom: none;
        }
        
        .scan-item-compact {
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 4px;
            padding: 6px 8px;
        }
        
        .barcode-text {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 0.85rem;
            color: #495057;
        }
        
        .product-name {
            font-size: 0.8rem;
            color: #6c757d;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        
        .status-compact {
            font-size: 0.7rem;
            padding: 2px 6px;
        }

        #scan-alert {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 280px;
            max-width: 90vw;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
            font-size: 16px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 5px !important;
            }
            
            .card {
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .btn-lg {
                padding: 12px 20px;
                font-size: 16px;
                border-radius: 8px;
            }
            
            .btn-block {
                width: 100%;
            }
            
            .input-group-lg .form-control {
                border-radius: 8px 0 0 8px;
            }
            
            .input-group-lg .btn {
                border-radius: 0 8px 8px 0;
                min-width: 60px;
            }
            
            /* Hide desktop elements on mobile */
            .content-header {
                display: none;
            }
            
            /* Full screen on mobile */
            .content-wrapper {
                margin-left: 0 !important;
            }
            
            .main-sidebar {
                display: none !important;
            }
            
            .main-header {
                display: none !important;
            }
            
            .main-footer {
                display: none !important;
            }
        }
        
        /* Large touch targets */
        .btn {
            min-height: 44px; /* iOS HIG minimum */
        }
        
        .form-control {
            min-height: 44px;
        }
        
        /* Improved visibility */
        .text-success { color: #28a745 !important; }
        .text-warning { color: #ffc107 !important; }
        .text-danger { color: #dc3545 !important; }
        .text-info { color: #17a2b8 !important; }
        
        /* Loading animation */
        .loading-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }
        
        /* Camera preview */
        #camera-container {
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 15px;
        }
        
        #camera-preview {
            width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: cover;
        }
    </style>
@stop

@section('js')
    <script>
        let sessionId = {{ $stockCheck->id }};
        let isScanning = false;

        $(document).ready(function() {
            loadInitialStats();
            loadRecentScans();
            loadExpectedStock(); // Load expected stock count
            
            // Auto-focus on barcode input
            $('#barcode-input').focus();

            // Handle Enter key or scan gun input
            $('#barcode-input').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    processBarcode();
                }
            });

            // Handle manual scan button
            $('#manual-scan-btn').on('click', function() {
                processBarcode();
            });

            // Auto-refresh stats every 10 seconds
            setInterval(function() {
                loadStats();
            }, 10000);
        });

        function loadExpectedStock() {
            $.get(`{{ route('admin.api.stock-checks.stats', $stockCheck) }}`, function(data) {
                if (data.expected_count !== undefined) {
                    $('#expected-count').html(
                        '<i class="fas fa-hourglass-half"></i> ' +
                        'ควรตรวจ: <strong>' + data.expected_count + '</strong> รายการ'
                    );
                }
            });
        }

        function processBarcode() {
            const barcode = $('#barcode-input').val().trim();
            const location = $('#location-input').val().trim();
            
            if (!barcode) {
                showAlert('กรุณาใส่ Barcode/QR Code', 'warning');
                $('#barcode-input').focus();
                return;
            }

            if (isScanning) {
                return; // Prevent double submission
            }

            isScanning = true;
            
            // Add loading state to input
            $('#barcode-input').addClass('loading-pulse');
            $('#manual-scan-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            // Show loading alert
            showAlert('กำลังตรวจสอบ...', 'info');

            $.ajax({
                url: `{{ route('admin.stock-checks.process-scan', $stockCheck) }}`,
                method: 'POST',
                data: {
                    barcode: barcode,
                    location_found: location,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, getAlertClass(response.status));
                        
                        // Clear inputs
                        $('#barcode-input').val('').focus();
                        $('#location-input').val('');
                        
                        // Collapse location section if it was open
                        $('#location-section').collapse('hide');
                        
                        // Refresh data
                        loadStats();
                        loadRecentScans();
                        
                        // Auto-expand recent scans on first scan
                        if (!$('#recent-scans-section').hasClass('show')) {
                            $('#recent-scans-section').collapse('show');
                        }
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function(xhr) {
                    let message = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showAlert(message, 'danger');
                },
                complete: function() {
                    isScanning = false;
                    $('#barcode-input').removeClass('loading-pulse');
                    $('#manual-scan-btn').prop('disabled', false).html('<i class="fas fa-plus"></i>');
                }
            });
        }

        function loadInitialStats() {
            loadStats();
        }

        function loadStats() {
            $.get(`{{ route('admin.api.stock-checks.stats', $stockCheck) }}`, function(data) {
                $('#total-scanned').text(data.total_scanned);
                $('#found-in-system').text(data.found_in_system);
                $('#not-in-system').text(data.not_in_system);
                $('#duplicates').text(data.duplicates);
            });
        }

        function loadRecentScans() {
            $.get(`{{ route('admin.api.stock-checks.recent-scans', $stockCheck) }}`, function(data) {
                let html = '';
                
                // Update total count
                $('#total-scanned').text(data.length);
                
                if (data.length === 0) {
                    html = `<div class="text-center text-muted py-2">
                        <i class="fas fa-barcode fa-lg mb-1"></i>
                        <p class="mb-0 small">ยังไม่มีการสแกน</p>
                    </div>`;
                } else {
                    // Show only last 10 items for performance
                    const recentItems = data.slice(0, 10);
                    
                    recentItems.forEach(function(item) {
                        const productName = item.product ? item.product.name : 'ไม่พบในระบบ';
                        const statusClass = getStatusClass(item.status);
                        const timeAgo = moment(item.last_scanned_at).fromNow();
                        
                        html += `<div class="scan-item-compact">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <div class="barcode-text">${item.barcode}</div>
                                    <div class="product-name" title="${productName}">${productName}</div>
                                </div>
                                <div class="text-right ml-2">
                                    <div>
                                        <span class="badge badge-${statusClass} status-compact">${getStatusText(item.status)}</span>
                                        ${item.scanned_count > 1 ? `<span class="badge badge-secondary status-compact ml-1">${item.scanned_count}x</span>` : ''}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">${timeAgo}</small>
                                </div>
                            </div>
                        </div>`;
                    });
                    
                    // Add more indicator if there are more items
                    if (data.length > 10) {
                        html += `<div class="text-center py-2">
                            <small class="text-muted">และอีก ${data.length - 10} รายการ...</small>
                        </div>`;
                    }
                }
                
                $('#recent-scans').html(html);
            });
        }

        function showAlert(message, type) {
            const alertClass = `alert-${type}`;
            const iconClass = getIconClass(type);
            
            $('#scan-alert')
                .removeClass('alert-success alert-danger alert-warning alert-info')
                .addClass(alertClass)
                .html(`<i class="${iconClass}"></i> ${message}`)
                .fadeIn(300);
            
            // Vibrate on mobile if supported
            if (navigator.vibrate) {
                if (type === 'success') {
                    navigator.vibrate(100);
                } else if (type === 'danger') {
                    navigator.vibrate([100, 100, 100]);
                }
            }
            
            // Auto-hide after 3 seconds
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
                case 'confirmed': return 'primary';
                default: return 'secondary';
            }
        }

        function getStatusText(status) {
            switch(status) {
                case 'found': return 'พบในระบบ';
                case 'not_in_system': return 'ไม่มีในระบบ';
                case 'duplicate': return 'สแกนซ้ำ';
                case 'confirmed': return 'ยืนยันแล้ว';
                default: return 'ไม่ทราบสถานะ';
            }
        }

        function getIconClass(type) {
            switch(type) {
                case 'success': return 'fas fa-check-circle';
                case 'danger': return 'fas fa-exclamation-triangle';
                case 'warning': return 'fas fa-exclamation-circle';
                case 'info': return 'fas fa-info-circle';
                default: return 'fas fa-info-circle';
            }
        }

        function submitForApproval() {
            if (!confirm('ต้องการส่งผลการตรวจนับเพื่อให้แอดมินตรวจสอบหรือไม่?\n\nเมื่อส่งแล้ว session จะปิดและไม่สามารถสแกนเพิ่มได้')) {
                return;
            }

            // Show loading
            const submitBtn = document.querySelector('button[onclick="submitForApproval()"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังส่ง...';
            submitBtn.disabled = true;
            
            // Make AJAX request to submit
            fetch('{{ route("admin.stock-checks.submit", $stockCheck) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    notes: 'ส่งจากหน้าสแกน'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showAlert('ส่งผลการตรวจนับเพื่อตรวจสอบเรียบร้อยแล้ว', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.stock-check-submissions.index") }}';
                    }, 2000);
                } else {
                    showAlert(data.message || 'เกิดข้อผิดพลาด', 'danger');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('เกิดข้อผิดพลาดในการส่งข้อมูล: ' + error.message, 'danger');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        function completeSession() {
            if (confirm('ต้องการปิด Session นี้หรือไม่?')) {
                window.location.href = '{{ route("admin.stock-checks.show", $stockCheck) }}';
            }
        }

        // Camera scanning with basic implementation
        $('#camera-scan-btn').on('click', function() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'environment', // Use back camera
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    } 
                })
                .then(function(stream) {
                    $('#camera-container').show();
                    const video = document.getElementById('camera-preview');
                    video.srcObject = stream;
                    video.play();
                    
                    $('#camera-scan-btn').text('ปิดกล้อง').removeClass('btn-info').addClass('btn-danger');
                    $('#camera-scan-btn').off('click').on('click', function() {
                        stream.getTracks().forEach(track => track.stop());
                        $('#camera-container').hide();
                        $('#camera-scan-btn').text('เปิดกล้องสแกน QR').removeClass('btn-danger').addClass('btn-info');
                        setupCameraButton();
                    });
                })
                .catch(function(err) {
                    showAlert('ไม่สามารถเปิดกล้องได้: ' + err.message, 'warning');
                });
            } else {
                showAlert('เบราว์เซอร์ไม่รองรับการใช้กล้อง', 'warning');
            }
        });
        
        function setupCameraButton() {
            $('#camera-scan-btn').off('click').on('click', function() {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            facingMode: 'environment',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        } 
                    })
                    .then(function(stream) {
                        $('#camera-container').show();
                        const video = document.getElementById('camera-preview');
                        video.srcObject = stream;
                        video.play();
                        
                        $('#camera-scan-btn').html('<i class="fas fa-times"></i> ปิดกล้อง')
                            .removeClass('btn-info').addClass('btn-danger');
                        
                        $('#camera-scan-btn').off('click').on('click', function() {
                            stream.getTracks().forEach(track => track.stop());
                            $('#camera-container').hide();
                            $('#camera-scan-btn').html('<i class="fas fa-camera"></i> เปิดกล้องสแกน QR')
                                .removeClass('btn-danger').addClass('btn-info');
                            setupCameraButton();
                        });
                    })
                    .catch(function(err) {
                        showAlert('ไม่สามารถเปิดกล้องได้: ' + err.message, 'warning');
                    });
                } else {
                    showAlert('เบราว์เซอร์นี้ไม่รองรับการใช้กล้อง\nกรุณาใช้เครื่องยิง QR/Barcode แทน', 'info');
                }
            });
        }
        
        // Initialize camera button
        setupCameraButton();
        
        // Prevent screen sleep on mobile
        if ('wakeLock' in navigator) {
            navigator.wakeLock.request('screen').catch(err => {
                console.log('Wake lock failed:', err);
            });
        }
    </script>
    
    <!-- Moment.js for time formatting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/th.min.js"></script>
    <script>
        moment.locale('th');
    </script>
@stop