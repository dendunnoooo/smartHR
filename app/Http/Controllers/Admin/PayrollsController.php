<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Enums\UserType;
use App\Models\Payslip;
use Illuminate\Http\Request;
use App\Models\EmployeeDetail;
use Illuminate\Support\Carbon;
use App\Enums\Payroll\SalaryType;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeDeduction;
use App\Models\Attendance;
use App\Models\AttendanceTimestamp;
use App\DataTables\PayslipDataTable;
use App\Http\Controllers\Controller;
use App\Models\PayslipItem;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PayslipCreatedNotification;

class PayrollsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PayslipDataTable $dataTable)
    {
        $pageTitle = __('Payslips');
        // If the current user is an employee, redirect them to their latest payslip preview (if any)
        try{
            $user = Auth::user();
            if($user && $user->type === UserType::EMPLOYEE){
                $employeeDetail = $user->employeeDetail;
                if(!empty($employeeDetail) && !empty($employeeDetail->id)){
                    $latest = Payslip::where('employee_detail_id', $employeeDetail->id)->latest('id')->first();
                    if($latest){
                        return redirect()->route('payslips.show', ['payslip' => Crypt::encrypt($latest->id)]);
                    }
                }
            }
        }catch(\Exception $e){
            // fail silently and show the index
        }

        return $dataTable->render('pages.payroll.payslips.index',compact(
            'pageTitle'
        ));
    }

    public function items(){
        $pageTitle = __('Payroll Items');
        $allowances = EmployeeAllowance::get();
        $deductions = EmployeeDeduction::get();
        return view('pages.payroll.items',compact(
            'pageTitle','allowances','deductions'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = User::where('is_active', true)->where('type', UserType::EMPLOYEE)->get();
        return view('pages.payroll.payslips.create',compact(
            'employees'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee' => 'required',
            'type' => 'required',
            'payslip_date' => 'nullable|date',
            'title' => 'required_if:type,contract',
            'from_date' => 'required_if:type,hourly',
            'weeks' => 'required_if:type,weekly',
        ]);

        // Validate payslip generation date for all types
        $today = Carbon::now();
        $isValidDate = $today->day === 15 || $today->isLastOfMonth();
        
        if (!$isValidDate && !$request->has('force')) {
            $day = $today->day;
            $suffix = ($day === 1) ? 'st' : (($day === 2) ? 'nd' : (($day === 3) ? 'rd' : 'th'));
            $typeLabel = ucfirst(str_replace('-', ' ', $request->type));
            
            return back()->withErrors([
                'type' => $typeLabel . ' payslips can only be generated on the 15th or last day of the month. Today is the ' . $day . $suffix . '.'
            ])->withInput();
        }

        $employee = EmployeeDetail::findOrFail($request->employee);
        $salaryInfo = $employee->salaryDetails;
    $deductions = 0;
    $allowances = 0;
    $total_hours = 0;
    $base_salary = $salaryInfo ? $salaryInfo->base_salary : 0;
        $allowancesItems = null;
        $deductionItems = null;
        if(!empty($request->use_allowance)){
            $allowancesItems = EmployeeAllowance::where('employee_detail_id',$employee->id)->get();
            $allowances = $allowancesItems->sum('amount');
        }
        // Apply global salary settings-derived allowances and deductions (DA/HRA, PF / ESI, Pag-IBIG, Tax)
        try{
            $salarySettings = SalarySettings();

            // Allowances (DA / HRA) — apply when enabled in settings
            if(!empty($salarySettings->enable_da_hra)){
                // COLA (DA)
                if(!empty($salarySettings->da_percent)){
                    $daAmount = ($base_salary * floatval($salarySettings->da_percent)) / 100;
                    $cola = EmployeeAllowance::firstOrCreate([
                        'employee_detail_id' => $employee->id,
                        'name' => 'COLA (Auto)'
                    ], [
                        'amount' => $daAmount
                    ]);
                    if(empty($allowancesItems)){
                        $allowancesItems = collect();
                        $allowancesItems->push($cola);
                    } else {
                        // avoid duplicating the same allowance if it was already loaded from DB
                        if(!$allowancesItems->contains('id', $cola->id)){
                            $allowancesItems->push($cola);
                        }
                    }
                    $allowances += $daAmount;
                }
                // HRA
                if(!empty($salarySettings->hra_percent)){
                    $hraAmount = ($base_salary * floatval($salarySettings->hra_percent)) / 100;
                    $hra = EmployeeAllowance::firstOrCreate([
                        'employee_detail_id' => $employee->id,
                        'name' => 'HRA (Auto)'
                    ], [
                        'amount' => $hraAmount
                    ]);
                    if(empty($allowancesItems)){
                        $allowancesItems = collect();
                        $allowancesItems->push($hra);
                    } else {
                        // avoid duplicating the same allowance if it was already loaded from DB
                        if(!$allowancesItems->contains('id', $hra->id)){
                            $allowancesItems->push($hra);
                        }
                    }
                    $allowances += $hraAmount;
                }
            }

            // Provident Fund (employee share)
            if(!empty($salarySettings->enable_provident_fund) && !empty($salarySettings->emp_pf_percentage)){
                $pfAmount = ($base_salary * floatval($salarySettings->emp_pf_percentage)) / 100;
                // create or reuse a system deduction for this employee
                $pfDeduction = EmployeeDeduction::firstOrCreate([
                    'employee_detail_id' => $employee->id,
                    'name' => 'Provident Fund (Auto)'
                ], [
                    'amount' => $pfAmount
                ]);
                if(empty($deductionItems)) $deductionItems = collect();
                $deductionItems->push($pfDeduction);
                $deductions += $pfAmount;
            }
            // ESI (employee share)
            if(!empty($salarySettings->enable_esi_fund) && !empty($salarySettings->emp_esi_percentage)){
                $esiAmount = ($base_salary * floatval($salarySettings->emp_esi_percentage)) / 100;
                $esiDeduction = EmployeeDeduction::firstOrCreate([
                    'employee_detail_id' => $employee->id,
                    'name' => 'ESI (Auto)'
                ], [
                    'amount' => $esiAmount
                ]);
                if(empty($deductionItems)) $deductionItems = collect();
                $deductionItems->push($esiDeduction);
                $deductions += $esiAmount;
            }

            // Pag-IBIG / other fund (employee share)
            if(!empty($salarySettings->emp_pagibig_percentage)){
                $pagibigAmount = ($base_salary * floatval($salarySettings->emp_pagibig_percentage)) / 100;
                $pagibigDeduction = EmployeeDeduction::firstOrCreate([
                    'employee_detail_id' => $employee->id,
                    'name' => 'Pag-IBIG (Auto)'
                ], [
                    'amount' => $pagibigAmount
                ]);
                if(empty($deductionItems)) $deductionItems = collect();
                $deductionItems->push($pagibigDeduction);
                $deductions += $pagibigAmount;
            }

            // Withholding Tax (TRAIN Law - RA 10963) — calculate based on progressive tax brackets
            if(!empty($salarySettings->enable_tax)){
                $taxAmount = \App\Helpers\TrainTaxCalculator::calculateMonthlyTax($base_salary);
                
                if($taxAmount > 0){
                    $taxDeduction = EmployeeDeduction::firstOrCreate([
                        'employee_detail_id' => $employee->id,
                        'name' => 'Withholding Tax (TRAIN Law)'
                    ], [
                        'amount' => $taxAmount
                    ]);
                    // Ensure amount is up-to-date based on current salary
                    if((float)$taxDeduction->amount !== (float)$taxAmount){
                        $taxDeduction->amount = $taxAmount;
                        $taxDeduction->save();
                    }
                    if(empty($deductionItems)) $deductionItems = collect();
                    $deductionItems->push($taxDeduction);
                    $deductions += $taxAmount;
                }
            }

            // Absent Deduction — calculate based on attendance records
            if(!empty($salarySettings->enable_absent_deduction)){
                $fromDate = $request->from_date ?? now()->startOfMonth();
                $toDate = $request->to_date ?? now()->endOfMonth();
                
                // Count total days in period
                $totalDays = \Carbon\Carbon::parse($fromDate)->diffInDays(\Carbon\Carbon::parse($toDate)) + 1;
                
                // Count present days (days with attendance records)
                $presentDays = Attendance::where('user_id', $employee->user_id)
                    ->whereBetween('startDate', [$fromDate, $toDate])
                    ->count();
                
                // Calculate absent days
                $absentDays = max(0, $totalDays - $presentDays);
                
                if($absentDays > 0){
                    // Calculate daily rate
                    $daysPerMonth = ($salarySettings->absent_calculation_method === 'working_days') ? 22 : 30;
                    $dailyRate = $base_salary / $daysPerMonth;
                    
                    // Calculate deduction amount
                    if(!empty($salarySettings->absent_deduction_percent)){
                        // Use percentage of daily rate
                        $absentAmount = round($absentDays * ($dailyRate * floatval($salarySettings->absent_deduction_percent) / 100), 2);
                    } else {
                        // Use fixed amount
                        $absentAmount = round($absentDays * floatval($salarySettings->absent_deduction_amount ?? 500), 2);
                    }
                    
                    $absentDeduction = EmployeeDeduction::firstOrCreate([
                        'employee_detail_id' => $employee->id,
                        'name' => 'Absent Deduction (Auto)'
                    ], [
                        'amount' => $absentAmount
                    ]);
                    $absentDeduction->update(['amount' => $absentAmount]);
                    if(empty($deductionItems)) $deductionItems = collect();
                    $deductionItems->push($absentDeduction);
                    $deductions += $absentAmount;
                }
            }

            // Overtime Pay — calculate based on overtime hours recorded in attendance
            if(!empty($salarySettings->enable_overtime)){
                $fromDate = $request->from_date ?? now()->startOfMonth();
                $toDate = $request->to_date ?? now()->endOfMonth();
                
                // Get total overtime hours from attendance records
                $totalOvertimeHours = Attendance::where('user_id', $employee->user_id)
                    ->whereBetween('startDate', [$fromDate, $toDate])
                    ->sum('overtime_hours');
                
                if($totalOvertimeHours > 0){
                    // Calculate hourly rate
                    $daysPerMonth = ($salarySettings->overtime_calculation_method === 'working_days') ? 22 : 30;
                    $hoursPerDay = floatval($salarySettings->overtime_threshold_hours ?? 8);
                    $hourlyRate = $base_salary / ($daysPerMonth * $hoursPerDay);
                    
                    // Calculate overtime pay with multiplier
                    $overtimeMultiplier = floatval($salarySettings->overtime_rate_multiplier ?? 1.25);
                    $overtimeAmount = round($totalOvertimeHours * $hourlyRate * $overtimeMultiplier, 2);
                    
                    $overtimeAllowance = EmployeeAllowance::firstOrCreate([
                        'employee_detail_id' => $employee->id,
                        'name' => 'Overtime Pay (Auto)'
                    ], [
                        'amount' => $overtimeAmount
                    ]);
                    $overtimeAllowance->update(['amount' => $overtimeAmount]);
                    if(empty($allowancesItems)) $allowancesItems = collect();
                    $allowancesItems->push($overtimeAllowance);
                    $allowances += $overtimeAmount;
                }
            }

            // Undertime Deduction — calculate based on undertime hours recorded in attendance
            if(!empty($salarySettings->enable_undertime)){
                $fromDate = $request->from_date ?? now()->startOfMonth();
                $toDate = $request->to_date ?? now()->endOfMonth();
                
                // Get total undertime hours from attendance records
                $totalUndertimeHours = Attendance::where('user_id', $employee->user_id)
                    ->whereBetween('startDate', [$fromDate, $toDate])
                    ->sum('undertime_hours');
                
                if($totalUndertimeHours > 0){
                    // Calculate hourly rate
                    $daysPerMonth = ($salarySettings->undertime_calculation_method === 'working_days') ? 22 : 30;
                    $hoursPerDay = floatval($salarySettings->undertime_threshold_hours ?? 8);
                    $hourlyRate = $base_salary / ($daysPerMonth * $hoursPerDay);
                    
                    // Calculate undertime deduction with multiplier
                    $undertimeMultiplier = floatval($salarySettings->undertime_rate_multiplier ?? 1);
                    $undertimeAmount = round($totalUndertimeHours * $hourlyRate * $undertimeMultiplier, 2);
                    
                    $undertimeDeduction = EmployeeDeduction::firstOrCreate([
                        'employee_detail_id' => $employee->id,
                        'name' => 'Undertime Deduction (Auto)'
                    ], [
                        'amount' => $undertimeAmount
                    ]);
                    $undertimeDeduction->update(['amount' => $undertimeAmount]);
                    if(empty($deductionItems)) $deductionItems = collect();
                    $deductionItems->push($undertimeDeduction);
                    $deductions += $undertimeAmount;
                }
            }

            // Late Deduction — calculate based on late minutes recorded in attendance timestamps
            if(!empty($salarySettings->enable_late_deduction)){
                $fromDate = $request->from_date ?? now()->startOfMonth();
                $toDate = $request->to_date ?? now()->endOfMonth();
                
                // Get grace period in minutes
                $graceMinutes = floatval($salarySettings->late_grace_minutes ?? 0);
                
                // Get all late timestamps within the payroll period
                $lateTimestamps = \App\Models\AttendanceTimestamp::where('user_id', $employee->user_id)
                    ->where('is_late', true)
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->get();
                
                // Calculate total late minutes beyond grace period
                $totalLateMinutes = $lateTimestamps->sum(function($timestamp) use ($graceMinutes) {
                    $lateMinutes = abs($timestamp->minutes_difference ?? 0);
                    return max(0, $lateMinutes - $graceMinutes);
                });
                
                if($totalLateMinutes > 0){
                    // Calculate deduction per minute
                    $deductionPerMinute = floatval($salarySettings->late_deduction_per_minute ?? 0);
                    
                    // If deduction per minute is 0, calculate based on hourly rate
                    if($deductionPerMinute == 0){
                        $daysPerMonth = 22; // Working days
                        $hoursPerDay = 8;
                        $hourlyRate = $base_salary / ($daysPerMonth * $hoursPerDay);
                        $deductionPerMinute = $hourlyRate / 60;
                    }
                    
                    $lateDeductionAmount = round($totalLateMinutes * $deductionPerMinute, 2);
                    
                    $lateDeduction = EmployeeDeduction::firstOrCreate([
                        'employee_detail_id' => $employee->id,
                        'name' => 'Late Deduction (Auto)'
                    ], [
                        'amount' => $lateDeductionAmount
                    ]);
                    $lateDeduction->update(['amount' => $lateDeductionAmount]);
                    if(empty($deductionItems)) $deductionItems = collect();
                    $deductionItems->push($lateDeduction);
                    $deductions += $lateDeductionAmount;
                }
            }
        } catch (\Throwable $e) {
            // ignore if settings not available
        }
        
        // Add approved token conversions to cash as allowance
        try {
            $tokenConversions = \App\Models\TokenConversion::where('user_id', $employee->user_id)
                ->where('status', 'approved')
                ->where('conversion_type', 'cash')
                ->where('included_in_payroll', false)
                ->get();
            
            if ($tokenConversions->isNotEmpty()) {
                $totalTokenCash = $tokenConversions->sum('cash_amount');
                
                if ($totalTokenCash > 0) {
                    $tokenAllowance = \App\Models\EmployeeAllowance::firstOrCreate([
                        'employee_detail_id' => $employee->id,
                        'name' => 'Monthly Token Conversion (Auto)'
                    ], [
                        'amount' => $totalTokenCash
                    ]);
                    $tokenAllowance->update(['amount' => $totalTokenCash]);
                    
                    if(empty($allowancesItems)) $allowancesItems = collect();
                    $allowancesItems->push($tokenAllowance);
                    $allowances += $totalTokenCash;
                    
                    // Mark conversions as included in payroll
                    \App\Models\TokenConversion::whereIn('id', $tokenConversions->pluck('id'))
                        ->update(['included_in_payroll' => true, 'payroll_date' => now()]);
                }
            }
        } catch (\Throwable $e) {
            // ignore if token conversions not available
        }
        
        if(!empty($request->use_deductions)){
            $deductionItems = EmployeeDeduction::where('employee_detail_id',$employee->id)->get();
            $deductions = $deductionItems->sum('amount');
        }
        $net_pay = round(($base_salary + $allowances) - $deductions, 2);
        if($request->type === SalaryType::Hourly){
            $total_hours = AttendanceTimestamp::where('user_id',$employee->user_id)->whereBetween('created_at',[Carbon::parse($request->from_date), Carbon::parse($request->to_date)])
                ->whereNotNull(['attendance_id','startTime','endTime'])->sum('totalHours');
            $hourly_pay = ($total_hours * $base_salary);
            $net_pay = round(($hourly_pay + $allowances) - $deductions, 2);
        }
        if($request->type === SalaryType::Weekly){
            $weeks_salary = ($request->weeks * $base_salary);
            $net_pay = round(($weeks_salary + $allowances) - $deductions, 2);
        }
        
        // Bi-Monthly: Divide by 2 and set cutoff details
        $cutoffNumber = null;
        $cutoffPeriod = null;
        $isBiMonthly = false;
        
        if($request->type === 'bi-monthly'){
            $isBiMonthly = true;
            $today = Carbon::now();
            
            // Determine cutoff based on today's date
            if($today->day === 15){
                $cutoffNumber = 1;
                $cutoffPeriod = '1st Cutoff (1-15)';
            } else if($today->isLastOfMonth()){
                $cutoffNumber = 2;
                $lastDay = $today->day;
                $cutoffPeriod = "2nd Cutoff (16-{$lastDay})";
            }
            
            // Divide all amounts by 2 for bi-monthly
            $base_salary = $base_salary / 2;
            $allowances = $allowances / 2;
            $deductions = $deductions / 2;
            $net_pay = round(($base_salary + $allowances) - $deductions, 2);
        }
        
        $payslip = Payslip::create([
            'ps_id' => pad_zeros(Payslip::count()+1),
            'title' => $request->title ?? $cutoffPeriod,
            'employee_detail_id' => $employee->id,
            'use_allowance' => !empty($request->use_allowance),
            'use_deduction' => !empty($request->use_deductions),
            'payslip_date' => $request->payslip_date,
            'type' => $request->type,
            'startDate' => $request->from_date,
            'endDate' => $request->to_date,
            'total_hours' => $total_hours,
            'weeks' => $request->weeks,
            'net_pay' => $net_pay,
            'cutoff_number' => $cutoffNumber,
            'cutoff_period' => $cutoffPeriod,
            'is_semi_monthly' => $isBiMonthly,
        ]);
        if(!empty($allowancesItems)){
            PayslipItem::insert($allowancesItems->map(function(EmployeeAllowance $item) use($payslip){
                return [
                    'type' => 'allowance',
                    'payslip_id' => $payslip->id,
                    'item_id' => $item->id
                ];
            })->all());
        }
        if(!empty($deductionItems)){
            PayslipItem::insert($deductionItems->map(function(EmployeeDeduction $item) use($payslip){
                return [
                    'type' => 'deduction',
                    'payslip_id' => $payslip->id,
                    'item_id' => $item->id
                ];
            })->all());
        }
        // Notify the employee that they have received a new payslip (database + email)
        try{
            $employeeUser = User::find($employee->user_id);
            if($employeeUser){
                $employeeUser->notify(new PayslipCreatedNotification($payslip));
            }
        } catch(\Throwable $e){
            // swallow notification errors so main flow doesn't break
        }
        $notification = notify(__('Payslip has been created'));
        return back()->with($notification);
    }

    /**
     * Generate payslips for all active employees at once
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:monthly,weekly,hourly,bi-monthly',
            'payslip_date' => 'required|date',
            'from_date' => 'required_if:type,hourly|nullable|date',
            'to_date' => 'required_if:type,hourly|nullable|date',
            'weeks' => 'required_if:type,weekly|nullable|integer|min:1',
            'send_email' => 'nullable|boolean',
        ]);

        // Validate payslip generation date for all types
        $today = Carbon::now();
        $isValidDate = $today->day === 15 || $today->isLastOfMonth();
        
        if (!$isValidDate && !$request->has('force')) {
            $day = $today->day;
            $suffix = ($day === 1) ? 'st' : (($day === 2) ? 'nd' : (($day === 3) ? 'rd' : 'th'));
            $typeLabel = ucfirst(str_replace('-', ' ', $request->type));
            
            return back()->with('error', 
                $typeLabel . ' payslips can only be generated on the 15th or last day of the month. Today is the ' . $day . $suffix . '. ' .
                'Please wait until the appropriate cutoff day or use the command with --force flag.'
            )->withInput();
        }

        $employees = EmployeeDetail::whereHas('user', function($q){
            $q->where('is_active', true)->where('type', UserType::EMPLOYEE);
        })->with('salaryDetails','user')->get();

        $successCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach($employees as $employee){
            try {
                // Check if employee is newly hired (7 days or less)
                $hiredDate = $employee->user->joining_date ?? $employee->user->created_at;
                $workDays = \Carbon\Carbon::parse($hiredDate)->diffInDays(now());
                
                if($workDays <= 7){
                    $skippedCount++;
                    $errors[] = "Skipped {$employee->user->fullname} - newly employed (hired {$workDays} days ago, needs >7 days)";
                    continue;
                }

                $salaryInfo = $employee->salaryDetails;
                if(empty($salaryInfo)){
                    $skippedCount++;
                    $errors[] = "Skipped {$employee->user->fullname} - no salary details";
                    continue;
                }

                $deductions = 0;
                $allowances = 0;
                $total_hours = 0;
                $base_salary = $salaryInfo->base_salary;
                $allowancesItems = collect();
                $deductionItems = collect();

                // Apply global salary settings-derived allowances and deductions
                try{
                    $salarySettings = SalarySettings();

                    // Allowances (DA / HRA)
                    if(!empty($salarySettings->enable_da_hra)){
                        if(!empty($salarySettings->da_percent)){
                            $daAmount = ($base_salary * floatval($salarySettings->da_percent)) / 100;
                            $cola = EmployeeAllowance::firstOrCreate([
                                'employee_detail_id' => $employee->id,
                                'name' => 'COLA (Auto)'
                            ], ['amount' => $daAmount]);
                            $cola->update(['amount' => $daAmount]);
                            $allowancesItems->push($cola);
                            $allowances += $daAmount;
                        }
                        if(!empty($salarySettings->hra_percent)){
                            $hraAmount = ($base_salary * floatval($salarySettings->hra_percent)) / 100;
                            $hra = EmployeeAllowance::firstOrCreate([
                                'employee_detail_id' => $employee->id,
                                'name' => 'HRA (Auto)'
                            ], ['amount' => $hraAmount]);
                            $hra->update(['amount' => $hraAmount]);
                            $allowancesItems->push($hra);
                            $allowances += $hraAmount;
                        }
                    }

                    // Provident Fund
                    if(!empty($salarySettings->enable_provident_fund) && !empty($salarySettings->emp_pf_percentage)){
                        $pfAmount = ($base_salary * floatval($salarySettings->emp_pf_percentage)) / 100;
                        $pfDeduction = EmployeeDeduction::firstOrCreate([
                            'employee_detail_id' => $employee->id,
                            'name' => 'Provident Fund (Auto)'
                        ], ['amount' => $pfAmount]);
                        $pfDeduction->update(['amount' => $pfAmount]);
                        $deductionItems->push($pfDeduction);
                        $deductions += $pfAmount;
                    }

                    // ESI
                    if(!empty($salarySettings->enable_esi_fund) && !empty($salarySettings->emp_esi_percentage)){
                        $esiAmount = ($base_salary * floatval($salarySettings->emp_esi_percentage)) / 100;
                        $esiDeduction = EmployeeDeduction::firstOrCreate([
                            'employee_detail_id' => $employee->id,
                            'name' => 'ESI (Auto)'
                        ], ['amount' => $esiAmount]);
                        $esiDeduction->update(['amount' => $esiAmount]);
                        $deductionItems->push($esiDeduction);
                        $deductions += $esiAmount;
                    }

                    // Pag-IBIG
                    if(!empty($salarySettings->emp_pagibig_percentage)){
                        $pagibigAmount = ($base_salary * floatval($salarySettings->emp_pagibig_percentage)) / 100;
                        $pagibigDeduction = EmployeeDeduction::firstOrCreate([
                            'employee_detail_id' => $employee->id,
                            'name' => 'Pag-IBIG (Auto)'
                        ], ['amount' => $pagibigAmount]);
                        $pagibigDeduction->update(['amount' => $pagibigAmount]);
                        $deductionItems->push($pagibigDeduction);
                        $deductions += $pagibigAmount;
                    }

                    // Withholding Tax (TRAIN Law - RA 10963)
                    if(!empty($salarySettings->enable_tax)){
                        $taxAmount = \App\Helpers\TrainTaxCalculator::calculateMonthlyTax($base_salary);
                        
                        if($taxAmount > 0){
                            $taxDeduction = EmployeeDeduction::firstOrCreate([
                                'employee_detail_id' => $employee->id,
                                'name' => 'Withholding Tax (TRAIN Law)'
                            ], ['amount' => $taxAmount]);
                            $taxDeduction->update(['amount' => $taxAmount]);
                            $deductionItems->push($taxDeduction);
                            $deductions += $taxAmount;
                        }
                    }

                    // Absent Deduction
                    if(!empty($salarySettings->enable_absent_deduction)){
                        $fromDate = $request->from_date ?? now()->startOfMonth();
                        $toDate = $request->to_date ?? now()->endOfMonth();
                        $totalDays = Carbon::parse($fromDate)->diffInDays(Carbon::parse($toDate)) + 1;
                        $presentDays = Attendance::where('user_id', $employee->user_id)
                            ->whereBetween('startDate', [$fromDate, $toDate])
                            ->count();
                        $absentDays = max(0, $totalDays - $presentDays);
                        
                        if($absentDays > 0){
                            $daysPerMonth = ($salarySettings->absent_calculation_method === 'working_days') ? 22 : 30;
                            $dailyRate = $base_salary / $daysPerMonth;
                            
                            if(!empty($salarySettings->absent_deduction_percent)){
                                $absentAmount = $absentDays * ($dailyRate * floatval($salarySettings->absent_deduction_percent) / 100);
                            } else {
                                $absentAmount = $absentDays * floatval($salarySettings->absent_deduction_amount ?? 500);
                            }
                            
                            $absentDeduction = EmployeeDeduction::firstOrCreate([
                                'employee_detail_id' => $employee->id,
                                'name' => 'Absent Deduction (Auto)'
                            ], ['amount' => $absentAmount]);
                            $absentDeduction->update(['amount' => $absentAmount]);
                            $deductionItems->push($absentDeduction);
                            $deductions += $absentAmount;
                        }
                    }

                    // Overtime Pay
                    if(!empty($salarySettings->enable_overtime)){
                        $fromDate = $request->from_date ?? now()->startOfMonth();
                        $toDate = $request->to_date ?? now()->endOfMonth();
                        
                        $totalOvertimeHours = Attendance::where('user_id', $employee->user_id)
                            ->whereBetween('startDate', [$fromDate, $toDate])
                            ->sum('overtime_hours');
                        
                        if($totalOvertimeHours > 0){
                            $daysPerMonth = ($salarySettings->overtime_calculation_method === 'working_days') ? 22 : 30;
                            $hoursPerDay = floatval($salarySettings->overtime_threshold_hours ?? 8);
                            $hourlyRate = $base_salary / ($daysPerMonth * $hoursPerDay);
                            
                            $overtimeMultiplier = floatval($salarySettings->overtime_rate_multiplier ?? 1.25);
                            $overtimeAmount = $totalOvertimeHours * $hourlyRate * $overtimeMultiplier;
                            
                            $overtimeAllowance = EmployeeAllowance::firstOrCreate([
                                'employee_detail_id' => $employee->id,
                                'name' => 'Overtime Pay (Auto)'
                            ], ['amount' => $overtimeAmount]);
                            $overtimeAllowance->update(['amount' => $overtimeAmount]);
                            $allowancesItems->push($overtimeAllowance);
                            $allowances += $overtimeAmount;
                        }
                    }

                    // Undertime Deduction
                    if(!empty($salarySettings->enable_undertime)){
                        $fromDate = $request->from_date ?? now()->startOfMonth();
                        $toDate = $request->to_date ?? now()->endOfMonth();
                        
                        $totalUndertimeHours = Attendance::where('user_id', $employee->user_id)
                            ->whereBetween('startDate', [$fromDate, $toDate])
                            ->sum('undertime_hours');
                        
                        if($totalUndertimeHours > 0){
                            $daysPerMonth = ($salarySettings->undertime_calculation_method === 'working_days') ? 22 : 30;
                            $hoursPerDay = floatval($salarySettings->undertime_threshold_hours ?? 8);
                            $hourlyRate = $base_salary / ($daysPerMonth * $hoursPerDay);
                            
                            $undertimeMultiplier = floatval($salarySettings->undertime_rate_multiplier ?? 1);
                            $undertimeAmount = $totalUndertimeHours * $hourlyRate * $undertimeMultiplier;
                            
                            $undertimeDeduction = EmployeeDeduction::firstOrCreate([
                                'employee_detail_id' => $employee->id,
                                'name' => 'Undertime Deduction (Auto)'
                            ], ['amount' => $undertimeAmount]);
                            $undertimeDeduction->update(['amount' => $undertimeAmount]);
                            $deductionItems->push($undertimeDeduction);
                            $deductions += $undertimeAmount;
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore if settings not available
                }

                // Load any custom allowances/deductions for this employee
                $customAllowances = EmployeeAllowance::where('employee_detail_id',$employee->id)
                    ->whereNotIn('name', ['COLA (Auto)', 'HRA (Auto)', 'Overtime Pay (Auto)'])->get();
                foreach($customAllowances as $item){
                    $allowancesItems->push($item);
                    $allowances += $item->amount;
                }

                $customDeductions = EmployeeDeduction::where('employee_detail_id',$employee->id)
                    ->whereNotIn('name', ['Provident Fund (Auto)', 'ESI (Auto)', 'Pag-IBIG (Auto)', 'Withholding Tax (Auto)', 'Withholding Tax (TRAIN Law)', 'Absent Deduction (Auto)', 'Undertime Deduction (Auto)'])->get();
                foreach($customDeductions as $item){
                    $deductionItems->push($item);
                    $deductions += $item->amount;
                }

                $net_pay = round(($base_salary + $allowances) - $deductions, 2);

                if($request->type === 'hourly'){
                    $total_hours = AttendanceTimestamp::where('user_id',$employee->user_id)
                        ->whereBetween('created_at',[Carbon::parse($request->from_date), Carbon::parse($request->to_date)])
                        ->whereNotNull(['attendance_id','startTime','endTime'])->sum('totalHours');
                    $hourly_pay = ($total_hours * $base_salary);
                    $net_pay = round(($hourly_pay + $allowances) - $deductions, 2);
                }
                if($request->type === 'weekly'){
                    $weeks_salary = ($request->weeks * $base_salary);
                    $net_pay = round(($weeks_salary + $allowances) - $deductions, 2);
                }
                
                // Bi-Monthly: Divide by 2 and set cutoff details
                $cutoffNumber = null;
                $cutoffPeriod = null;
                $isBiMonthly = false;
                
                if($request->type === 'bi-monthly'){
                    $isBiMonthly = true;
                    $today = Carbon::now();
                    
                    // Determine cutoff based on today's date
                    if($today->day === 15){
                        $cutoffNumber = 1;
                        $cutoffPeriod = '1st Cutoff (1-15)';
                    } else if($today->isLastOfMonth()){
                        $cutoffNumber = 2;
                        $lastDay = $today->day;
                        $cutoffPeriod = "2nd Cutoff (16-{$lastDay})";
                    }
                    
                    // Divide all amounts by 2 for bi-monthly
                    $base_salary = $base_salary / 2;
                    $allowances = $allowances / 2;
                    $deductions = $deductions / 2;
                    $net_pay = round(($base_salary + $allowances) - $deductions, 2);
                }

                $payslip = Payslip::create([
                    'ps_id' => pad_zeros(Payslip::count()+1),
                    'title' => $request->title ?? ($cutoffPeriod ?: 'Payslip - ' . now()->format('F Y')),
                    'employee_detail_id' => $employee->id,
                    'use_allowance' => true,
                    'use_deduction' => true,
                    'payslip_date' => $request->payslip_date,
                    'type' => SalaryType::from($request->type),
                    'startDate' => $request->from_date,
                    'endDate' => $request->to_date,
                    'total_hours' => $total_hours,
                    'weeks' => $request->weeks,
                    'net_pay' => $net_pay,
                    'cutoff_number' => $cutoffNumber,
                    'cutoff_period' => $cutoffPeriod,
                    'is_semi_monthly' => $isBiMonthly,
                ]);

                if($allowancesItems->isNotEmpty()){
                    PayslipItem::insert($allowancesItems->map(function(EmployeeAllowance $item) use($payslip){
                        return [
                            'type' => 'allowance',
                            'payslip_id' => $payslip->id,
                            'item_id' => $item->id
                        ];
                    })->all());
                }
                if($deductionItems->isNotEmpty()){
                    PayslipItem::insert($deductionItems->map(function(EmployeeDeduction $item) use($payslip){
                        return [
                            'type' => 'deduction',
                            'payslip_id' => $payslip->id,
                            'item_id' => $item->id
                        ];
                    })->all());
                }

                // Send email notification if requested
                if($request->send_email){
                    try{
                        $employeeUser = User::find($employee->user_id);
                        if($employeeUser){
                            $employeeUser->notify(new PayslipCreatedNotification($payslip));
                        }
                    } catch(\Throwable $e){
                        // swallow notification errors
                    }
                }

                $successCount++;
            } catch(\Throwable $e) {
                $skippedCount++;
                $errors[] = "Error for {$employee->user->fullname}: {$e->getMessage()}";
            }
        }

        $message = __('Successfully generated :count payslips.', ['count' => $successCount]);
        if($skippedCount > 0){
            $message .= ' ' . __(':skipped employees skipped.', ['skipped' => $skippedCount]);
        }

        $notification = notify($message, $skippedCount > 0 ? 'warning' : 'success');
        return redirect()->route('payslips.index')->with($notification)->with('bulk_errors', $errors);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payslip = Payslip::findOrFail(Crypt::decrypt($id));
        $pageTitle = $payslip->ps_id ?? __('Payslip');
        $currency = LocaleSettings('currency_symbol');
        $employee = $payslip->employee;
        $allowances = $payslip->allowances();
        $deductions = $payslip->deductions();
        return view('pages.payroll.payslips.show',compact(
            'payslip','pageTitle','currency','employee','allowances','deductions'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payslip $payslip)
    {
        $employees = User::where('is_active', true)->where('type', UserType::EMPLOYEE)->get();
        return view('pages.payroll.payslips.edit',compact(
            'employees','payslip'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payslip $payslip)
    {
        $request->validate([
            'employee' => 'required',
            'type' => 'required',
            'payslip_date' => 'nullable|date',
            'title' => 'required_if:type,contract',
            'from_date' => 'required_if:type,hourly',
            'weeks' => 'required_if:type,weekly',
        ]);

        $employee = EmployeeDetail::findOrFail($request->employee);
        $salaryInfo = $employee->salaryDetails;
        $deductions = 0;
        $allowances = 0;
        $total_hours = 0;
        $allowancesItems = null;
        $deductionItems = null;
        if(!empty($request->use_allowance)){
            $allowancesItems = EmployeeAllowance::where('employee_detail_id',$employee->id)->get();
            $allowances = $allowancesItems->sum('amount');
        }
        // Apply global salary settings-derived deductions (PF / ESI) if enabled
        try{
            $salarySettings = SalarySettings();
            if(!empty($salarySettings->enable_provident_fund) && !empty($salarySettings->emp_pf_percentage)){
                $pfAmount = ($salaryInfo->base_salary * floatval($salarySettings->emp_pf_percentage)) / 100;
                $pfDeduction = EmployeeDeduction::firstOrCreate([
                    'employee_detail_id' => $employee->id,
                    'name' => 'Provident Fund (Auto)'
                ], [
                    'amount' => $pfAmount
                ]);
                if(empty($deductionItems)) $deductionItems = collect();
                $deductionItems->push($pfDeduction);
                $deductions += $pfAmount;
            }
            if(!empty($salarySettings->enable_esi_fund) && !empty($salarySettings->emp_esi_percentage)){
                $esiAmount = ($salaryInfo->base_salary * floatval($salarySettings->emp_esi_percentage)) / 100;
                $esiDeduction = EmployeeDeduction::firstOrCreate([
                    'employee_detail_id' => $employee->id,
                    'name' => 'ESI (Auto)'
                ], [
                    'amount' => $esiAmount
                ]);
                if(empty($deductionItems)) $deductionItems = collect();
                $deductionItems->push($esiDeduction);
                $deductions += $esiAmount;
            }
        }catch(\Throwable $e){
            // ignore
        }
        if(!empty($request->use_deductions)){
            $deductionItems = EmployeeDeduction::where('employee_detail_id',$employee->id)->get();
            $deductions = $deductionItems->sum('amount');
        }
        $net_pay = round(($salaryInfo->base_salary + $allowances) - $deductions, 2);
        if($request->type === SalaryType::Hourly){
            $total_hours = AttendanceTimestamp::where('user_id',$employee->user_id)->whereBetween('created_at',[Carbon::parse($request->from_date), Carbon::parse($request->to_date)])
                ->whereNotNull(['attendance_id','startTime','endTime'])->sum('totalHours');
            $hourly_pay = ($total_hours * $salaryInfo->base_salary);
            $net_pay = round(($hourly_pay + $allowances) - $deductions, 2);
        }
        if($request->type === SalaryType::Weekly){
            $weeks_salary = ($request->weeks * $salaryInfo->base_salary);
            $net_pay = round(($weeks_salary + $allowances) - $deductions, 2);
        }
        $payslip->update([
            'title' => $request->title,
            'employee_detail_id' => $employee->id,
            'use_allowance' => !empty($request->use_allowance),
            'use_deduction' => !empty($request->use_deductions),
            'payslip_date' => $request->payslip_date,
            'type' => $request->type,
            'startDate' => $request->from_date,
            'endDate' => $request->to_date,
            'total_hours' => $total_hours,
            'weeks' => $request->weeks,
            'net_pay' => $net_pay,
        ]);
        if(!empty($allowancesItems)){
            PayslipItem::insert($allowancesItems->map(function(EmployeeAllowance $item) use($payslip){
                return [
                    'type' => 'allowance',
                    'payslip_id' => $payslip->id,
                    'item_id' => $item->id
                ];
            })->all());
        }
        if(!empty($deductionItems)){
            PayslipItem::insert($deductionItems->map(function(EmployeeDeduction $item) use($payslip){
                return [
                    'type' => 'deduction',
                    'payslip_id' => $payslip->id,
                    'item_id' => $item->id
                ];
            })->all());
        }
        $notification = notify(__('Payslip has been updated'));
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payslip $payslip)
    {
        $payslip->delete();
        $notification = notify(__('Payslip has been deleted'));
        return back()->with($notification);
    }
}
