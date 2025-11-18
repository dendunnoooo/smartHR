@extends('layouts.app')

@push('page-styles')
    <style>
        .system-admin-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .admin-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            flex-shrink: 0;
        }
        .stat-icon i {
            font-weight: 900;
        }
        .activity-item {
            padding: 12px;
            border-left: 3px solid #e0e0e0;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .activity-item:hover {
            background: #f8f9fa;
            border-left-color: #667eea;
        }
    </style>
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">
                <div class="d-flex align-items-center">
                    <span class="system-admin-badge me-3">
                        <i class="fa fa-shield-alt"></i> System Administrator
                    </span>
                    {{ __('System Control Panel') }}
                </div>
            </x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item active">
                    <a href="{{ route('dashboard') }}">{{ __('System Dashboard') }}</a>
                </li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- System Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="card admin-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Users</h6>
                                <h2 class="mb-0">{{ $totalUsers ?? 0 }}</h2>
                                <small class="text-success"><i class="fa fa-arrow-up"></i> Active System</small>
                            </div>
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fa fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card admin-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Employees</h6>
                                <h2 class="mb-0">{{ $totalEmployees ?? 0 }}</h2>
                                <small class="text-info"><i class="fa fa-user-tie"></i> Staff Members</small>
                            </div>
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="fa fa-user-tie"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card admin-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Departments</h6>
                                <h2 class="mb-0">{{ $totalDepartments ?? 0 }}</h2>
                                <small class="text-warning"><i class="fa fa-building"></i> Active Depts</small>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fa fa-building"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card admin-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">System Health</h6>
                                <h2 class="mb-0 text-success">100%</h2>
                                <small class="text-success"><i class="fa fa-check-circle"></i> Operational</small>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fa fa-heartbeat"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Management Grid -->
        <div class="row mt-4">
            <!-- User Management -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-users-cog"></i> User Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-user text-primary"></i> All Users</span>
                                <span class="badge bg-primary rounded-pill">{{ $totalUsers ?? 0 }}</span>
                            </a>
                            <a href="{{ route('employees.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-id-badge text-info"></i> Employees</span>
                                <span class="badge bg-info rounded-pill">{{ $totalEmployees ?? 0 }}</span>
                            </a>
                            <a href="{{ route('roles.index') }}" class="list-group-item list-group-item-action">
                                <i class="fa fa-shield-alt text-warning"></i> Roles & Permissions
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Organization Structure -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fa fa-sitemap"></i> Organization</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('departments.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-building text-primary"></i> Departments</span>
                                <span class="badge bg-primary rounded-pill">{{ $totalDepartments ?? 0 }}</span>
                            </a>
                            <a href="{{ route('designations.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-tag text-success"></i> Designations</span>
                                <span class="badge bg-success rounded-pill">{{ $totalDesignations ?? 0 }}</span>
                            </a>
                            <a href="{{ route('holidays.index') }}" class="list-group-item list-group-item-action">
                                <i class="fa fa-calendar-alt text-warning"></i> Holidays
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Settings -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fa fa-cogs"></i> System Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action">
                                <i class="fa fa-sliders-h text-primary"></i> General Settings
                            </a>
                            <a href="{{ route('settings.salary') }}" class="list-group-item list-group-item-action">
                                <i class="fa fa-money-bill-wave text-success"></i> Salary Settings
                            </a>
                            <a href="{{ route('backups.index') }}" class="list-group-item list-group-item-action">
                                <i class="fa fa-database text-danger"></i> Backups
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance & Payroll Management -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fa fa-clock"></i> Attendance Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('attendances.index') }}" class="list-group-item list-group-item-action">
                                <i class="fa fa-calendar-check text-success"></i> View Attendance Records
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fa fa-chart-bar text-info"></i> Attendance Reports
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fa fa-ticket-alt text-warning"></i> Leave Credits Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fa fa-money-check-alt"></i> Payroll System</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('payslips.index') }}" class="list-group-item list-group-item-action">
                                <i class="fa fa-file-invoice-dollar text-primary"></i> Payslip Management
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fa fa-plus-circle text-success"></i> Allowances & Deductions
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fa fa-chart-line text-info"></i> Payroll Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent System Activity -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-history"></i> Recent System Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>System Initialized</strong>
                                    <p class="mb-0 text-muted small">All modules are operational and running smoothly</p>
                                </div>
                                <small class="text-muted">{{ now()->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Leave Credit System Active</strong>
                                    <p class="mb-0 text-muted small">Automatic token granting scheduled for every Monday 6:00 AM</p>
                                </div>
                                <small class="text-muted">{{ now()->subHours(2)->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Payroll Automation Configured</strong>
                                    <p class="mb-0 text-muted small">Automated payslip generation with overtime/undertime calculations</p>
                                </div>
                                <small class="text-muted">{{ now()->subHours(5)->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fa fa-bolt text-warning"></i> Quick Actions</h5>
                        <div class="btn-group-vertical w-100" role="group">
                            <button type="button" class="btn btn-outline-primary text-start mb-2" data-url="{{ route('users.create') }}" data-ajax-modal="true" data-title="Add User" data-size="lg">
                                <i class="fa fa-user-plus"></i> Create New User
                            </button>
                            <button type="button" class="btn btn-outline-success text-start mb-2" onclick="location.href='{{ route('employees.create') }}'">
                                <i class="fa fa-id-card"></i> Add New Employee
                            </button>
                            <button type="button" class="btn btn-outline-info text-start mb-2" data-url="{{ route('departments.create') }}" data-ajax-modal="true" data-title="Add Department" data-size="md">
                                <i class="fa fa-building"></i> Create Department
                            </button>
                            <button type="button" class="btn btn-outline-warning text-start mb-2" onclick="location.href='{{ route('holidays.create') }}'">
                                <i class="fa fa-calendar-plus"></i> Add Holiday
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('page-scripts')
<script>
    // Auto-refresh system status every 5 minutes
    setInterval(function() {
        console.log('System status check...');
    }, 300000);
</script>
@endpush
