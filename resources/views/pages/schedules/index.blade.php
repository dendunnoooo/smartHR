@extends('layouts.app')

@push('page-styles')
<style>
    .schedule-card {
        transition: all 0.3s;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        height: 100%;
    }
    .schedule-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .schedule-header {
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .schedule-header h5 {
        color: white;
        margin-bottom: 10px;
        font-weight: 600;
    }
    .time-badge {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .schedule-card.day-shift .schedule-header {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    }
    .schedule-card.mid-shift .schedule-header {
        background: linear-gradient(135deg, #FF9800 0%, #f57c00 100%);
    }
    .schedule-card.night-shift .schedule-header {
        background: linear-gradient(135deg, #9C27B0 0%, #7b1fa2 100%);
    }
    .schedule-card.morning-shift .schedule-header {
        background: linear-gradient(135deg, #2196F3 0%, #1976d2 100%);
    }
    .schedule-card.flexible-shift .schedule-header {
        background: linear-gradient(135deg, #00BCD4 0%, #0097a7 100%);
    }
    .schedule-body {
        padding: 20px;
    }
    .schedule-info-item {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    .schedule-info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .schedule-info-label {
        font-size: 11px;
        color: #888;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .schedule-info-value {
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }
    .schedule-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .schedule-actions {
        position: absolute;
        top: 15px;
        right: 15px;
    }
    .schedule-actions .btn {
        background: rgba(255,255,255,0.3);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .schedule-actions .btn:hover {
        background: rgba(255,255,255,0.4);
    }
    .schedule-actions .dropdown-menu {
        min-width: 150px;
    }
    .badge-custom {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Work Schedules') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">{{ __('Employees') }}</li>
                <li class="breadcrumb-item active">{{ __('Schedules') }}</li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('schedules.create') }}" class="btn add-btn">
                        <i class="fa fa-plus"></i> {{ __('Add Schedule') }}
                    </a>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            @forelse($schedules as $schedule)
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card schedule-card {{ strtolower(str_replace(' ', '-', $schedule->name)) }}">
                        <div class="schedule-header position-relative">
                            <div class="schedule-actions">
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('schedules.edit', $schedule) }}">
                                                <i class="fa fa-edit text-info"></i> {{ __('Edit') }}
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fa fa-trash text-danger"></i> {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <h5>{{ $schedule->name }}</h5>
                            <span class="time-badge">
                                <i class="fa fa-clock"></i> {{ $schedule->time_range }}
                            </span>
                        </div>

                        <div class="schedule-body">
                            <div class="schedule-info-item">
                                <div class="schedule-info-label">{{ __('Work Hours') }}</div>
                                <div class="schedule-info-value">{{ $schedule->work_hours }} {{ __('hours') }}</div>
                            </div>
                            <div class="schedule-info-item">
                                <div class="schedule-info-label">{{ __('Working Days') }}</div>
                                <div class="schedule-info-value">{{ $schedule->working_days }}</div>
                            </div>
                            @if($schedule->description)
                            <div class="schedule-info-item">
                                <div class="schedule-info-label">{{ __('Description') }}</div>
                                <div class="schedule-info-value">{{ $schedule->description }}</div>
                            </div>
                            @endif
                            
                            @if($schedule->rotation_day && $schedule->nextSchedule)
                            <div class="schedule-info-item">
                                <div class="schedule-info-label">
                                    <i class="fa fa-sync-alt"></i> {{ __('Schedule Rotation') }}
                                </div>
                                <div class="schedule-info-value">
                                    <small class="text-muted">{{ __('Day') }} {{ $schedule->rotation_day }} → <strong>{{ $schedule->nextSchedule->name }}</strong></small>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="schedule-footer">
                            <div>
                                <span class="badge {{ $schedule->is_active ? 'bg-success' : 'bg-secondary' }} badge-custom">
                                    {{ $schedule->is_active ? __('Active') : __('Inactive') }}
                                </span>
                                @if($schedule->rotation_day && $schedule->nextSchedule)
                                <span class="badge bg-warning badge-custom ms-1" title="{{ __('Auto-rotation enabled') }}">
                                    <i class="fa fa-sync-alt"></i>
                                </span>
                                @endif
                            </div>
                            <div>
                                <span class="badge bg-info badge-custom">
                                    <i class="fa fa-users"></i> {{ $schedule->users_count }} {{ __('Employees') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> {{ __('No schedules found. Create your first work schedule.') }}
                    </div>
                </div>
            @endforelse
        </div>

    </div>
@endsection
