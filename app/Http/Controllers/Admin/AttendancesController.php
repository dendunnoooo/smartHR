<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Enums\UserType;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class AttendancesController extends Controller
{
    
    public function index(Request $request){

        $pageTitle = __('Attendances');

        // Check if the current user is an employee
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->type === UserType::EMPLOYEE) {
            // Show the employee attendance timesheet view
            return view('pages.attendances.employee-timesheet', compact('pageTitle'));
        }

        $selectedMonth = $request->month ?? Carbon::now()->month;
        $selectedYear = $request->year ?? Carbon::now()->year;

        $years_range = CarbonPeriod::create(now()->subYears(10), Carbon::now()->addYears(10))->years();
        $days_in_month = Carbon::createFromDate($selectedYear, $selectedMonth,01)->daysInMonth;
        
        $users = User::with(['attendances' => function ($query) use ($selectedMonth,$selectedYear) {
            $query->whereMonth('startDate', $selectedMonth)
                ->whereYear('startDate', $selectedYear);
        }])->where('type', UserType::EMPLOYEE);
        
        if(!empty($request->employee)){
            $users = $users->where(function($q) use ($request) {
                $q->where('email','LIKE','%'.$request->employee.'%')
                    ->orWhere('firstname','LIKE','%'.$request->employee.'%')
                    ->orWhere('middlename','LIKE','%'.$request->employee.'%')
                    ->orWhere('lastname','LIKE','%'.$request->employee.'%')
                    ->orWhere('username','LIKE','%'.$request->employee.'%');
            });
        }
        
        $employees = $users->get()->map(function($employee) use ($selectedMonth, $selectedYear, $days_in_month) {
            $presentDays = $employee->attendances->count();
            $absentDays = $days_in_month - $presentDays;
            $employee->present_days = $presentDays;
            $employee->absent_days = $absentDays;
            $employee->total_days = $days_in_month;
            return $employee;
        });
        
        $monthName = Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y');
        
        return view('pages.attendances.index',compact(
            'pageTitle','employees','years_range','days_in_month','monthName','selectedMonth','selectedYear'
        ));
    }

    public function attendanceDetails(Request $request, Attendance $attendance)
    {
        $attendanceActivity = $attendance->timestamps()->get();
        $totalHours = $attendance->timestamps()->get()->sum(function($timestamp) {
            return $timestamp->total_hours_numeric ?? 0;
        });
        return view('pages.attendances.attendance-details',compact(
            'attendance','totalHours','attendanceActivity'
        ));
    }
}
