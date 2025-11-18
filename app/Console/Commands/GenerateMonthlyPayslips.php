<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Enums\UserType;
use App\Models\Payslip;
use App\Models\PayslipItem;
use App\Enums\Payroll\SalaryType;
use App\Models\EmployeeDetail;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeDeduction;
use Illuminate\Console\Command;
use App\Notifications\PayslipCreatedNotification;

class GenerateMonthlyPayslips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payslips:generate-monthly
                            {--type=monthly : Type of payslip (monthly, weekly, hourly)}
                            {--date= : Payslip date (default: today)}
                            {--send-email : Send email notifications to employees}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate payslips for all active employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type') ?? 'monthly';
        $payslipDate = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $sendEmail = $this->option('send-email');

        $this->info("Generating {$type} payslips for " . $payslipDate->format('Y-m-d') . '...');

        $employees = EmployeeDetail::whereHas('user', function($q){
            $q->where('is_active', true)->where('type', UserType::EMPLOYEE);
        })->with('salaryDetails','user')->get();

        $successCount = 0;
        $skippedCount = 0;

        $bar = $this->output->createProgressBar($employees->count());
        $bar->start();

        foreach($employees as $employee){
            try {
                // Check if employee is newly hired (7 days or less)
                $hiredDate = $employee->user->joining_date ?? $employee->user->created_at;
                $workDays = \Carbon\Carbon::parse($hiredDate)->diffInDays(now());
                
                if($workDays <= 7){
                    $skippedCount++;
                    $this->warn("  Skipped {$employee->user->fullname} - newly employed ({$workDays} days)");
                    $bar->advance();
                    continue;
                }

                $salaryInfo = $employee->salaryDetails;
                if(empty($salaryInfo)){
                    $skippedCount++;
                    $bar->advance();
                    continue;
                }

                $deductions = 0;
                $allowances = 0;
                $base_salary = $salaryInfo->base_salary;
                $allowancesItems = collect();
                $deductionItems = collect();

                // Apply global salary settings
                try{
                    $salarySettings = SalarySettings();

                    // Allowances
                    if(!empty($salarySettings->enable_da_hra)){
                        if(!empty($salarySettings->da_percent)){
                            $daAmount = round(($base_salary * floatval($salarySettings->da_percent)) / 100, 2);
                            $cola = EmployeeAllowance::firstOrCreate([
                                'employee_detail_id' => $employee->id,
                                'name' => 'COLA (Auto)'
                            ], ['amount' => $daAmount]);
                            $cola->update(['amount' => $daAmount]);
                            $allowancesItems->push($cola);
                            $allowances += $daAmount;
                        }
                        if(!empty($salarySettings->hra_percent)){
                            $hraAmount = round(($base_salary * floatval($salarySettings->hra_percent)) / 100, 2);
                            $hra = EmployeeAllowance::firstOrCreate([
                                'employee_detail_id' => $employee->id,
                                'name' => 'HRA (Auto)'
                            ], ['amount' => $hraAmount]);
                            $hra->update(['amount' => $hraAmount]);
                            $allowancesItems->push($hra);
                            $allowances += $hraAmount;
                        }
                    }

                    // Deductions
                    if(!empty($salarySettings->enable_provident_fund) && !empty($salarySettings->emp_pf_percentage)){
                        $pfAmount = round(($base_salary * floatval($salarySettings->emp_pf_percentage)) / 100, 2);
                        $pfDeduction = EmployeeDeduction::firstOrCreate([
                            'employee_detail_id' => $employee->id,
                            'name' => 'Provident Fund (Auto)'
                        ], ['amount' => $pfAmount]);
                        $pfDeduction->update(['amount' => $pfAmount]);
                        $deductionItems->push($pfDeduction);
                        $deductions += $pfAmount;
                    }

                    if(!empty($salarySettings->enable_esi_fund) && !empty($salarySettings->emp_esi_percentage)){
                        $esiAmount = round(($base_salary * floatval($salarySettings->emp_esi_percentage)) / 100, 2);
                        $esiDeduction = EmployeeDeduction::firstOrCreate([
                            'employee_detail_id' => $employee->id,
                            'name' => 'ESI (Auto)'
                        ], ['amount' => $esiAmount]);
                        $esiDeduction->update(['amount' => $esiAmount]);
                        $deductionItems->push($esiDeduction);
                        $deductions += $esiAmount;
                    }

                    if(!empty($salarySettings->emp_pagibig_percentage)){
                        $pagibigAmount = round(($base_salary * floatval($salarySettings->emp_pagibig_percentage)) / 100, 2);
                        $pagibigDeduction = EmployeeDeduction::firstOrCreate([
                            'employee_detail_id' => $employee->id,
                            'name' => 'Pag-IBIG (Auto)'
                        ], ['amount' => $pagibigAmount]);
                        $pagibigDeduction->update(['amount' => $pagibigAmount]);
                        $deductionItems->push($pagibigDeduction);
                        $deductions += $pagibigAmount;
                    }

                    if(!empty($salarySettings->enable_tax) && !empty($salarySettings->emp_withholding_percentage)){
                        $taxAmount = round(($base_salary * floatval($salarySettings->emp_withholding_percentage)) / 100, 2);
                        $taxDeduction = EmployeeDeduction::firstOrCreate([
                            'employee_detail_id' => $employee->id,
                            'name' => 'Withholding Tax (Auto)'
                        ], ['amount' => $taxAmount]);
                        $taxDeduction->update(['amount' => $taxAmount]);
                        $deductionItems->push($taxDeduction);
                        $deductions += $taxAmount;
                    }

                    // Absent Deduction
                    if(!empty($salarySettings->enable_absent_deduction)){
                        $fromDate = now()->startOfMonth();
                        $toDate = now()->endOfMonth();
                        $totalDays = $fromDate->diffInDays($toDate) + 1;
                        $presentDays = \App\Models\Attendance::where('user_id', $employee->user_id)
                            ->whereBetween('startDate', [$fromDate, $toDate])
                            ->count();
                        $absentDays = max(0, $totalDays - $presentDays);
                        
                        if($absentDays > 0){
                            $daysPerMonth = ($salarySettings->absent_calculation_method === 'working_days') ? 22 : 30;
                            $dailyRate = $base_salary / $daysPerMonth;
                            
                            if(!empty($salarySettings->absent_deduction_percent)){
                                $absentAmount = round($absentDays * ($dailyRate * floatval($salarySettings->absent_deduction_percent) / 100), 2);
                            } else {
                                $absentAmount = round($absentDays * floatval($salarySettings->absent_deduction_amount ?? 500), 2);
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

                    // Late Deduction — calculate based on late minutes recorded in attendance timestamps
                    if(!empty($salarySettings->enable_late_deduction)){
                        $fromDate = now()->startOfMonth();
                        $toDate = now()->endOfMonth();
                        
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
                            $tokenAllowance = EmployeeAllowance::firstOrCreate([
                                'employee_detail_id' => $employee->id,
                                'name' => 'Monthly Token Conversion (Auto)'
                            ], [
                                'amount' => $totalTokenCash
                            ]);
                            $tokenAllowance->update(['amount' => $totalTokenCash]);
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

                // Custom allowances/deductions
                $customAllowances = EmployeeAllowance::where('employee_detail_id',$employee->id)
                    ->whereNotIn('name', ['COLA (Auto)', 'HRA (Auto)', 'Overtime Pay (Auto)', 'Monthly Token Conversion (Auto)'])->get();
                foreach($customAllowances as $item){
                    $allowancesItems->push($item);
                    $allowances += $item->amount;
                }

                $customDeductions = EmployeeDeduction::where('employee_detail_id',$employee->id)
                    ->whereNotIn('name', ['Provident Fund (Auto)', 'ESI (Auto)', 'Pag-IBIG (Auto)', 'Withholding Tax (Auto)', 'Absent Deduction (Auto)', 'Undertime Deduction (Auto)', 'Late Deduction (Auto)'])->get();
                foreach($customDeductions as $item){
                    $deductionItems->push($item);
                    $deductions += $item->amount;
                }

                $net_pay = round(($base_salary + $allowances) - $deductions, 2);

                $payslip = Payslip::create([
                    'ps_id' => pad_zeros(Payslip::count()+1),
                    'title' => 'Payslip - ' . $payslipDate->format('F Y'),
                    'employee_detail_id' => $employee->id,
                    'use_allowance' => true,
                    'use_deduction' => true,
                    'payslip_date' => $payslipDate,
                    'type' => SalaryType::from($type),
                    'net_pay' => $net_pay,
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

                // Send email notification
                if($sendEmail){
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
                $this->error("Error for {$employee->user->fullname}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ Successfully generated {$successCount} payslips.");
        if($skippedCount > 0){
            $this->warn("⚠ Skipped {$skippedCount} employees.");
        }

        return Command::SUCCESS;
    }
}
