@extends('adminlte::page')

@section('title', 'รายละเอียดใบตัดสต็อก - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>รายละเอียดใบตัดสต็อก</h1>
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

@section('content')
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {!! session('warning') !!}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- หัวเอกสาร -->
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">
                <i class="fas fa-file-invoice"></i> เลขที่: {{ $deliveryNote->delivery_number }}
            </h3>
            <div class="card-tools">
                <span class="badge badge-{{ $deliveryNote->status_color }} badge-lg">
                    {{ $deliveryNote->status_text }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-user"></i> ข้อมูลลูกค้า</h5>
                    <table class="table table-sm">
                        <tr>
                            <td width="150"><strong>ชื่อลูกค้า:</strong></td>
                            <td>{{ $deliveryNote->customer_name }}</td>
                        </tr>
                        @if($deliveryNote->customer_phone)
                        <tr>
                            <td><strong>เบอร์โทร:</strong></td>
                            <td>{{ $deliveryNote->customer_phone }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h5><i class="fas fa-info-circle"></i> ข้อมูลเอกสาร</h5>
                    <table class="table table-sm">
                        @if($deliveryNote->sales_order_number)
                        <tr>
                            <td width="150"><strong>เลขที่ใบสั่งขาย:</strong></td>
                            <td>{{ $deliveryNote->sales_order_number }}</td>
                        </tr>
                        @endif
                        @if($deliveryNote->quotation_number)
                        <tr>
                            <td><strong>เลขที่ใบเสนอราคา:</strong></td>
                            <td>{{ $deliveryNote->quotation_number }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>วันที่จัดส่ง:</strong></td>
                            <td>{{ $deliveryNote->delivery_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>ผู้สร้าง:</strong></td>
                            <td>{{ $deliveryNote->creator->name }} <small class="text-muted">({{ $deliveryNote->created_at->format('d/m/Y H:i') }})</small></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- สถานะการดำเนินการ -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-primary">สถานะการดำเนินการ</span>
                        </div>
                        
                        <!-- สร้างเอกสาร -->
                        <div>
                            <i class="fas fa-file bg-success"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $deliveryNote->created_at->format('d/m/Y H:i') }}</span>
                                <h3 class="timeline-header">สร้างเอกสาร</h3>
                                <div class="timeline-body">
                                    โดย {{ $deliveryNote->creator->name }}
                                </div>
                            </div>
                        </div>

                        @if($deliveryNote->confirmed_at)
                        <!-- ยืนยัน -->
                        <div>
                            <i class="fas fa-check-circle bg-info"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $deliveryNote->confirmed_at->format('d/m/Y H:i') }}</span>
                                <h3 class="timeline-header">ยืนยันเอกสาร</h3>
                                <div class="timeline-body">
                                    โดย {{ $deliveryNote->confirmer->name }}
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($deliveryNote->scanned_at)
                        <!-- สแกน -->
                        <div>
                            <i class="fas fa-barcode bg-primary"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $deliveryNote->scanned_at->format('d/m/Y H:i') }}</span>
                                <h3 class="timeline-header">สแกน Barcode</h3>
                                <div class="timeline-body">
                                    โดย {{ $deliveryNote->scanner->name }}
                                    <br>สแกนแล้ว: <strong>{{ $deliveryNote->total_scanned }} / {{ $deliveryNote->total_items }}</strong> ชิ้น
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($deliveryNote->approved_at)
                        <!-- อนุมัติ -->
                        <div>
                            <i class="fas fa-check-double bg-success"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $deliveryNote->approved_at->format('d/m/Y H:i') }}</span>
                                <h3 class="timeline-header">อนุมัติและตัดสต็อก</h3>
                                <div class="timeline-body">
                                    โดย {{ $deliveryNote->approver->name }}
                                    @if($deliveryNote->discrepancy_notes)
                                        <br><span class="text-warning"><i class="fas fa-exclamation-triangle"></i> มีความไม่ตรงกัน</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- รายการสินค้า -->
    <div class="card">
        <div class="card-header bg-success">
            <h3 class="card-title">รายการสินค้า</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th >#</th>
                            <th>สินค้า</th>
                            <th class="text-center">จำนวนที่ต้องการ</th>
                            <th  class="text-center">จำนวนที่สแกน</th>
                            <th  class="text-center">สถานะ</th>

                            <th  class="text-center">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveryNote->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                                <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $item->quantity }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $item->scanned_quantity >= $item->quantity ? 'success' : 'warning' }}">
                                    {{ $item->scanned_quantity }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $item->status_color }}">
                                    {{ $item->status_text }}
                                </span>
                            </td>
                         
                            <td class="text-center">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $item->completion_percentage >= 100 ? 'success' : 'warning' }}" 
                                         style="width: {{ min($item->completion_percentage, 100) }}%">
                                        {{ number_format($item->completion_percentage, 0) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        @if($item->scanned_items && count($item->scanned_items) > 0)
                        <tr class="bg-light">
                            <td colspan="8">
                                <small><strong>รายการที่สแกน:</strong></small>
                                <div class="mt-1">
                                    @foreach($item->scanned_items as $scanned)
                                        <span class="badge badge-secondary mr-1">
                                            <i class="fas fa-barcode"></i> {{ $scanned['barcode'] }}
                                            @if(isset($scanned['serial_number']))
                                                | SN: {{ $scanned['serial_number'] }}
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="6" class="text-right"><strong>ยอดรวมทั้งหมด:</strong></td>
                            <td class="text-right"><strong>{{ number_format($deliveryNote->total_amount, 2) }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Discrepancies -->
    @if(!empty($discrepancies))
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> ความไม่ตรงกัน</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>สินค้า</th>
                            <th class="text-center">ที่วางแผน</th>
                            <th class="text-center">ที่สแกนจริง</th>
                            <th class="text-center">ส่วนต่าง</th>
                            <th class="text-center">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($discrepancies as $disc)
                        <tr>
                            <td>{{ $disc['product_name'] }}</td>
                            <td class="text-center">{{ $disc['planned'] }}</td>
                            <td class="text-center">{{ $disc['scanned'] }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $disc['difference'] > 0 ? 'warning' : 'danger' }}">
                                    {{ $disc['difference'] > 0 ? '+' : '' }}{{ $disc['difference'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($disc['status'] === 'over')
                                    <span class="text-warning">เกินกำหนด</span>
                                @else
                                    <span class="text-danger">ขาดจากกำหนด</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- ปุ่มดำเนินการ -->
    <div class="card">
        <div class="card-body">
            <!-- Debug Information -->
            {{-- <div class="alert alert-info mb-3">
                <strong>Debug Info:</strong><br>
                Status: <code>{{ $deliveryNote->status }}</code><br>
                Is Admin: <code>{{ auth()->user()->can('manage-users') ? 'YES' : 'NO' }}</code><br>
                Total Items: {{ $deliveryNote->items->count() }}<br>
                @php
                    $totalScanned = 0;
                    $hasOver = false;
                    foreach($deliveryNote->items as $item) {
                        $totalScanned += $item->scanned_quantity;
                        if($item->scanned_quantity > $item->quantity) {
                            $hasOver = true;
                        }
                    }
                @endphp
                Total Scanned: {{ $totalScanned }}<br>
                Has Over-Scanned: <code>{{ $hasOver ? 'YES' : 'NO' }}</code><br>
                Discrepancies Count: {{ !empty($discrepancies) ? count($discrepancies) : 0 }}
            </div> --}}
            
            @php
                // ตรวจสอบว่ามีการสแกนแล้วหรือไม่
                $totalScannedItems = 0;
                foreach($deliveryNote->items as $item) {
                    $totalScannedItems += $item->scanned_quantity;
                }
                $hasScanned = $totalScannedItems > 0;
            @endphp

            @if(in_array($deliveryNote->status, ['pending', 'confirmed', 'scanned']))
                @if($hasScanned && auth()->user()->can('manage-users'))
                    @php
                        $hasOverScanned = false;
                        foreach($deliveryNote->items as $item) {
                            if($item->scanned_quantity > $item->quantity) {
                                $hasOverScanned = true;
                                break;
                            }
                        }
                    @endphp
                    
                    @if(!empty($discrepancies))
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>พบความไม่ตรงกัน {{ count($discrepancies) }} รายการ</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($discrepancies as $disc)
                                <li>{{ $disc['product_name'] }}: วางแผน {{ $disc['planned'] }}, สแกนได้ {{ $disc['scanned'] }} 
                                    <span class="badge badge-{{ $disc['difference'] > 0 ? 'warning' : 'danger' }}">
                                        {{ $disc['difference'] > 0 ? '+' : '' }}{{ $disc['difference'] }}
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('admin.delivery-notes.approve', $deliveryNote->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @if($hasOverScanned)
                            <button type="submit" class="btn btn-warning" onclick="return confirm('มีรายการที่สแกนเกิน คุณแน่ใจว่าต้องการบังคับอนุมัติ?')">
                                <i class="fas fa-exclamation-triangle"></i> บังคับอนุมัติและตัดสต็อก
                            </button>
                            <input type="hidden" name="force_approve" value="1">
                        @else
                            <button type="submit" class="btn btn-success" onclick="return confirm('ยืนยันการอนุมัติและตัดสต็อก?')">
                                <i class="fas fa-check-double"></i> อนุมัติและตัดสต็อก
                            </button>
                        @endif
                    </form>
                @endif
                
                <a href="{{ route('admin.delivery-notes.scan', $deliveryNote->id) }}" class="btn btn-primary">
                    <i class="fas fa-barcode"></i> {{ $hasScanned ? 'สแกนเพิ่ม' : 'สแกน Barcode' }}
                </a>
                
                @if($deliveryNote->status === 'pending')
                    <a href="{{ route('admin.delivery-notes.edit', $deliveryNote->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> แก้ไข
                    </a>
                @endif
            @endif

            @if($deliveryNote->status === 'scanned')
                @if(!$hasScanned)
                    <a href="{{ route('admin.delivery-notes.scan', $deliveryNote->id) }}" class="btn btn-primary">
                        <i class="fas fa-barcode"></i> สแกนเพิ่ม
                    </a>
                @endif
            @endif

            <a href="{{ route('admin.delivery-notes.print', $deliveryNote->id) }}" class="btn btn-secondary" target="_blank">
                <i class="fas fa-print"></i> พิมพ์
            </a>

            <a href="{{ route('admin.delivery-notes.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> กลับ
            </a>
        </div>
    </div>
@stop
