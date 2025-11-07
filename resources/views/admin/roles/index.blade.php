@extends('adminlte::page')

@section('title', 'Roles Management')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Roles Management</h1>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Role
        </a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Roles</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="rolesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Display Name</th>
                            <th>Level</th>
                            <th>Description</th>
                            <th>Users Count</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td><code>{{ $role->name }}</code></td>
                            <td>
                                <span class="badge 
                                    @if($role->level == 1) badge-danger 
                                    @elseif($role->level == 2) badge-warning 
                                    @else badge-info 
                                    @endif">
                                    {{ $role->display_name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">Level {{ $role->level }}</span>
                                <br>
                                <small class="text-muted">{{ $role->level_name }}</small>
                            </td>
                            <td>{{ Str::limit($role->description, 50) ?: '-' }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $role->users_count }}</span>
                                @if($role->users_count > 0)
                                    <br>
                                    <a href="{{ route('admin.roles.manage-users', $role) }}" class="text-sm">
                                        <i class="fas fa-users"></i> Manage
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($role->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($role->users_count == 0)
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $role->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-secondary btn-sm" title="Cannot delete role with users" disabled>
                                        <i class="fas fa-lock"></i>
                                    </button>
                                    @endif
                                </div>

                                <!-- Delete Form -->
                                <form id="delete-form-{{ $role->id }}" action="{{ route('admin.roles.destroy', $role) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#rolesTable').DataTable({
                "responsive": true,
                "autoWidth": false,
                "pageLength": 10,
                "order": [[ 3, "asc" ]] // Order by level
            });
        });

        function confirmDelete(roleId) {
            if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
                document.getElementById('delete-form-' + roleId).submit();
            }
        }
    </script>
@stop