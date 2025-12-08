@extends('adminlte::page')

@section('title', 'ตรวจนับสต๊อก')

@section('content_header')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-2">
        <div>
            <h1 class="h4 h-sm-2 mb-1 mb-sm-0">ตรวจนับสต๊อก</h1>
        </div>
        <div class="d-block d-sm-none w-100 mt-2">
            <a href="{{ route('admin.stock-checks.create') }}" class="btn btn-success btn-block btn-lg">
                <i class="fas fa-plus"></i> เริ่มตรวจนับใหม่
            </a>
        </div>
        <div class="d-none d-sm-block">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item active">ตรวจนับสต๊อก</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Mobile Filter -->
    <div class="card mb-3">
        <div class="card-header py-2">
            <button class="btn btn-link text-left p-0 w-100" type="button" data-toggle="collapse" data-target="#filterCollapse">
                <i class="fas fa-filter"></i> ตัวกรอง
                <i class="fas fa-chevron-down float-right mt-1"></i>
            </button>
        </div>
        <div class="collapse" id="filterCollapse">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.stock-checks.index') }}">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">สถานะ</label>
                        <select name="status" class="form-control form-control-lg">
                            <option value="">ทั้งหมด</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">คลัง</label>
                        <select name="warehouse_id" class="form-control form-control-lg">
                            <option value="">ทั้งหมด</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block btn-lg">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                        </div>
                        <div class="col-6 d-none d-sm-block">
                            <a href="{{ route('admin.stock-checks.create') }}" class="btn btn-success btn-block btn-lg">
                                <i class="fas fa-plus"></i> เริ่มตรวจนับใหม่
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Sessions List -->
    @if($sessions->count() > 0)
        @foreach($sessions as $session)
            <div class="card mb-3 session-card" onclick="window.location='{{ route('admin.stock-checks.show', $session) }}'">
                <div class="card-body p-3">
                    <!-- Header Row -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="font-weight-bold mb-0 text-primary mr-2">{{ $session->session_code }}</h6>
                                @switch($session->status)
                                    @case('active')
                                        <span class="badge badge-success">กำลังดำเนินการ</span>
                                        @break
                                    @case('completed')
                                        <span class="badge badge-primary">เสร็จสิ้น</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge badge-danger">ยกเลิก</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $session->status }}</span>
                                @endswitch
                            </div>
                            <h5 class="mb-1 font-weight-normal">{{ $session->title }}</h5>
                            @if($session->description)
                                <p class="text-muted small mb-0">{{ Str::limit($session->description, 80) }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Info Section -->
                    <div class="mb-3">
                        <div class="row text-sm mb-2">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-warehouse text-muted mr-1"></i>
                                    <span class="text-muted">คลัง:</span>
                                    <span class="ml-1 font-weight-medium">{{ Str::limit(optional($session->warehouse)->name ?? '-', 20) }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-tags text-muted mr-1"></i>
                                    <span class="text-muted">หมวด:</span>
                                    <span class="ml-1 font-weight-medium">{{ Str::limit($session->category->name ?? 'ทั้งหมด', 15) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row text-sm">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock text-muted mr-1"></i>
                                    <span class="text-muted">เริ่ม:</span>
                                    <span class="ml-1 font-weight-medium">{{ $session->started_at->format('d/m H:i') }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user text-muted mr-1"></i>
                                    <span class="text-muted">โดย:</span>
                                    <span class="ml-1 font-weight-medium">{{ Str::limit(optional($session->creator)->name ?? '-', 12) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($session->completed_at)
                            <div class="mt-2">
                                <div class="d-flex align-items-center text-success text-sm">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <span>เสร็จเมื่อ: {{ $session->completed_at->format('d/m H:i') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-3">
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Primary Actions -->
                            <div class="btn-group flex-grow-1 mr-2" role="group">
                                <a href="{{ route('admin.stock-checks.show', $session) }}" class="btn btn-outline-info btn-sm" onclick="event.stopPropagation()" title="ดูรายละเอียด">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($session->status === 'active')
                                    <a href="{{ route('admin.stock-checks.scan', $session) }}" class="btn btn-success btn-sm" onclick="event.stopPropagation()" title="เริ่มสแกน">
                                        <i class="fas fa-qrcode"></i> สแกน
                                    </a>
                                @endif
                                @if($session->status === 'completed')
                                    <a href="{{ route('admin.stock-checks.report', $session) }}" class="btn btn-primary btn-sm" onclick="event.stopPropagation()" title="รายงาน">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                @endif
                            </div>
                            
                            <!-- Admin Menu Dropdown -->
                            @if(auth()->user()->isAdmin())
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton-{{ $session->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="event.stopPropagation(); toggleDropdown('{{ $session->id }}')" title="ตัวเลือกจัดการ">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton-{{ $session->id }}" id="dropdown-{{ $session->id }}">
                                        <a class="dropdown-item" href="{{ route('admin.stock-checks.edit', $session) }}" onclick="event.stopPropagation()">
                                            <i class="fas fa-edit text-warning"></i> แก้ไขข้อมูล
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <button class="dropdown-item text-danger" onclick="confirmDelete('{{ $session->id }}', '{{ $session->title }}'); event.stopPropagation()">
                                            <i class="fas fa-trash"></i> ลบรายการ
                                        </button>
                                        <div class="dropdown-divider"></div>
                                        <button class="dropdown-item" onclick="shareSession('{{ $session->session_code }}'); event.stopPropagation()">
                                            <i class="fas fa-share-alt text-info"></i> แชร์รายการ
                                        </button>
                                    </div>
                                </div>
                            @else
                                <!-- Share button for non-admin -->
                                <button class="btn btn-outline-secondary btn-sm" onclick="shareSession('{{ $session->session_code }}'); event.stopPropagation()" title="แชร์">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $sessions->withQueryString()->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-check fa-4x text-muted mb-3"></i>
                <h5 class="text-muted mb-3">ไม่พบข้อมูลการตรวจนับสต๊อก</h5>
                <p class="text-muted mb-4">เริ่มต้นการตรวจนับสต๊อกครั้งแรกของคุณ</p>
                <a href="{{ route('admin.stock-checks.create') }}" class="btn btn-success btn-lg">
                    <i class="fas fa-plus"></i> เริ่มตรวจนับใหม่
                </a>
            </div>
        </div>
    @endif
@stop

@section('css')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <style>
        /* Mobile-First Design */
        body {
            font-size: 16px; /* Prevent zoom on iOS */
        }
        
        /* Card Styles */
        .session-card {
            border-left: 4px solid #007bff;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        
        .session-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            border-left-color: #0056b3;
        }
        
        .session-card:active {
            transform: translateY(0);
        }
        
        /* Action Buttons */
        .btn-group .btn {
            border-radius: 0;
        }
        
        .btn-group .btn:first-child {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
        }
        
        .btn-group .btn:last-child {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
        }
        
        /* Dropdown improvements */
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        /* Badge Styles */
        .badge-lg {
            font-size: 0.9rem;
            padding: 0.4rem 0.6rem;
        }
        
        /* Touch-friendly buttons */
        .btn {
            min-height: 44px;
            border-radius: 8px;
        }
        
        .btn-sm {
            min-height: 38px;
            font-size: 0.85rem;
        }
        
        /* Form controls */
        .form-control {
            min-height: 44px;
            border-radius: 8px;
            font-size: 16px; /* Prevent zoom */
        }
        
        .form-control-lg {
            min-height: 50px;
            font-size: 18px;
        }
        
        /* Text sizes */
        .text-sm {
            font-size: 0.875rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .container-fluid {
                padding: 10px;
            }
            
            .card {
                border-radius: 12px;
                margin-bottom: 15px;
            }
            
            .h4 {
                font-size: 1.3rem;
            }
            
            /* Hide desktop elements */
            .d-none.d-sm-block {
                display: none !important;
            }
        }
        
        /* Status indicators */
        .badge-success { background-color: #28a745 !important; }
        .badge-primary { background-color: #007bff !important; }
        .badge-danger { background-color: #dc3545 !important; }
        .badge-warning { background-color: #ffc107 !important; color: #212529 !important; }
        
        /* Loading states */
        .btn:disabled {
            opacity: 0.6;
        }
    </style>
@stop

@section('js')
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-exclamation-triangle"></i> ยืนยันการลบ
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                        <h5>ต้องการลบรายการตรวจนับนี้หรือไม่?</h5>
                        <p class="text-muted mb-0">การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                    </div>
                    <div class="alert alert-warning">
                        <strong>รายการ:</strong> <span id="deleteItemName"></span><br>
                        <small>ข้อมูลการสแกนทั้งหมดจะถูกลบด้วย</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> ยกเลิก
                    </button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> ลบรายการ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Share functionality
        function shareSession(sessionCode) {
            if (navigator.share) {
                navigator.share({
                    title: 'Stock Check Session',
                    text: 'รหัสการตรวจนับสต๊อก: ' + sessionCode,
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                navigator.clipboard.writeText(sessionCode).then(function() {
                    alert('คัดลอกรหัส Session แล้ว: ' + sessionCode);
                });
            }
        }
        
        // Toggle dropdown manually
        function toggleDropdown(sessionId) {
            $('.dropdown-menu').not('#dropdown-' + sessionId).removeClass('show');
            $('#dropdown-' + sessionId).toggleClass('show');
        }
        
        // Delete confirmation
        function confirmDelete(sessionId, sessionTitle) {
            $('#deleteItemName').text(sessionTitle);
            $('#deleteForm').attr('action', '/admin/stock-checks/' + sessionId);
            $('#deleteModal').modal('show');
        }
        
        // Smooth scrolling for mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Add ripple effect to cards
            $('.session-card').on('touchstart', function() {
                $(this).addClass('shadow-lg');
            }).on('touchend', function() {
                var self = $(this);
                setTimeout(function() {
                    self.removeClass('shadow-lg');
                }, 150);
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu').removeClass('show');
                }
            });
            
            // Handle delete form submission
            $('#deleteForm').on('submit', function(e) {
                var btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังลบ...');
            });
        });
        
        // Show success/error messages if any
        @if(session('success'))
            $(document).ready(function() {
                toastr.success('{{ session('success') }}');
            });
        @endif
        
        @if(session('error'))
            $(document).ready(function() {
                toastr.error('{{ session('error') }}');
            });
        @endif
    </script>
@stop