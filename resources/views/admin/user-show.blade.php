@extends('base.layout')

@section('title', 'Admin — ' . $targetUser->name)

@section('content')

<div class="row mb-4">
    <div class="col">
        <a href="{{ route('admin.users') }}" class="text-decoration-none" style="font-size: 0.85rem; color: var(--sword-navy);">
            <i class="mdi mdi-arrow-left me-1"></i>Back to User Management
        </a>
        <h3 class="font-weight-bold mb-1 mt-2" style="color: var(--sword-navy);">
            {{ $targetUser->name }}
            @if($targetUser->is_admin)
                <span class="badge" style="background: rgba(201,168,76,0.15); color: #92681a; font-size: 0.65rem; padding: 5px 10px; vertical-align: middle;">
                    <i class="mdi mdi-shield-account me-1"></i>Admin
                </span>
            @endif
            @if($targetUser->is_active)
                <span class="badge" style="background: rgba(16,185,129,0.12); color: #059669; font-size: 0.65rem; padding: 5px 10px; vertical-align: middle;">Active</span>
            @else
                <span class="badge" style="background: rgba(245,158,11,0.12); color: #d97706; font-size: 0.65rem; padding: 5px 10px; vertical-align: middle;">Pending</span>
            @endif
        </h3>
        <p class="text-muted mb-0">
            {{ $targetUser->email }}
            &middot; Joined {{ $targetUser->created_at->format('M j, Y') }}
            @if($targetUser->referred_by)
                &middot; Referred by {{ $targetUser->referred_by }}
            @endif
        </p>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">
            Last login:
            @if($lastLogin)
                {{ $lastLogin->logged_in_at->diffForHumans() }} ({{ $lastLogin->logged_in_at->format('M j, Y g:ia') }})
            @else
                Never
            @endif
        </p>
    </div>
</div>

@php
    $columns = [
        'chapters_read' => 'Chapters Read',
        'prayers' => 'Prayers',
        'commentary' => 'Commentary',
        'topics' => 'Topics',
        'memory_completed' => 'Memory Completed',
        'digests' => 'Digests',
    ];
@endphp

<div class="row">
    <div class="col">
        <div class="card" style="border-top: 3px solid var(--sword-gold);">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead style="background: rgba(201,168,76,0.06);">
                        <tr>
                            <th class="ps-4 py-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--sword-navy);">Month</th>
                            @foreach($columns as $label)
                                <th class="py-3 text-center" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--sword-navy);">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $month)
                        <tr>
                            <td class="ps-4 py-3 align-middle fw-semibold" style="color: var(--sword-navy);">{{ $month['label'] }}</td>
                            @foreach(array_keys($columns) as $key)
                                <td class="py-3 align-middle text-center">{{ $month[$key] }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: rgba(201,168,76,0.08); border-top: 2px solid var(--sword-gold);">
                            <td class="ps-4 py-3 fw-bold" style="color: var(--sword-navy);">All-Time</td>
                            @foreach(array_keys($columns) as $key)
                                <td class="py-3 text-center fw-bold" style="color: var(--sword-navy);">{{ $allTime[$key] }}</td>
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @if($months->lastPage() > 1)
            <div class="mt-3">
                {{ $months->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@endsection
