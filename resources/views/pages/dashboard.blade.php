@extends('layouts.app')

@push('page-styles')
    <!-- Chart CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/morris/morris.css') }}">
    <style>
        .admin-stat-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }
        .admin-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .stat-icon-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            flex-shrink: 0;
        }
        .stat-icon-large i {
            font-weight: 900;
        }
        .quick-action-btn {
            padding: 15px;
            border-radius: 8px;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .quick-action-btn:hover {
            border-color: currentColor;
            transform: scale(1.05);
        }
        .metric-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.98));
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .attendance-badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 12px;
        }
        .badge-hr-head {
            background: linear-gradient(135deg, #ff902f 0%, #ff6b35 100%);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255, 144, 47, 0.3);
        }
        .badge-hr-staff {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
        }
        .badge-employee {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
        }
    </style>
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">
                <div class="d-flex align-items-center">
                    @if(auth()->user()->hasRole('HR Admin'))
                        <span class="badge badge-hr-head me-3 px-3 py-2">
                            <i class="fa fa-crown"></i> HR HEAD
                        </span>
                    @elseif(auth()->user()->hasRole('Admin'))
                        <span class="badge badge-hr-staff me-3 px-3 py-2">
                            <i class="fa fa-user-tie"></i> HR STAFF
                        </span>
                    @elseif(auth()->user()->type === \App\Enums\UserType::EMPLOYEE)
                        <span class="badge badge-employee me-3 px-3 py-2">
                            <i class="fa fa-id-badge"></i> EMPLOYEE
                        </span>
                    @else
                        <span class="badge bg-gradient-primary me-3 px-3 py-2">
                            <i class="fa fa-crown"></i> {{ auth()->user()->type->value }}
                        </span>
                    @endif
                    {{ __('Welcome') }}, {{ auth()->check() ? (auth()->user()->fullname ?? auth()->user()->name ?? '') : __('Guest') }}!
                </div>
            </x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item active">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        @superadmin

        <!-- Key Metrics Row -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="card admin-stat-card border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase">Total Employees</h6>
                                <h2 class="mb-1 fw-bold">{{ !empty($employees) ? $employees->count(): 0 }}</h2>
                                <small class="text-success">
                                    <i class="fa fa-arrow-up"></i> {{ $thisMonthTotalEmployees }} this month
                                </small>
                            </div>
                            <div class="stat-icon-large bg-primary bg-opacity-10 text-primary">
                                <i class="fa fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card admin-stat-card border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase">Present Today</h6>
                                @php
                                    $todayPresent = \App\Models\User::where('type', \App\Enums\UserType::EMPLOYEE)
                                        ->whereHas('attendances', function($q) {
                                            $q->whereDate('startDate', \Carbon\Carbon::today());
                                        })->count();
                                @endphp
                                <h2 class="mb-1 fw-bold text-success">{{ $todayPresent }}</h2>
                                <small class="text-muted">
                                    {{ !empty($employees) ? round(($todayPresent / $employees->count()) * 100, 1) : 0 }}% attendance
                                </small>
                            </div>
                            <div class="stat-icon-large bg-success bg-opacity-10 text-success">
                                <i class="fa fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card admin-stat-card border-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase">Absent Today</h6>
                                <h2 class="mb-1 fw-bold text-danger">{{ !empty($absentees) ? $absentees->count() : 0 }}</h2>
                                <small class="text-muted">
                                    Requires attention
                                </small>
                            </div>
                            <div class="stat-icon-large bg-danger bg-opacity-10 text-danger">
                                <i class="fa fa-user-times"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card admin-stat-card border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase">Open Tickets</h6>
                                <h2 class="mb-1 fw-bold text-warning">{{ !empty($tickets) ? $tickets->where('status', \App\Enums\TicketStatus::NEW)->count() : 0 }}</h2>
                                <small class="text-muted">
                                    {{ !empty($myAssignedTickets) ? $myAssignedTickets->count() : 0 }} assigned to you
                                </small>
                            </div>
                            <div class="stat-icon-large bg-warning bg-opacity-10 text-warning">
                                <i class="fa fa-ticket-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-bolt"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('employees.create') }}" class="quick-action-btn btn btn-outline-primary w-100 text-start">
                                    <i class="fa fa-user-plus fa-2x mb-2"></i>
                                    <div class="fw-bold">Add Employee</div>
                                    <small class="text-muted">Register new staff</small>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('attendances.index') }}" class="quick-action-btn btn btn-outline-success w-100 text-start">
                                    <i class="fa fa-calendar-check fa-2x mb-2"></i>
                                    <div class="fw-bold">View Attendance</div>
                                    <small class="text-muted">Check records</small>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('payslips.index') }}" class="quick-action-btn btn btn-outline-info w-100 text-start">
                                    <i class="fa fa-file-invoice-dollar fa-2x mb-2"></i>
                                    <div class="fw-bold">Generate Payslips</div>
                                    <small class="text-muted">Payroll management</small>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('holidays.index') }}" class="quick-action-btn btn btn-outline-warning w-100 text-start">
                                    <i class="fa fa-calendar-alt fa-2x mb-2"></i>
                                    <div class="fw-bold">Manage Holidays</div>
                                    <small class="text-muted">Configure calendar</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Analytics -->
        <div class="row mt-3">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fa fa-chart-bar text-primary"></i> Monthly Expense Overview</h5>
                    </div>
                    <div class="card-body">
                        <div id="monthly_expense_barchart"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0"><i class="fa fa-chart-pie"></i> Department Summary</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $departments = \App\Models\Department::withCount('users')->orderBy('users_count', 'desc')->take(5)->get();
                        @endphp
                        @forelse($departments as $dept)
                            <div class="metric-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $dept->name }}</strong>
                                        <div class="text-muted small">{{ $dept->location ?? 'No location' }}</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">{{ $dept->users_count }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="fa fa-building fa-3x mb-2 opacity-25"></i>
                                <p>No departments found</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @if (!empty(module('Sales')))
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">{{ __('New Employees') }}</h5>
                            <span class="badge bg-primary">{{ $thisMonthTotalEmployees }}</span>
                        </div>
                        <h3 class="mb-3">{{ $thisMonthTotalEmployees }}</h3>
                        <div class="progress mb-2" style="height: 8px;">
                            @php
                                $growthPercent = $prevMonthTotalEmployees > 0 ? 
                                    round((($thisMonthTotalEmployees - $prevMonthTotalEmployees) / $prevMonthTotalEmployees) * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-primary" style="width: {{ min(abs($growthPercent), 100) }}%" role="progressbar"></div>
                        </div>
                        <p class="mb-0">
                            <span class="text-muted">Previous Month:</span> 
                            <strong>{{ $prevMonthTotalEmployees }}</strong>
                            @if($growthPercent > 0)
                                <span class="badge bg-success ms-2"><i class="fa fa-arrow-up"></i> {{ $growthPercent }}%</span>
                            @elseif($growthPercent < 0)
                                <span class="badge bg-danger ms-2"><i class="fa fa-arrow-down"></i> {{ abs($growthPercent) }}%</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">{{ __('Monthly Expenses') }}</h5>
                            <span class="badge bg-danger">{{ LocaleSettings('currency_symbol') }}</span>
                        </div>
                        <h3 class="mb-3">{{ LocaleSettings('currency_symbol').' '.number_format($thisMonthExpenses, 2) }}</h3>
                        <div class="progress mb-2" style="height: 8px;">
                            @php
                                $expenseChange = $prevMonthExpenses > 0 ? 
                                    round((($thisMonthExpenses - $prevMonthExpenses) / $prevMonthExpenses) * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-danger" style="width: {{ min(abs($expenseChange), 100) }}%" role="progressbar"></div>
                        </div>
                        <p class="mb-0">
                            <span class="text-muted">Previous Month:</span> 
                            <strong>{{ LocaleSettings('currency_symbol').' '.number_format($prevMonthExpenses, 2) }}</strong>
                            @if($expenseChange > 0)
                                <span class="badge bg-warning ms-2"><i class="fa fa-arrow-up"></i> {{ $expenseChange }}%</span>
                            @elseif($expenseChange < 0)
                                <span class="badge bg-success ms-2"><i class="fa fa-arrow-down"></i> {{ abs($expenseChange) }}%</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Statistics and Absent Employees -->
        <div class="row mt-3">
            <div class="col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fa fa-chart-line text-success"></i> {{ __('Statistics Overview') }}</h5>
                    </div>
                    <div class="card-body">
                        @if (!empty($invoices) && $invoices->count() > 0)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">{{ __('Declined Invoices') }}</span>
                                    <span class="badge bg-danger">{{ $invoices->where('status', '4')->count() }} / {{ $invoices->count() }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    @php $declinedPercent = ($invoices->count() > 0) ? round(($invoices->where('status', '4')->count() / $invoices->count()) * 100) : 0; @endphp
                                    <div class="progress-bar bg-danger" style="width: {{ $declinedPercent }}%" role="progressbar"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">{{ __('Partially Paid') }}</span>
                                    <span class="badge bg-info">{{ $invoices->where('status', '3')->count() }} / {{ $invoices->count() }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    @php $partialPercent = ($invoices->count() > 0) ? round(($invoices->where('status', '3')->count() / $invoices->count()) * 100) : 0; @endphp
                                    <div class="progress-bar bg-info" style="width: {{ $partialPercent }}%" role="progressbar"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">{{ __('Paid Invoices') }}</span>
                                    <span class="badge bg-success">{{ $invoices->where('status', '2')->count() }} / {{ $invoices->count() }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    @php $paidPercent = ($invoices->count() > 0) ? round(($invoices->where('status', '2')->count() / $invoices->count()) * 100) : 0; @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $paidPercent }}%" role="progressbar"></div>
                                </div>
                            </div>
                        @endif
                        
                        @if (!empty($tickets) && $tickets->count() > 0)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">{{ __('Open Tickets') }}</span>
                                    <span class="badge bg-warning">{{ $tickets->where('status', \App\Enums\TicketStatus::NEW)->count() }} / {{ $tickets->count() }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    @php $openPercent = ($tickets->count() > 0) ? round(($tickets->where('status', \App\Enums\TicketStatus::NEW)->count() / $tickets->count()) * 100) : 0; @endphp
                                    <div class="progress-bar bg-warning" style="width: {{ $openPercent }}%" role="progressbar"></div>
                                </div>
                            </div>
                            
                            <div class="mb-0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">{{ __('Closed Tickets') }}</span>
                                    <span class="badge bg-success">{{ $tickets->where('status', \App\Enums\TicketStatus::CLOSED)->count() }} / {{ $tickets->count() }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    @php $closedPercent = ($tickets->count() > 0) ? round(($tickets->where('status', \App\Enums\TicketStatus::CLOSED)->count() / $tickets->count()) * 100) : 0; @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $closedPercent }}%" role="progressbar"></div>
                                </div>
                            </div>
                        @endif
                        
                        @if (empty($invoices) && empty($tickets))
                            <div class="text-center text-muted py-4">
                                <i class="fa fa-chart-bar fa-3x mb-2 opacity-25"></i>
                                <p>No statistics available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            @if (!empty($absentees) && $absentees->count() > 0)
            <div class="col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-user-times"></i> {{ __('Today Absent') }} 
                            <span class="badge bg-white text-danger ms-2">{{ $absentees->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @foreach ($absentees as $user)
                        <div class="d-flex align-items-center mb-3 p-2 rounded hover-shadow">
                            <a @can('show-Employeeprofile') href="{{ route('employees.index') }}" @else href="#" @endcan class="avatar me-3">
                                <img src="{{ !empty($user->avatar) ? asset('storage/users/'.$user->avatar) : asset('images/user.jpg') }}" 
                                     alt="{{ $user->fullname }}"
                                     class="rounded-circle"
                                     style="width: 45px; height: 45px; object-fit: cover;">
                            </a>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $user->fullname }}</div>
                                <small class="text-muted">
                                    <i class="fa fa-envelope"></i> {{ $user->email }}
                                </small>
                            </div>
                            <span class="attendance-badge badge bg-danger">Absent</span>
                        </div>
                        @endforeach
                        
                        @can('view-attendances')
                        <div class="text-center mt-3 pt-3 border-top">
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('attendances.index') }}">
                                <i class="fa fa-list"></i> {{ __('View All Attendance') }}
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
            @endif
        </div>


        @push('page-scripts')
        <!-- ChartJS -->
        <script defer src="{{ asset('js/plugins/morris/morris.min.js') }}"></script>
        <script defer src="{{ asset('js/plugins/raphael/raphael.min.js') }}"></script>
        <script type="module" defer>
        $(document).ready(function() {
                let currency_symbol = "{{ LocaleSettings('currency_symbol') }}"
                @if(!empty($monthly_expense))
                let chartData = @json($monthly_expense_chart_js ?? []);
                Morris.Bar({
                    element: 'monthly_expense_barchart',
                    data: chartData,
                    xkey: 'y',
                    ykeys: ['a', 'b'],
                    labels: [`Total Expense (${currency_symbol})`, 'Total Expenses'],
                    lineColors: ['#ff9b44','#fc6075'],
                    lineWidth: '3px',
                    barColors: ['#ff9b44','#fc6075'],
                    resize: true,
                    redraw: true,
                    gridTextSize: 12,
                    hideHover: 'auto'
                });
                @endif
            });
        </script>
        @endpush


        @endsuperadmin


    </div>
@endsection
