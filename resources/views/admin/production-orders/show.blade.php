@extends('adminlte::page')

@section('title', 'รายละเอียดใบสั่งผลิต')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>รายละเอียดใบสั่งผลิต</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.production-orders.index') }}">ใบสั่งผลิต</a></li>
                <li class="breadcrumb-item active">{{ $productionOrder->order_code }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Order Info -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลใบสั่งผลิต: {{ $productionOrder->order_code }}</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $productionOrder->status_color }} badge-lg">
                            {{ $productionOrder->status_text }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">รหัสสั่งผลิต:</th>
                                    <td><strong>{{ $productionOrder->order_code }}</strong></td>
                                </tr>
                                <tr>
                                    <th>ประเภท:</th>
                                    <td>
                                        @if($productionOrder->order_type === 'package')
                                            <i class="fas fa-box-open text-primary"></i> สั่งผลิตจากแพ
                                        @elseif($productionOrder->order_type === 'multiple')
                                            <i class="fas fa-cubes text-success"></i> สั่งผลิตหลายรายการ
                                        @else
                                            <i class="fas fa-cube text-info"></i> สั่งผลิตสินค้าเดี่ยว
                                        @endif
                                    </td>
                                </tr>
                                @if($productionOrder->order_type === 'package' && $productionOrder->package)
                                    <tr>
                                        <th>แพสินค้า:</th>
                                        <td>
                                            <strong>{{ $productionOrder->package->name }}</strong><br>
                                            <small class="text-muted">{{ $productionOrder->package->code }}</small>
                                        </td>
                                    </tr>
                                @elseif($productionOrder->order_type === 'single' && $productionOrder->product)
                                    <tr>
                                        <th>สินค้า:</th>
                                        <td>
                                            <strong>{{ $productionOrder->product->name }}</strong><br>
                                            <small class="text-muted">SKU: {{ $productionOrder->product->sku }}</small><br>
                                            @if($productionOrder->product->category)
                                                <small class="text-muted">หมวดหมู่: {{ $productionOrder->product->category->name }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>คลังปลายทาง:</th>
                                    <td>
                                        <span class="badge badge-info">{{ $productionOrder->targetWarehouse->code }}</span>
                                        {{ $productionOrder->targetWarehouse->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>จำนวนที่สั่งผลิต:</th>
                                    <td><strong class="text-primary">{{ number_format($productionOrder->quantity) }} รายการ</strong></td>
                                </tr>
                                @if($productionOrder->status === 'completed')
                                    <tr>
                                        <th>จำนวนที่ผลิตจริง:</th>
                                        <td>
                                            <strong class="text-success">{{ number_format($productionOrder->produced_quantity) }} รายการ</strong>
                                            @if($productionOrder->produced_quantity < $productionOrder->quantity)
                                                <br><small class="text-warning">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    ผลิตได้น้อยกว่าที่สั่ง {{ number_format($productionOrder->quantity - $productionOrder->produced_quantity) }} รายการ
                                                </small>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">ความสำคัญ:</th>
                                    <td>
                                        <span class="badge badge-{{ $productionOrder->priority_color }}">
                                            {{ $productionOrder->priority_text }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>วันที่ต้องการ:</th>
                                    <td>
                                        {{ $productionOrder->due_date->format('d/m/Y') }}<br>
                                        <small class="text-muted">{{ $productionOrder->due_date->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>ผู้ขอ:</th>
                                    <td>{{ $productionOrder->requestedBy->name ?? '-' }}</td>
                                </tr>
                                @if($productionOrder->approved_by)
                                <tr>
                                    <th>ผู้อนุมัติ:</th>
                                    <td>{{ $productionOrder->approvedBy->name ?? '-' }}</td>
                                </tr>
                                @endif
                                @if($productionOrder->assigned_to)
                                <tr>
                                    <th>ผู้รับผิดชอบ:</th>
                                    <td>{{ $productionOrder->assignedTo->name ?? '-' }}</td>
                                </tr>
                                @endif
                                @if($productionOrder->start_date)
                                <tr>
                                    <th>วันที่เริ่มผลิต:</th>
                                    <td>{{ $productionOrder->start_date->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                                @if($productionOrder->completion_date)
                                <tr>
                                    <th>วันที่เสร็จสิ้น:</th>
                                    <td>{{ $productionOrder->completion_date->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>วันที่สร้าง:</th>
                                    <td>{{ $productionOrder->requested_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($productionOrder->description)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>หมายเหตุ</h5>
                                <p>{{ $productionOrder->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Production Items -->
            @if($productionOrder->order_type === 'multiple' || $productionOrder->order_type === 'package')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">รายการสินค้าที่สั่งผลิต</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>สินค้า</th>
                                        <th width="15%">จำนวนที่สั่ง</th>
                                        @if($productionOrder->status === 'completed')
                                            <th width="15%">ผลิตจริง</th>
                                        @endif
                                        <th width="10%">ต้นทุน/หน่วย</th>
                                        <th width="10%">ต้นทุนรวม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productionOrder->items as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->product->name }}</strong><br>
                                                <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary">{{ number_format($item->quantity) }}</span>
                                            </td>
                                            @if($productionOrder->status === 'completed')
                                                <td class="text-center">
                                                    <span class="badge badge-success">{{ number_format($item->produced_quantity) }}</span>
                                                </td>
                                            @endif
                                            <td class="text-right">
                                                @if($item->unit_cost)
                                                    ฿{{ number_format($item->unit_cost, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($item->total_cost)
                                                    ฿{{ number_format($item->total_cost, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $productionOrder->status === 'completed' ? '6' : '5' }}" class="text-center text-muted">ไม่มีรายการสินค้า</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($productionOrder->items->isNotEmpty())
                                    <tfoot>
                                        <tr class="table-info">
                                            <th>รวม</th>
                                            <th class="text-center">{{ number_format($productionOrder->items->sum('quantity')) }}</th>
                                            @if($productionOrder->status === 'completed')
                                                <th class="text-center">{{ number_format($productionOrder->items->sum('produced_quantity')) }}</th>
                                            @endif
                                            <th></th>
                                            <th class="text-right">
                                                ฿{{ number_format($productionOrder->items->sum('total_cost'), 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Actions Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">จัดการสถานะ</h3>
                </div>
                <div class="card-body">
                    @if($productionOrder->status === 'pending')
                        <button type="button" class="btn btn-info btn-block mb-2" onclick="updateStatus('in_production')">
                            <i class="fas fa-play"></i> เริ่มผลิต
                        </button>
                        <button type="button" class="btn btn-danger btn-block" onclick="updateStatus('cancelled')">
                            <i class="fas fa-times"></i> ยกเลิก
                        </button>
                    @elseif($productionOrder->status === 'in_production')
                        <button type="button" class="btn btn-success btn-block mb-2" onclick="updateStatus('completed')">
                            <i class="fas fa-check"></i> เสร็จแล้ว
                        </button>
                        <button type="button" class="btn btn-warning btn-block mb-2" onclick="updateStatus('pending')">
                            <i class="fas fa-arrow-left"></i> กลับเป็นรอดำเนินการ
                        </button>
                        <button type="button" class="btn btn-danger btn-block" onclick="updateStatus('cancelled')">
                            <i class="fas fa-times"></i> ยกเลิก
                        </button>
                    @elseif($productionOrder->status === 'completed')
                        @if(is_null($productionOrder->produced_quantity) || $productionOrder->produced_quantity <= 0)
                            <button type="button" class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#updateQuantityModal">
                                <i class="fas fa-edit"></i> บันทึกจำนวนที่ผลิตจริง
                            </button>
                        @else
                            <div class="alert alert-success text-center">
                                <i class="fas fa-check-circle"></i><br>
                                <strong>ผลิตเสร็จแล้ว</strong><br>
                                จำนวน: {{ number_format($productionOrder->produced_quantity) }} รายการ
                            </div>
                            @php
                                // ตรวจสอบว่ามีการ pull สต๊อกแล้วหรือยัง
                                $hasStockTransaction = \App\Models\InventoryTransaction::where('reference_type', 'production_order')
                                    ->where('reference_id', $productionOrder->id)
                                    ->exists();
                            @endphp
                            @if(!$hasStockTransaction)
                                <button type="button" class="btn btn-warning btn-block mb-2" data-toggle="modal" data-target="#updateQuantityModal">
                                    <i class="fas fa-exclamation-triangle"></i> ยังไม่ได้ pull สต๊อก - กรุณาปรับจำนวน
                                </button>
                            @else
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-check-double"></i><br>
                                    <strong>Pull สต๊อกเรียบร้อยแล้ว</strong>
                                </div>
                            @endif
                        @endif
                        <button type="button" class="btn btn-warning btn-block" onclick="updateStatus('in_production')">
                            <i class="fas fa-arrow-left"></i> กลับเป็นกำลังผลิต
                        </button>
                    @endif
                    
                    <hr>
                    <a href="{{ route('admin.production-orders.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> กลับไปรายการ
                    </a>
                    
                    @if(in_array($productionOrder->status, ['pending']))
                        <a href="{{ route('admin.production-orders.edit', $productionOrder) }}" class="btn btn-warning btn-block">
                            <i class="fas fa-edit"></i> แก้ไข
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">เปลี่ยนสถานะ</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.production-orders.update-status', $productionOrder) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" id="modal-status">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <span id="status-message"></span>
                        </div>
                        <div class="form-group">
                            <label>หมายเหตุ (ไม่บังคับ)</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="หมายเหตุเพิ่มเติม"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">ยืนยัน</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Quantity Modal (for completed status) -->
    @if($productionOrder->status === 'completed')
        <div class="modal fade" id="updateQuantityModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">บันทึกจำนวนที่ผลิตจริง</h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.production-orders.update-produced-quantity', $productionOrder) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>จำนวนที่ผลิตจริง <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       name="produced_quantity" 
                                       value="{{ $productionOrder->produced_quantity }}"
                                       min="0" 
                                       max="{{ $productionOrder->quantity }}"
                                       required>
                                <small class="form-text text-muted">
                                    จำนวนที่สั่งผลิต: {{ number_format($productionOrder->quantity) }} รายการ
                                </small>
                            </div>
                            <div class="form-group">
                                <label>หมายเหตุ</label>
                                <textarea class="form-control" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="หมายเหตุเกี่ยวกับการผลิต"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop

@section('js')
    <script>
        function updateStatus(status) {
            const statusTexts = {
                'pending': 'รอดำเนินการ',
                'in_production': 'กำลังผลิต',
                'completed': 'เสร็จแล้ว',
                'cancelled': 'ยกเลิก'
            };
            
            $('#modal-status').val(status);
            $('#status-message').text('คุณต้องการเปลี่ยนสถานะเป็น "' + statusTexts[status] + '" หรือไม่?');
            $('#updateStatusModal').modal('show');
        }
    </script>
@stop