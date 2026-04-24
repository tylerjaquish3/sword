@extends('base.layout')

@section('title', 'Admin — Users')

@section('content')

<div class="row mb-4">
    <div class="col">
        <h3 class="font-weight-bold mb-1" style="color: var(--sword-navy);">User Management</h3>
        <p class="text-muted mb-0">Activate, deactivate, or remove user accounts.</p>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Desktop table --}}
<div class="row d-none d-md-flex">
    <div class="col">
        <div class="card" style="border-top: 3px solid var(--sword-gold);">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead style="background: rgba(201,168,76,0.06);">
                        <tr>
                            <th class="ps-4 py-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--sword-navy);">Name</th>
                            <th class="py-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--sword-navy);">Email</th>
                            <th class="py-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--sword-navy);">Joined</th>
                            <th class="py-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--sword-navy);">Status</th>
                            <th class="py-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--sword-navy);">Role</th>
                            <th class="pe-4 py-3 text-end" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--sword-navy);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="ps-4 py-3 align-middle">
                                <span class="fw-semibold" style="color: var(--sword-navy);">{{ $user->name }}</span>
                                @if($user->id === auth()->id())
                                    <span class="badge ms-1" style="background: rgba(70,77,238,0.12); color: #464dee; font-size: 0.65rem;">you</span>
                                @endif
                            </td>
                            <td class="py-3 align-middle text-muted">{{ $user->email }}</td>
                            <td class="py-3 align-middle text-muted" style="font-size: 0.85rem;">{{ $user->created_at->format('M j, Y') }}</td>
                            <td class="py-3 align-middle">
                                @if($user->is_active)
                                    <span class="badge" style="background: rgba(16,185,129,0.12); color: #059669; font-size: 0.72rem; padding: 5px 10px;">Active</span>
                                @else
                                    <span class="badge" style="background: rgba(245,158,11,0.12); color: #d97706; font-size: 0.72rem; padding: 5px 10px;">Pending</span>
                                @endif
                            </td>
                            <td class="py-3 align-middle">
                                @if($user->is_admin)
                                    <span class="badge" style="background: rgba(201,168,76,0.15); color: #92681a; font-size: 0.72rem; padding: 5px 10px;">
                                        <i class="mdi mdi-shield-account me-1"></i>Admin
                                    </span>
                                @else
                                    <span class="badge" style="background: rgba(107,114,128,0.1); color: #6b7280; font-size: 0.72rem; padding: 5px 10px;">User</span>
                                @endif
                            </td>
                            <td class="pe-4 py-3 align-middle text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    @if($user->is_active)
                                        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" style="font-size: 0.78rem;"
                                                @if($user->id === auth()->id()) disabled title="Cannot deactivate your own account" @endif>
                                                Deactivate
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" style="font-size: 0.78rem;">
                                                Activate
                                            </button>
                                        </form>
                                    @endif

                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            onsubmit="return confirm('Delete {{ addslashes($user->name) }}\'s account? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size: 0.78rem;">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No users found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Mobile cards --}}
<div class="d-md-none">
    @forelse($users as $user)
    <div class="card mb-3" style="border-top: 3px solid var(--sword-gold);">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="fw-semibold" style="color: var(--sword-navy); font-size: 1rem;">{{ $user->name }}</span>
                    @if($user->id === auth()->id())
                        <span class="badge ms-1" style="background: rgba(70,77,238,0.12); color: #464dee; font-size: 0.65rem;">you</span>
                    @endif
                    <div class="text-muted mt-1" style="font-size: 0.83rem;">{{ $user->email }}</div>
                    <div class="text-muted" style="font-size: 0.78rem;">Joined {{ $user->created_at->format('M j, Y') }}</div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                    @if($user->is_active)
                        <span class="badge" style="background: rgba(16,185,129,0.12); color: #059669; font-size: 0.72rem; padding: 4px 9px;">Active</span>
                    @else
                        <span class="badge" style="background: rgba(245,158,11,0.12); color: #d97706; font-size: 0.72rem; padding: 4px 9px;">Pending</span>
                    @endif
                    @if($user->is_admin)
                        <span class="badge" style="background: rgba(201,168,76,0.15); color: #92681a; font-size: 0.72rem; padding: 4px 9px;">
                            <i class="mdi mdi-shield-account me-1"></i>Admin
                        </span>
                    @else
                        <span class="badge" style="background: rgba(107,114,128,0.1); color: #6b7280; font-size: 0.72rem; padding: 4px 9px;">User</span>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                @if($user->is_active)
                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="flex-fill">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100" style="font-size: 0.82rem;"
                            @if($user->id === auth()->id()) disabled @endif>
                            Deactivate
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="flex-fill">
                        @csrf
                        <button type="submit" class="btn btn-outline-success w-100" style="font-size: 0.82rem;">
                            Activate
                        </button>
                    </form>
                @endif

                @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                        onsubmit="return confirm('Delete {{ addslashes($user->name) }}\'s account? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" style="font-size: 0.82rem; white-space: nowrap;">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <p class="text-muted text-center py-4">No users found.</p>
    @endforelse
</div>

@endsection
