@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Leave Details') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Leaves') }}
                </li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ __('Leave Details') }}</h4>
                        <div>
                            @php
                                $authUser = auth()->user();
                                $isAdminView = false;
                                if ($authUser) {
                                    if (method_exists($authUser, 'hasRole') && ($authUser->hasRole('Admin') || $authUser->hasRole('Super Admin'))) {
                                        $isAdminView = true;
                                    }
                                    try {
                                        if (! $isAdminView && isset($authUser->type) && $authUser->type === \App\Enums\UserType::SUPERADMIN) {
                                            $isAdminView = true;
                                        }
                                    } catch (\Throwable $e) {}
                                }
                            @endphp
                            @if(auth()->user()->can('cancel', $leave) && ! $isAdminView)
                                <form action="{{ route('leaves.cancel', $leave) }}" method="POST" style="display:inline-block" onsubmit="return confirm('{{ __('Are you sure you want to cancel this leave request?') }}');">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">{{ __('Cancel') }}</button>
                                </form>
                            @endif
                            <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-secondary ms-2">{{ __('Back') }}</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Type') }}</label>
                                <div class="fw-bold">{{ $leave->leaveType->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted">{{ __('Date Filed') }}</label>
                                <div>{{ optional($leave->date_filed)->toDateString() }}</div>
                            </div>
                            <div class="col-md-3 text-end">
                                <span class="badge bg-info text-dark">{{ ucfirst($leave->status) }}</span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">{{ __('Start') }}</label>
                                <div class="fw-bold">{{ optional($leave->start_date)->toDateString() }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">{{ __('End') }}</label>
                                <div class="fw-bold">{{ optional($leave->end_date)->toDateString() }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">{{ __('Total Days') }}</label>
                                <div class="fw-bold">{{ $leave->total_days }}</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Type of Day') }}</label>
                                <div>{{ ucfirst($leave->day_type) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Created By') }}</label>
                                <div>{{ $leave->user->name ?? ($leave->user_id ?? '-') }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">{{ __('Reason') }}</label>
                            <div class="p-3 border rounded bg-light">{{ $leave->reason }}</div>
                        </div>

                        @if($leave->attachments->isNotEmpty())
                            <div class="mb-3">
                                <label class="form-label text-muted">{{ __('Attachments') }}</label>
                                <ul class="list-unstyled">
                                    @foreach($leave->attachments as $att)
                                        <li>
                                            <a href="{{ route('leaves.attachments.download', $att) }}">{{ $att->original_name ?? $att->id }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
