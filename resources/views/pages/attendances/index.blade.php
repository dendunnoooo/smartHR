@extends('layouts.app')

@push('page-styles')
<style>
    /* Filter Form Styling */
    .filter-row .form-control, 
    .filter-row .select {
        height: 46px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-size: 14px;
        padding: 0 15px;
    }
    .filter-row .form-control:focus,
    .filter-row .select:focus {
        border-color: #ff902f;
        box-shadow: 0 0 0 0.2rem rgba(255, 144, 47, 0.1);
    }
    .filter-row .btn {
        height: 46px;
        border-radius: 4px;
    }
    .filter-row .input-block {
        margin-bottom: 0 !important;
    }
    
    .attendance-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 15px;
        box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .attendance-card:hover {
        box-shadow: 0 2px 4px 0 rgba(0,0,0,0.1);
    }
    .employee-header {
        padding: 20px;
        background: white;
        border-bottom: 1px solid #e0e0e0;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .employee-header:hover {
        background: #f8f9fa;
    }
    .employee-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 2px solid #e0e0e0;
        object-fit: cover;
    }
    .employee-name {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }
    .employee-email {
        font-size: 13px;
        color: #757575;
    }
    .stats-card {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        text-align: center;
        border: 1px solid #e0e0e0;
    }
    .stats-number {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .stats-label {
        font-size: 11px;
        color: #757575;
        text-transform: uppercase;
        font-weight: 500;
    }
    .attendance-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(45px, 1fr));
        gap: 6px;
        padding: 20px;
        background: white;
    }
    .day-cell {
        aspect-ratio: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s;
        cursor: pointer;
        border: 1px solid;
    }
    .day-cell.present {
        background: #e8f5e9;
        color: #2e7d32;
        border-color: #4caf50;
    }
    .day-cell.present:hover {
        background: #4caf50;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(76,175,80,0.3);
    }
    .day-cell.absent {
        background: #ffebee;
        color: #c62828;
        border-color: #f44336;
    }
    .day-cell.absent:hover {
        background: #ffcdd2;
    }
    .day-cell.future {
        background: #fafafa;
        color: #bdbdbd;
        border-color: #e0e0e0;
        cursor: not-allowed;
    }
    .day-number {
        font-size: 14px;
        font-weight: 600;
    }
    .day-status {
        font-size: 9px;
        margin-top: 2px;
    }
    .collapse-toggle {
        transition: transform 0.3s;
        color: #757575;
    }
    .collapsed .collapse-toggle {
        transform: rotate(0deg);
    }
    .employee-header:not(.collapsed) .collapse-toggle {
        transform: rotate(180deg);
    }
    .month-badge {
        background: #f5f5f5;
        color: #616161;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #e0e0e0;
    }
    .summary-footer {
        padding: 15px 20px;
        background: #fafafa;
        border-top: 1px solid #e0e0e0;
    }
    .summary-item {
        text-align: center;
    }
    .summary-label {
        font-size: 11px;
        color: #757575;
        text-transform: uppercase;
        font-weight: 500;
        margin-bottom: 5px;
    }
    .summary-value {
        font-size: 18px;
        font-weight: 700;
    }
