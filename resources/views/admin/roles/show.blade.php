@extends('adminlte::page')

@section('title', 'รายละเอียดบทบาท - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>รายละเอียดบทบาท</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">บทบาท</a></li>
                <li class="breadcrumb-item active">{{ $role->display_name }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลบทบาท</h3>
                </div>
                
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">ชื่อบทบาท</th>
                            <td>{{ $role->name }}</td>
                        </tr>
                        <tr>
                            <th>ชื่อแสดง</th>
                            <td>{{ $role->display_name }}</td>
                        </tr>
                        <tr>
                            <th>ระดับ</th>
                            <td>
                                <span class="badge badge-{{ $role->level == 1 ? 'danger' : ($role->level == 2 ? 'warning' : 'info') }}">
                                    {{ $role->level }} - 
                                    @if($role->level == 1)
                                        Master Admin
                                    @elseif($role->level == 2)
                                        Admin
                                    @else
                                        Member
                                    @endif
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>สถานะ</th>
                            <td>
                                @if($role->is_active)
                                    <span class="badge badge-success">ใช้งาน</span>
                                @else
                                    <span class="badge badge-secondary">ไม่ใช้งาน</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>คำอธิบาย</th>
                            <td>{{ $role->description ?: 'ไม่มีคำอธิบาย' }}</td>
                        </tr>
                        <tr>
                            <th>สร้างเมื่อ</th>
                            <td>{{ $role->created_at->format('d/m/Y H:i') }} ({{ $role->created_at->diffForHumans() }})</td>
                        </tr>
                        <tr>
                            <th>อัปเดตล่าสุด</th>
                            <td>{{ $role->updated_at->format('d/m/Y H:i') }} ({{ $role->updated_at->diffForHumans() }})</td>
                        </tr>
                    </table>
                </div>
                
                <div class="card-footer">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> แก้ไข
                    </a>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> กลับ
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">สถิติผู้ใช้</h3>
                </div>
                
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-users"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">จำนวนผู้ใช้</span>
                            <span class="info-box-number">{{ $role->users->count() }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-user-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">ผู้ใช้ที่ใช้งาน</span>
                            <span class="info-box-number">{{ $role->users->where('is_active', true)->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($role->users->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ผู้ใช้ในบทบาทนี้</h3>
                </div>
                
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($role->users->take(5) as $user)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $user->name }}</strong><br>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                            <span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }}">
                                {{ $user->is_active ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
                            </span>
                        </li>
                        @endforeach
                        
                        @if($role->users->count() > 5)
                        <li class="list-group-item text-center">
                            <small class="text-muted">และอีก {{ $role->users->count() - 5 }} คน</small>
                        </li>
                        @endif
                    </ul>
                </div>
                
                @if($role->users->count() > 0)
                <div class="card-footer text-center">
                    <a href="{{ route('admin.roles.manage-users', $role) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-users-cog"></i> จัดการผู้ใช้
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .info-box {
            margin-bottom: 15px;
        }
    </style>
@stop