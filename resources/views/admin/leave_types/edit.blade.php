@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb>
            <x-slot name="title">{{ __('Edit Leave Type') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('leave-types.index') }}">{{ __('Leave Types') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Edit') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('leave-types.update', $leaveType) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label>{{ __('Name') }}</label>
                                <input name="name" class="form-control" value="{{ old('name', $leaveType->name) }}" required />
                            </div>
                            <div class="mb-3">
                                <label>{{ __('Code') }}</label>
                                <input name="code" class="form-control" value="{{ old('code', $leaveType->code) }}" />
                            </div>
                            <div class="mb-3">
                                <label>{{ __('Accrual rate per month') }}</label>
                                <input name="accrual_rate_per_month" type="number" step="0.01" class="form-control" value="{{ old('accrual_rate_per_month', $leaveType->accrual_rate_per_month) }}" />
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="requires_approval" value="1" class="form-check-input" id="requiresApproval" {{ old('requires_approval', $leaveType->requires_approval) ? 'checked' : '' }}>
                                <label class="form-check-label" for="requiresApproval">{{ __('Requires Approval') }}</label>
                            </div>
                            <div class="mb-3">
                                <label>{{ __('Max Days (optional)') }}</label>
                                <input name="max_days" type="number" min="0" class="form-control" value="{{ old('max_days', $leaveType->max_days) }}" />
                                <small class="form-text text-muted">{{ __('Leave empty to use system defaults (maternity/paternity/sick/annual default values).') }}</small>
                            </div>
                            <button class="btn btn-primary">{{ __('Update') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
