@extends('adminlte::page')

@section('title', 'ตรวจสอบและอนุมัติ - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>ตรวจสอบและอนุมัติการตัดสต็อก</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.delivery-notes.index') }}">ใบตัดสต็อก</a></li>
                <li class="breadcrumb-item active">ตรวจสอบ</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- สรุปใบตัดสต็อก -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-invoice"></i> {{ $deliveryNote->delivery_number }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>ข้อมูลลูกค้า</h5>
                    <p>
                        <strong>{{ $deliveryNote->customer_name }}</strong><br>
                        @if($deliveryNote->customer_phone)
                            <i class="fas fa-phone"></i> {{ $deliveryNote->customer_phone }}<br>
                        @endif
                        @if($deliveryNote->customer_address)
                            {{ $deliveryNote->customer_address }}
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <h5>ข้อมูลการจัดส่ง</h5>
                    <p>
                        <strong>วันที่:</strong> {{ $deliveryNote->delivery_date->format('d/m/Y') }}<br>
                        <strong>คลัง:</strong> {{ $deliveryNote->warehouse->name }}<br>
                        <strong>ยอดรวม:</strong> {{ number_format($deliveryNote->total_amount, 2) }} บาท
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ตรวจสอบความถูกต้อง -->
    @if(empty($discrepancies))
        <!-- ตรงทุกรายการ -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-circle"></i> ผลการตรวจสอบ</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h4><i class="fas fa-check-circle"></i> ตรงทุกรายการ!</h4>
                    <p class="mb-0">รายการสินค้าที่สแกนตรงกับใบตัดสต็อกทุกรายการ สามารถอนุมัติและตัดสต็อกได้เลย</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>สินค้า</th>
                                <th class="text-center">จำนวนที่ต้องการ</th>
                                <th class="text-center">จำนวนที่สแกน</th>
                                <th class="text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveryNote->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->product->name }}</strong>
                                    <br><small class="text-muted">{{ $item->product->sku }}</small>
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center">{{ $item->scanned_quantity }}</td>
                                <td class="text-center">
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> ตรง
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ปุ่มอนุมัติ -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.delivery-notes.approve', $deliveryNote->id) }}" method="POST" id="approve-form">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check-double"></i> อนุมัติและตัดสต็อกเลย
                    </button>
                    <a href="{{ route('admin.delivery-notes.scan', $deliveryNote->id) }}" class="btn btn-warning">
                        <i class="fas fa-barcode"></i> กลับไปสแกนเพิ่ม
                    </a>
                    <a href="{{ route('admin.delivery-notes.show', $deliveryNote->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> กลับ
                    </a>
                </form>
            </div>
        </div>

    @else
        <!-- มีความไม่ตรงกัน -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> พบความไม่ตรงกัน!</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> ตรวจพบความไม่ตรงกัน {{ count($discrepancies) }} รายการ</h4>
                    <p class="mb-0">กรุณาตรวจสอบรายการด้านล่างและตัดสินใจว่าจะอนุมัติหรือไม่</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>สินค้า</th>
                                <th class="text-center">จำนวนที่ต้องการ</th>
                                <th class="text-center">จำนวนที่สแกนจริง</th>
                                <th class="text-center">ส่วนต่าง</th>
                                <th class="text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($discrepancies as $index => $disc)
                            <tr class="table-warning">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $disc['product_name'] }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $disc['planned'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $disc['status'] === 'over' ? 'warning' : 'danger' }}">
                                        {{ $disc['scanned'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $disc['difference'] > 0 ? 'warning' : 'danger' }}">
                                        {{ $disc['difference'] > 0 ? '+' : '' }}{{ $disc['difference'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($disc['status'] === 'over')
                                        <span class="text-warning"><i class="fas fa-arrow-up"></i> เกินกำหนด</span>
                                    @else
                                        <span class="text-danger"><i class="fas fa-arrow-down"></i> ขาดจากกำหนด</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach

                            <!-- รายการที่ตรง -->
                            @foreach($deliveryNote->items as $item)
                                @php
                                    $hasDiscrepancy = false;
                                    foreach($discrepancies as $disc) {
                                        if($disc['product_id'] === $item->product_id) {
                                            $hasDiscrepancy = true;
                                            break;
                                        }
                                    }
                                @endphp
                                
                                @if(!$hasDiscrepancy)
                                <tr>
                                    <td>{{ count($discrepancies) + $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">{{ $item->scanned_quantity }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-success">0</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-success"><i class="fas fa-check"></i> ตรง</span>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- คำอธิบาย -->
                <div class="mt-3">
                    <h5><i class="fas fa-info-circle"></i> คำอธิบาย</h5>
                    <ul>
                        <li><strong>เกินกำหนด:</strong> สแกนมากกว่าที่ต้องการ (อาจส่งสินค้าเกิน)</li>
                        <li><strong>ขาดจากกำหนด:</strong> สแกนน้อยกว่าที่ต้องการ (ลูกค้าจะได้สินค้าไม่ครบ)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ตัวเลือก -->
        <div class="card">
            <div class="card-body">
                <h5><i class="fas fa-question-circle"></i> คุณต้องการดำเนินการอย่างไร?</h5>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <h5 class="card-title"><i class="fas fa-barcode"></i> กลับไปสแกนใหม่</h5>
                                <p class="card-text">แนะนำถ้ายังไม่แน่ใจ หรือต้องการตรวจสอบอีกครั้ง</p>
                                <a href="{{ route('admin.delivery-notes.scan', $deliveryNote->id) }}" class="btn btn-warning btn-lg">
                                    <i class="fas fa-redo"></i> สแกนใหม่
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> บังคับอนุมัติ</h5>
                                <p class="card-text">ยืนยันว่าต้องการตัดสต็อกตามที่สแกนจริง (มีความไม่ตรงกัน)</p>
                                <form action="{{ route('admin.delivery-notes.approve', $deliveryNote->id) }}" method="POST" id="force-approve-form">
                                    @csrf
                                    <input type="hidden" name="force_approve" value="1">
                                    <button type="button" class="btn btn-danger btn-lg" onclick="confirmForceApprove()">
                                        <i class="fas fa-check-double"></i> บังคับอนุมัติ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('admin.delivery-notes.show', $deliveryNote->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> กลับ
                    </a>
                </div>
            </div>
        </div>
    @endif
@stop

@section('js')
<script>
// Confirm normal approve
$('#approve-form').on('submit', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: 'ยืนยันการอนุมัติ?',
        text: "ระบบจะทำการตัดสต็อกสินค้าทันที และไม่สามารถยกเลิกได้",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check"></i> ยืนยัน อนุมัติเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});

// Confirm force approve
function confirmForceApprove() {
    Swal.fire({
        title: 'แน่ใจหรือไม่?',
        html: '<p class="text-danger"><strong>คำเตือน:</strong> คุณกำลังจะบังคับอนุมัติทั้งที่มีความไม่ตรงกัน</p>' +
              '<p>ระบบจะตัดสต็อกตามที่สแกนจริง และบันทึกความไม่ตรงกันไว้เป็นหลักฐาน</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-exclamation-triangle"></i> ยืนยัน บังคับอนุมัติ!',
        cancelButtonText: 'ยกเลิก',
        input: 'textarea',
        inputPlaceholder: 'ระบุเหตุผลในการบังคับอนุมัติ (ถ้ามี)',
        inputAttributes: {
            'aria-label': 'ระบุเหตุผล'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // เพิ่มเหตุผลถ้ามี
            if (result.value) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'reason',
                    value: result.value
                }).appendTo('#force-approve-form');
            }
            
            $('#force-approve-form').submit();
        }
    });
}
</script>
@stop
