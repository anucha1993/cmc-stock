@extends('adminlte::page')

@section('title', 'สแกน Barcode - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>สแกน Barcode - {{ $deliveryNote->delivery_number }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.delivery-notes.index') }}">ใบตัดสต็อก</a></li>
                <li class="breadcrumb-item active">สแกน</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- ช่องสแกน Barcode -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-barcode"></i> สแกน Barcode</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>ยิง Barcode ที่นี่</label>
                        <input type="text" id="barcode-input" class="form-control form-control-lg" 
                               placeholder="กด Enter หลังสแกน Barcode" autofocus>
                    </div>

                    <div id="scan-result" class="mt-3"></div>

                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> วิธีใช้งาน</h5>
                        <ol class="mb-0">
                            <li>คลิกที่ช่องด้านบน</li>
                            <li>ยิง Barcode ของสินค้าที่ต้องการตัดสต็อก</li>
                            <li>กด Enter หรือ ยิงต่อเลย</li>
                            <li>ตรวจสอบรายการที่สแกนด้านขวา</li>
                            <li>เมื่อสแกนครบแล้ว กดปุ่ม "เสร็จสิ้นการสแกน"</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- ข้อมูลลูกค้า -->
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title"><i class="fas fa-user"></i> ข้อมูลลูกค้า</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td width="120"><strong>ลูกค้า:</strong></td>
                            <td>{{ $deliveryNote->customer_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>วันที่จัดส่ง:</strong></td>
                            <td>{{ $deliveryNote->delivery_date->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- สรุปรายการ -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> สรุปรายการสแกน</h3>
                </div>
                <div class="card-body">
                    <div id="summary-items">
                        @foreach($deliveryNote->items as $item)
                        <div class="item-summary mb-3 p-3 border rounded" id="summary-{{ $item->id }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $item->product->name }}</h6>
                                    <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                </div>
                                <div class="text-right">
                                    <h4 class="mb-0">
                                        <span class="scanned-count" id="count-{{ $item->id }}">{{ $item->scanned_quantity }}</span>
                                        <span class="text-muted">/ {{ $item->quantity }}</span>
                                    </h4>
                                </div>
                            </div>
                            
                            <div class="progress mt-2" style="height: 25px;">
                                <div class="progress-bar bg-success progress-bar-animated" 
                                     id="progress-{{ $item->id }}"
                                     style="width: {{ $item->completion_percentage }}%">
                                    <span id="percent-{{ $item->id }}">{{ number_format($item->completion_percentage, 0) }}%</span>
                                </div>
                            </div>

                            <!-- รายการที่สแกนแล้ว -->
                            <div class="mt-2" id="scanned-list-{{ $item->id }}">
                                @if($item->scanned_items && count($item->scanned_items) > 0)
                                    @foreach($item->scanned_items as $scanned)
                                        <span class="badge badge-success mr-1 scanned-badge" data-barcode="{{ $scanned['barcode'] }}">
                                            {{ $scanned['barcode'] }}
                                            <button type="button" class="btn-close-badge" onclick="removeScan({{ $item->id }}, '{{ $scanned['barcode'] }}')">×</button>
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- ปุ่มดำเนินการ -->
            <div class="card">
                <div class="card-body">
                    <button type="button" class="btn btn-success btn-lg btn-block" id="finish-btn">
                        <i class="fas fa-check-double"></i> เสร็จสิ้นการสแกน
                    </button>
                    <a href="{{ route('admin.delivery-notes.show', $deliveryNote->id) }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> กลับ
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
#barcode-input {
    font-size: 1.5rem;
    font-weight: bold;
    text-align: center;
}

.item-summary {
    background: #f8f9fa;
    transition: all 0.3s;
}

.item-summary.completed {
    background: #d4edda;
    border-color: #28a745 !important;
}

.scanned-badge {
    font-size: 0.9rem;
    padding: 0.4rem 0.6rem;
    margin-bottom: 0.3rem;
}

.btn-close-badge {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    line-height: 1;
    padding: 0 0.3rem;
    cursor: pointer;
    opacity: 0.7;
}

.btn-close-badge:hover {
    opacity: 1;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse {
    animation: pulse 0.5s ease-in-out;
}
</style>
@stop

@section('js')
<script>
const deliveryNoteId = {{ $deliveryNote->id }};
let isScanning = false;

// Focus on barcode input
$('#barcode-input').focus();

// Handle barcode scan
$('#barcode-input').on('keypress', function(e) {
    if (e.which === 13) { // Enter key
        e.preventDefault();
        const barcode = $(this).val().trim();
        
        if (barcode) {
            scanBarcode(barcode);
            $(this).val('');
        }
    }
});

// Scan barcode
function scanBarcode(barcode) {
    if (isScanning) return;
    isScanning = true;

    $.ajax({
        url: `/admin/delivery-notes/${deliveryNoteId}/scan`,
        method: 'POST',
        data: {
            barcode: barcode,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                // ถ้าสแกนเกิน แสดงเตือนสีเหลือง
                if (response.is_over_scanned) {
                    showWarning(response.message);
                } else {
                    showSuccess(response.message);
                }
                updateProductRow(response.data);
                playSuccessSound();
            } else {
                showError(response.message);
                playErrorSound();
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด';
            showError(message);
            playErrorSound();
        },
        complete: function() {
            isScanning = false;
            $('#barcode-input').focus();
        }
    });
}

// Update product row after scan
function updateProductRow(data) {
    // ค้นหา item ที่ตรงกับสินค้า (ต้องหาจาก product name)
    let itemId = null;
    @foreach($deliveryNote->items as $item)
        if ('{{ $item->product->name }}' === data.product_name) {
            itemId = {{ $item->id }};
        }
    @endforeach

    if (!itemId) return;

    // อัปเดตตัวเลข
    $(`#count-${itemId}`).text(data.scanned_quantity);
    
    // อัปเดต progress bar
    const percent = data.completion_percentage;
    $(`#progress-${itemId}`).css('width', percent + '%');
    $(`#percent-${itemId}`).text(Math.round(percent) + '%');
    
    // ถ้าสแกนเกิน เปลี่ยน progress bar เป็นสีแดง
    if (data.scanned_quantity > data.total_quantity) {
        $(`#progress-${itemId}`).removeClass('bg-success bg-info').addClass('bg-danger');
        $(`#summary-${itemId}`).addClass('border-danger');
    }
    
    // เพิ่ม badge
    const badgeColor = data.scanned_quantity > data.total_quantity ? 'danger' : 'success';
    const badge = `<span class="badge badge-${badgeColor} mr-1 scanned-badge pulse" data-barcode="${data.serial_number}">
        ${data.serial_number}
        <button type="button" class="btn-close-badge" onclick="removeScan(${itemId}, '${data.serial_number}')">×</button>
    </span>`;
    $(`#scanned-list-${itemId}`).append(badge);
    
    // ถ้าครบแล้ว
    if (percent >= 100) {
        $(`#summary-${itemId}`).addClass('completed');
        $(`#progress-${itemId}`).removeClass('progress-bar-animated');
    }

    // Scroll to item
    $(`#summary-${itemId}`)[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Remove scanned item
function removeScan(itemId, barcode) {
    if (!confirm('ต้องการลบรายการสแกนนี้?')) return;

    $.ajax({
        url: `/admin/delivery-notes/${deliveryNoteId}/scan`,
        method: 'DELETE',
        data: {
            item_id: itemId,
            barcode: barcode,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                // ลบ badge
                $(`.scanned-badge[data-barcode="${barcode}"]`).fadeOut(300, function() {
                    $(this).remove();
                });
                
                // อัปเดตตัวเลข (ลด 1)
                const currentCount = parseInt($(`#count-${itemId}`).text());
                const totalCount = parseInt($(`#count-${itemId}`).next('.text-muted').text().replace('/', '').trim());
                const newCount = currentCount - 1;
                
                $(`#count-${itemId}`).text(newCount);
                
                // อัปเดต progress
                const newPercent = (newCount / totalCount) * 100;
                $(`#progress-${itemId}`).css('width', newPercent + '%');
                $(`#percent-${itemId}`).text(Math.round(newPercent) + '%');
                
                // ลบ completed class
                if (newPercent < 100) {
                    $(`#summary-${itemId}`).removeClass('completed');
                    $(`#progress-${itemId}`).addClass('progress-bar-animated');
                }
                
                showSuccess(response.message);
            }
        },
        error: function() {
            showError('ไม่สามารถลบรายการได้');
        }
    });
}

// Finish scanning
$('#finish-btn').on('click', function() {
    window.location.href = '{{ route("admin.delivery-notes.show", $deliveryNote->id) }}';
});

// Show messages
function showSuccess(message) {
    $('#scan-result').html(`
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> ${message}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    `);
    
    setTimeout(() => {
        $('#scan-result .alert').fadeOut();
    }, 3000);
}

function showError(message) {
    $('#scan-result').html(`
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-times-circle"></i> ${message}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    `);
}

function showWarning(message) {
    $('#scan-result').html(`
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> ${message}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    `);
    
    setTimeout(() => {
        $('#scan-result .alert').fadeOut();
    }, 5000);
}

// Sound effects
function playSuccessSound() {
    // สามารถเพิ่มเสียงได้ถ้าต้องการ
    console.log('Success!');
}

function playErrorSound() {
    console.log('Error!');
}

// Prevent accidental page leave
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = '';
});
</script>
@stop
