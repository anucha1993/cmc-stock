@extends('adminlte::page')

@section('title', 'จัดการผู้ใช้ในบทบาท - CMC-STOCK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>จัดการผู้ใช้ในบทบาท: {{ $role->display_name }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">บทบาท</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.show', $role) }}">{{ $role->display_name }}</a></li>
                <li class="breadcrumb-item active">จัดการผู้ใช้</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลบทบาท</h3>
                </div>
                
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>ชื่อ:</th>
                            <td>{{ $role->display_name }}</td>
                        </tr>
                        <tr>
                            <th>ระดับ:</th>
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
                            <th>จำนวนผู้ใช้:</th>
                            <td><span class="badge badge-primary">{{ $role->users->count() }}</span></td>
                        </tr>
                        <tr>
                            <th>สถานะ:</th>
                            <td>
                                @if($role->is_active)
                                    <span class="badge badge-success">ใช้งาน</span>
                                @else
                                    <span class="badge badge-secondary">ไม่ใช้งาน</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    
                    <div class="mt-3">
                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> กลับ
                        </a>
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> แก้ไข
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ผู้ใช้ในบทบาทนี้</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    @if($role->users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ชื่อ</th>
                                        <th>อีเมล</th>
                                        <th>โทรศัพท์</th>
                                        <th>สถานะ</th>
                                        <th>เข้าร่วมเมื่อ</th>
                                        <th>การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm mr-2">
                                                    @if($user->profile && $user->profile->avatar)
                                                        <img src="{{ asset('storage/' . $user->profile->avatar) }}" 
                                                             class="img-circle elevation-2" width="30" height="30">
                                                    @else
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 30px; height: 30px; font-size: 12px;">
                                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    @if($user->profile)
                                                        <br><small class="text-muted">{{ $user->profile->full_name }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?: '-' }}</td>
                                        <td>
                                            @if($user->is_active)
                                                <span class="badge badge-success">ใช้งาน</span>
                                            @else
                                                <span class="badge badge-secondary">ไม่ใช้งาน</span>
                                            @endif
                                            
                                            @if(!$user->email_verified_at)
                                                <br><span class="badge badge-warning mt-1">ยังไม่ยืนยันอีเมล</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $user->created_at->format('d/m/Y') }}<br>
                                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.users.show', $user) }}" 
                                                   class="btn btn-info btn-sm" title="ดูรายละเอียด">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.users.edit', $user) }}" 
                                                   class="btn btn-warning btn-sm" title="แก้ไข">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($user->id !== auth()->id())
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="removeUserFromRole({{ $user->id }}, '{{ $user->name }}')" 
                                                        title="ลบออกจากบทบาท">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">ไม่มีผู้ใช้ในบทบาทนี้</h5>
                            <p class="text-muted">คุณสามารถเพิ่มผู้ใช้ใหม่หรือแก้ไขบทบาทของผู้ใช้ที่มีอยู่ได้</p>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> เพิ่มผู้ใช้ใหม่
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            @if($availableUsers->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">เพิ่มผู้ใช้เข้าสู่บทบาท</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <form id="addUserForm" action="{{ route('admin.roles.add-user', $role) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <select name="user_id" class="form-control select2" required>
                                    <option value="">เลือกผู้ใช้</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus"></i> เพิ่มผู้ใช้
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Remove User Modal -->
    <div class="modal fade" id="removeUserModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">ยืนยันการลบผู้ใช้</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>คุณต้องการลบ <strong id="userName"></strong> ออกจากบทบาท <strong>{{ $role->display_name }}</strong> หรือไม่?</p>
                    <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> การดำเนินการนี้จะลบบทบาทนี้ออกจากผู้ใช้</p>
                </div>
                <div class="modal-footer">
                    <form id="removeUserForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ลบออกจากบทบาท</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .avatar-sm {
            flex-shrink: 0;
        }
        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#usersTable').DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
                }
            });
            
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'เลือกผู้ใช้'
            });
        });
        
        function removeUserFromRole(userId, userName) {
            $('#userName').text(userName);
            $('#removeUserForm').attr('action', '{{ route("admin.roles.remove-user", [$role, "__USER_ID__"]) }}'.replace('__USER_ID__', userId));
            $('#removeUserModal').modal('show');
        }
    </script>
@stop