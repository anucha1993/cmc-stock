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
                                    <th>จำนวนรวม:</th>
                                    <td><strong class="text-primary">{{ number_format($productionOrder->quantity) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>ผลิตแล้ว:</th>
                                    <td><strong class="text-success">{{ number_format($productionOrder->produced_quantity) }}</strong></td>
                                </tr>
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
                                <tr>
                                    <th>วันที่สร้าง:</th>
                                    <td>{{ $productionOrder->requested_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    @if($productionOrder->order_type === 'multiple')
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>ความคืบหน้ารวม</h5>
                                <div class="progress progress-lg">
                                    <div class="progress-bar bg-success" 
                                         style="width: {{ $productionOrder->total_progress }}%">
                                        {{ round($productionOrder->total_progress, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>ความคืบหน้า</h5>
                                <div class="progress progress-lg">
                                    <div class="progress-bar bg-success" 
                                         style="width: {{ $productionOrder->progress_percentage }}%">
                                        {{ round($productionOrder->progress_percentage, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

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
                                        <th width="10%">จำนวนสั่งผลิต</th>
                                        <th width="10%">ผลิตแล้ว</th>
                                        <th width="15%">ความคืบหน้า</th>
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
                                            <td class="text-center">
                                                <span class="badge badge-success">{{ number_format($item->produced_quantity) }}</span>
                                            </td>
                                            <td>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-success" 
                                                         style="width: {{ $item->progress_percentage }}%">
                                                    </div>
                                                </div>
                                                <small>{{ round($item->progress_percentage, 1) }}%</small>
                                            </td>
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
                                            <td colspan="6" class="text-center text-muted">ไม่มีรายการสินค้า</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($productionOrder->items->isNotEmpty())
                                    <tfoot>
                                        <tr class="table-info">
                                            <th>รวม</th>
                                            <th class="text-center">{{ number_format($productionOrder->items->sum('quantity')) }}</th>
                                            <th class="text-center">{{ number_format($productionOrder->items->sum('produced_quantity')) }}</th>
                                            <th></th>
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
                    <h3 class="card-title">การดำเนินการ</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical btn-block">
                        <a href="{{ route('admin.production-orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> กลับไปรายการ
                        </a>
                        
                        @if(in_array($productionOrder->status, ['pending', 'approved']))
                            <a href="{{ route('admin.production-orders.edit', $productionOrder) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> แก้ไข
                            </a>
                        @endif

                        @if($productionOrder->status === 'pending')
                            <form action="{{ route('admin.production-orders.start', $productionOrder) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-info btn-block" onclick="return confirm('ต้องการเริ่มการผลิตหรือไม่?')">
                                    <i class="fas fa-play"></i> เริ่มผลิต
                                </button>
                            </form>
                        @endif

                        @if($productionOrder->status === 'in_production')
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#updateProgressModal">
                                <i class="fas fa-chart-line"></i> อัปเดตความคืบหน้า
                            </button>
                        @endif

                        @if(in_array($productionOrder->status, ['pending', 'approved', 'in_production']))
                            <form action="{{ route('admin.production-orders.cancel', $productionOrder) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('ต้องการยกเลิกใบสั่งผลิตหรือไม่?')">
                                    <i class="fas fa-times"></i> ยกเลิก
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Production Details Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">รายละเอียดเพิ่มเติม</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
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
                        @if($productionOrder->production_cost)
                            <tr>
                                <th>ต้นทุนการผลิต:</th>
                                <td>฿{{ number_format($productionOrder->production_cost, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>อัปเดตล่าสุด:</th>
                            <td>{{ $productionOrder->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Progress Modal -->
    @if($productionOrder->status === 'in_production')
        <div class="modal fade" id="updateProgressModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">อัปเดตความคืบหน้าการผลิต</h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.production-orders.update-progress', $productionOrder) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>จำนวนที่ผลิตแล้ว</label>
                                <input type="number" 
                                       class="form-control" 
                                       name="produced_quantity" 
                                       value="{{ $productionOrder->produced_quantity }}"
                                       min="0" 
                                       max="{{ $productionOrder->quantity }}"
                                       required>
                                <small class="form-text text-muted">
                                    จำนวนที่สั่งผลิต: {{ number_format($productionOrder->quantity) }}
                                </small>
                            </div>
                            <div class="form-group">
                                <label>หมายเหตุความคืบหน้า</label>
                                <textarea class="form-control" 
                                          name="progress_notes" 
                                          rows="3" 
                                          placeholder="หมายเหตุเกี่ยวกับความคืบหน้า"></textarea>
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