</style>
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">{{ __('Attendance') }} - {{ $monthName }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Attendance List') }}
                </li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <form action="" method="get">
            <div x-data="{employee: '{{ request()->employee }}', month: '{{ $selectedMonth }}',year: '{{ $selectedYear }}'}" class="row filter-row mb-4">
                <div class="col-sm-6 col-md-3">  
                    <div class="input-block mb-3">
                        <input type="text" name="employee" x-model="employee" class="form-control" placeholder="Search employee...">
                    </div>
                </div>
                <div class="col-sm-6 col-md-3"> 
                    <div class="input-block mb-3">
                        <select name="month" x-model="month" class="form-control select"> 
                            <option value="01" {{ $selectedMonth == '01' ? 'selected' : '' }}>{{ __('January') }}</option>
                            <option value="02" {{ $selectedMonth == '02' ? 'selected' : '' }}>{{ __('February') }}</option>
                            <option value="03" {{ $selectedMonth == '03' ? 'selected' : '' }}>{{ __('March') }}</option>
                            <option value="04" {{ $selectedMonth == '04' ? 'selected' : '' }}>{{ __('April') }}</option>
                            <option value="05" {{ $selectedMonth == '05' ? 'selected' : '' }}>{{ __('May') }}</option>
                            <option value="06" {{ $selectedMonth == '06' ? 'selected' : '' }}>{{ __('June') }}</option>
                            <option value="07" {{ $selectedMonth == '07' ? 'selected' : '' }}>{{ __('July') }}</option>
                            <option value="08" {{ $selectedMonth == '08' ? 'selected' : '' }}>{{ __('August') }}</option>
                            <option value="09" {{ $selectedMonth == '09' ? 'selected' : '' }}>{{ __('September') }}</option>
                            <option value="10" {{ $selectedMonth == '10' ? 'selected' : '' }}>{{ __('October') }}</option>
                            <option value="11" {{ $selectedMonth == '11' ? 'selected' : '' }}>{{ __('November') }}</option>
                            <option value="12" {{ $selectedMonth == '12' ? 'selected' : '' }}>{{ __('December') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3"> 
                    <div class="input-block mb-3">
                        <select name="year" x-model="year" class="form-control select"> 
                            @foreach ($years_range as $year)
                            <option {{ $selectedYear == $year->year ? 'selected' : '' }}>{{$year->year}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">  
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> {{ __('Search') }}</button>
                    </div>
                </div>       
            </div>
        </form> 
        <!-- /Search Filter -->
        
        @if($employees->isEmpty())
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> {{ __('No employees found for the selected criteria.') }}
            </div>
        @else
            <!-- Employee Attendance Cards -->
            @foreach ($employees as $employee)
                @php
                    $img = !empty($employee->avatar) ? asset('storage/users/'.$employee->avatar): asset('images/user.jpg');
                    $link = route('employees.show', ['employee' => Crypt::encrypt($employee->id)]);
                    $attendancePercentage = $days_in_month > 0 ? round(($employee->present_days / $days_in_month) * 100, 1) : 0;
                @endphp
                
                <div class="attendance-card">
                    <!-- Employee Header -->
                    <div class="employee-header collapsed" data-bs-toggle="collapse" data-bs-target="#attendance-{{ $employee->id }}" aria-expanded="false">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $img }}" alt="{{ $employee->fullname }}" class="employee-avatar me-3">
                                    <div>
                                        <div class="employee-name">{{ $employee->fullname }}</div>
                                        <div class="employee-email">{{ $employee->email }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="row g-2">
                                    <div class="col-3">
                                        <div class="stats-card">
                                            <div class="stats-number text-success">{{ $employee->present_days }}</div>
                                            <div class="stats-label text-success">{{ __('Present Days') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="stats-card">
                                            <div class="stats-number text-danger">{{ $employee->absent_days }}</div>
                                            <div class="stats-label text-danger">{{ __('Absent Days') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="stats-card">
                                            <div class="stats-number text-primary">{{ $attendancePercentage }}%</div>
                                            <div class="stats-label text-primary">{{ __('Attendance') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="stats-card">
                                            <div class="stats-number text-muted">{{ $days_in_month }}</div>
                                            <div class="stats-label">{{ __('Total Days') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1 text-end">
                                <i class="fa fa-chevron-down collapse-toggle fs-5"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Grid (Collapsible) -->
                    <div class="collapse" id="attendance-{{ $employee->id }}">
                        <div class="attendance-grid">
                            @for ($day = 1; $day <= $days_in_month; $day++)
                                @php
                                    $currentDate = \Carbon\Carbon::create($selectedYear, $selectedMonth, $day);
                                    $isFuture = $currentDate->isFuture();
                                    $attendance = $employee->attendances->first(function($att) use ($day, $selectedMonth, $selectedYear) {
                                        $startDate = \Carbon\Carbon::parse($att->startDate);
                                        return $startDate->day == $day && 
                                               $startDate->month == $selectedMonth && 
                                               $startDate->year == $selectedYear;
                                    });
                                    $isPresent = !empty($attendance);
                                    $dayName = $currentDate->format('D');
                                @endphp
                                
                                @if($isPresent)
                                    <div class="day-cell present" 
                                         data-ajax-modal="true" 
                                         data-title="{{ __('Attendance Details') }} - {{ $employee->fullname }}" 
                                         data-size="lg" 
                                         data-url="{{ route('attendance.details', $attendance->id) }}"
                                         data-bs-toggle="tooltip"
                                         title="{{ __('Present on') }} {{ $currentDate->format('M d, Y') }} ({{ $dayName }}) - Click for details">
                                        <div class="day-number">{{ $day }}</div>
                                        <div class="day-status"><i class="fa fa-check"></i></div>
                                    </div>
                                @elseif($isFuture)
                                    <div class="day-cell future" 
                                         data-bs-toggle="tooltip"
                                         title="{{ $currentDate->format('M d, Y') }} ({{ $dayName }}) - Future date">
                                        <div class="day-number">{{ $day }}</div>
                                        <div class="day-status">-</div>
                                    </div>
                                @else
                                    <div class="day-cell absent"
                                         data-bs-toggle="tooltip"
                                         title="{{ __('Absent on') }} {{ $currentDate->format('M d, Y') }} ({{ $dayName }})">
                                        <div class="day-number">{{ $day }}</div>
                                        <div class="day-status"><i class="fa fa-times"></i></div>
                                    </div>
                                @endif
                            @endfor
                        </div>
                        
                        <!-- Summary Footer -->
                        <div class="summary-footer">
                            <div class="row">
                                <div class="col-md-3 summary-item">
                                    <div class="summary-label">{{ __('Total Days') }}</div>
                                    <div class="summary-value text-muted">{{ $days_in_month }}</div>
                                </div>
                                <div class="col-md-3 summary-item">
                                    <div class="summary-label">{{ __('Present') }}</div>
                                    <div class="summary-value text-success">{{ $employee->present_days }}</div>
                                </div>
                                <div class="col-md-3 summary-item">
                                    <div class="summary-label">{{ __('Absent') }}</div>
                                    <div class="summary-value text-danger">{{ $employee->absent_days }}</div>
                                </div>
                                <div class="col-md-3 summary-item">
                                    <div class="summary-label">{{ __('Attendance Rate') }}</div>
                                    <div class="summary-value text-primary">{{ $attendancePercentage }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

    </div>
@endsection

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Handle collapse toggle icon rotation
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(element) {
            element.addEventListener('click', function() {
                this.classList.toggle('collapsed');
            });
        });
    });
</script>
@endpush
