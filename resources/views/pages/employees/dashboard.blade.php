@extends('layouts.app')

@push('page-styles')
<style>
    .employee-welcome-card {
        background: linear-gradient(135deg, #ff902f 0%, #ff6b35 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(255, 144, 47, 0.3);
    }
    .employee-badge {
        background: linear-gradient(135deg, #ffa726 0%, #ff7043 100%);
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 14px;
        display: inline-block;
        box-shadow: 0 4px 15px rgba(255, 112, 67, 0.3);
    }
    .clock-status-badge {
        background: white;
        padding: 12px 24px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        animation: pulse 2s infinite;
    }
    .clock-status-badge.clocked-in {
        color: #10b981;
    }
    .clock-status-badge.clocked-out {
        color: #ef4444;
    }
    .clock-status-badge .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        animation: blink 2s infinite;
    }
    .clock-status-badge.clocked-in .status-dot {
        background: #10b981;
    }
    .clock-status-badge.clocked-out .status-dot {
        background: #ef4444;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }
    .stat-card {
        border-radius: 12px;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .credit-box {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        border: 2px solid #f0f0f0;
        transition: all 0.3s;
    }
    .credit-box:hover {
        border-color: #ff902f;
        box-shadow: 0 5px 15px rgba(255, 144, 47, 0.2);
    }
    .credit-box h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .info-banner {
        background: linear-gradient(135deg, #ff902f 0%, #ff6b35 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 20px;
    }
    .quick-action-card {
        border-radius: 10px;
        border: 2px solid transparent;
        transition: all 0.3s;
    }
    .quick-action-card:hover {
        border-color: #ff902f;
        transform: scale(1.02);
    }
    .alert-clock-out {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 20px;
        animation: alertPulse 3s infinite;
    }
    @keyframes alertPulse {
        0%, 100% { box-shadow: 0 0 20px rgba(255, 107, 107, 0.4); }
        50% { box-shadow: 0 0 40px rgba(255, 107, 107, 0.8); }
    }
</style>
@endpush

@section('page-content')
    <div class="content container-fluid">

        @php
            $tz = LocaleSettings('timezone') ?? config('app.timezone');
            $start = \Carbon\Carbon::now($tz)->startOfDay()->setTimezone('UTC');
            $end = \Carbon\Carbon::now($tz)->endOfDay()->setTimezone('UTC');
            $todayClockin = \App\Models\Attendance::where('user_id', auth()->id())
                ->whereBetween('created_at', [$start, $end])
                ->first();
            $isClockedIn = false;
            $clockInTime = null;
            $hoursWorked = 0;
            
            if(!empty($todayClockin)){
                $latestClockin = $todayClockin->timestamps()->latest()->whereNull('endTime')->first();
                if(!empty($latestClockin)){
                    $isClockedIn = true;
                    $clockInTime = \Carbon\Carbon::parse($latestClockin->startTime, 'UTC')->setTimezone($tz);
                    $hoursWorked = $clockInTime->diffInHours(\Carbon\Carbon::now($tz));
                }
            }
        @endphp

        <!-- Clock-Out Reminder Alert -->
        @if($isClockedIn && $hoursWorked >= 8)
        <div class="alert alert-clock-out mb-4" role="alert">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa fa-exclamation-triangle fa-2x"></i>
                    <div>
                        <h5 class="mb-1">⏰ Don't Forget to Clock Out!</h5>
                        <p class="mb-0">You've been clocked in for {{ $hoursWorked }} hours. Remember to clock out when you finish your shift.</p>
                    </div>
                </div>
                <a href="{{ route('attendances.index') }}" class="btn btn-light">
                    <i class="fa fa-clock"></i> Clock Out Now
                </a>
            </div>
        </div>
        @endif

        <!-- Welcome Header -->
        <div class="employee-welcome-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="employee-badge mb-3">
                        <i class="fa fa-id-badge"></i> EMPLOYEE
                    </span>
                    <h2 class="mb-2 mt-3">Welcome back, {{ auth()->user()->firstname }}! 👋</h2>
                    <p class="mb-0 opacity-75">{{ \Carbon\Carbon::now()->format('l, F d, Y') }}</p>
                </div>
                <div class="text-end">
                    <div class="d-flex flex-column align-items-end gap-3">
                        <!-- Clock Status Indicator -->
                        <div class="clock-status-badge {{ $isClockedIn ? 'clocked-in' : 'clocked-out' }}">
                            <span class="status-dot"></span>
                            @if($isClockedIn)
                                <span>
                                    <i class="fa fa-clock"></i> Clocked In
                                    <small class="d-block" style="font-size: 12px; opacity: 0.8;">Since {{ $clockInTime->format('g:i A') }}</small>
                                </span>
                            @else
                                <span>
                                    <i class="fa fa-clock"></i> Not Clocked In
                                </span>
                            @endif
                        </div>
                        
                        @if(auth()->user()->avatar)
                            <img src="{{ asset(auth()->user()->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 80px; height: 80px; border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: white; color: #ff902f; font-size: 32px; font-weight: bold; border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                                {{ strtoupper(substr(auth()->user()->firstname, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stat-card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Leave Credits</p>
                                <h3 class="mb-0">{{ auth()->user()->leaveToken->tokens ?? 0 }}</h3>
                            </div>
                            <div class="text-primary" style="font-size: 2.5rem; opacity: 0.3;">
                                <i class="fa fa-coins"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stat-card border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Monthly Tokens</p>
                                <h3 class="mb-0">{{ auth()->user()->monthlyToken->tokens ?? 0 }}</h3>
                            </div>
                            <div class="text-warning" style="font-size: 2.5rem; opacity: 0.3;">
                                <i class="fa fa-trophy"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stat-card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">This Week Hours</p>
                                <h3 class="mb-0">{{ $totalHoursThisWeek ?? 0 }}h</h3>
                            </div>
                            <div class="text-success" style="font-size: 2.5rem; opacity: 0.3;">
                                <i class="fa fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stat-card border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Open Tickets</p>
                                <h3 class="mb-0">{{ auth()->user()->tickets()->where('status', 'Open')->count() ?? 0 }}</h3>
                            </div>
                            <div class="text-info" style="font-size: 2.5rem; opacity: 0.3;">
                                <i class="fa fa-ticket"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Schedule Card -->
        @if(auth()->user()->schedule)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ff902f 0%, #ff6b35 100%);">
                    <div class="card-body text-white">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fa fa-calendar-alt fa-2x me-3"></i>
                                    <div>
                                        <h5 class="text-white mb-1">Your Work Schedule: {{ auth()->user()->schedule->name }}</h5>
                                        <p class="mb-0 opacity-75">{{ auth()->user()->schedule->description }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="d-inline-block bg-white bg-opacity-25 rounded p-3">
                                    <div class="mb-2">
                                        <i class="fa fa-clock me-2"></i>
                                        <strong>{{ auth()->user()->schedule->time_range }}</strong>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fa fa-hourglass-half me-2"></i>
                                        {{ auth()->user()->schedule->work_hours }} hours per day
                                    </div>
                                    <div>
                                        <i class="fa fa-calendar-week me-2"></i>
                                        {{ auth()->user()->schedule->working_days }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fa fa-bolt text-warning"></i> Quick Actions
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('leaves.create') }}" class="text-decoration-none">
                                    <div class="quick-action-card card h-100">
                                        <div class="card-body text-center">
                                            <i class="fa fa-calendar-plus fa-2x text-primary mb-2"></i>
                                            <h6 class="mb-0">Apply for Leave</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('monthly-tokens.index') }}" class="text-decoration-none">
                                    <div class="quick-action-card card h-100">
                                        <div class="card-body text-center">
                                            <i class="fa fa-exchange-alt fa-2x text-warning mb-2"></i>
                                            <h6 class="mb-0">Convert Tokens</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('payslips.index') }}" class="text-decoration-none">
                                    <div class="quick-action-card card h-100">
                                        <div class="card-body text-center">
                                            <i class="fa fa-file-invoice-dollar fa-2x text-success mb-2"></i>
                                            <h6 class="mb-0">View Payslips</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('tickets.index') }}" class="text-decoration-none">
                                    <div class="quick-action-card card h-100">
                                        <div class="card-body text-center">
                                            <i class="fa fa-life-ring fa-2x text-info mb-2"></i>
                                            <h6 class="mb-0">Support Tickets</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Credits Details -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fa-solid fa-coins text-warning"></i> Leave Credit Balance
                        </h5>
                        @php
                            $leaveToken = auth()->user()->leaveToken;
                            $availableTokens = $leaveToken->tokens ?? 0;
                            $earnedTokens = $leaveToken->earned_tokens ?? 0;
                            $usedTokens = $leaveToken->used_tokens ?? 0;
                        @endphp
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="credit-box border-success">
                                    <i class="fa-solid fa-coins text-success fa-2x mb-2"></i>
                                    <h2 class="text-success">{{ $availableTokens }}</h2>
                                    <p class="mb-0 text-muted">Available Credits</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="credit-box border-primary">
                                    <i class="fa-solid fa-award text-primary fa-2x mb-2"></i>
                                    <h2 class="text-primary">{{ $earnedTokens }}</h2>
                                    <p class="mb-0 text-muted">Total Earned</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="credit-box border-info">
                                    <i class="fa-solid fa-check-circle text-info fa-2x mb-2"></i>
                                    <h2 class="text-info">{{ $usedTokens }}</h2>
                                    <p class="mb-0 text-muted">Total Used</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-banner mt-3">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-lightbulb fa-2x me-3"></i>
                                <div>
                                    <strong>How to earn credits:</strong> Maintain perfect attendance for a full week (Monday-Friday) to earn 1 leave credit. 
                                    Credits can be used when requesting leave.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection


@push('page-scripts')
@endpush
