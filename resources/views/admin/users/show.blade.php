@extends('adminlte::page')

@section('title', 'View User')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>View User: {{ $user->name }}</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">ID</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $user->phone ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($user->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Roles</th>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="badge 
                                        @if($role->level == 1) badge-danger 
                                        @elseif($role->level == 2) badge-warning 
                                        @else badge-info 
                                        @endif">
                                        {{ $role->display_name }}
                                    </span>
                                @empty
                                    <span class="badge badge-secondary">No Role</span>
                                @endforelse
                            </td>
                        </tr>
                        <tr>
                            <th>Email Verified</th>
                            <td>{{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y H:i') : 'Not Verified' }}</td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Updated</th>
                            <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    @if($user->id !== auth()->id())
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#resetPasswordModal">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Profile Information</h3>
                </div>
                <div class="card-body">
                    @if($user->profile)
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Full Name</th>
                                <td>{{ $user->profile->full_name }}</td>
                            </tr>
                            <tr>
                                <th>First Name</th>
                                <td>{{ $user->profile->first_name }}</td>
                            </tr>
                            <tr>
                                <th>Last Name</th>
                                <td>{{ $user->profile->last_name }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $user->profile->phone ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Birth Date</th>
                                <td>{{ $user->profile->birth_date ? $user->profile->birth_date->format('M d, Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td>{{ ucfirst($user->profile->gender) ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>{{ $user->profile->address ?: '-' }}</td>
                            </tr>
                            @if($user->profile->avatar)
                            <tr>
                                <th>Avatar</th>
                                <td>
                                    <img src="{{ Storage::url($user->profile->avatar) }}" alt="Avatar" class="img-thumbnail" style="max-width: 100px;">
                                </td>
                            </tr>
                            @endif
                        </table>
                    @else
                        <p class="text-muted">No profile information available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    @if($user->id !== auth()->id())
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reset Password for {{ $user->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@stop

@section('css')
@stop

@section('js')
@stop