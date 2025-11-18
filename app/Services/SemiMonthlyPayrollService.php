<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payslip;
use App\Models\PayslipItem;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeDeduction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SemiMonthlyPayrollService
{
    /**
     * Generate bi-monthly payslips for a specific cutoff period
     * 
     * @param int $cutoffNumber 1 for 1-15, 2 for 16-end
     * @param Carbon|null $date Reference date (defaults to current date)
     * @param bool $sendEmail Whether to send email notifications
     * @param bool $bypassDateValidation Skip date validation (for manual override)
     * @return array Results with success/skip counts
     * @throws \Exception If not on appropriate cutoff day
     */
    public function generateBiMonthlyPayslips(int $cutoffNumber, ?Carbon $date = null, bool $sendEmail = false, bool $bypassDateValidation = false): array
    {
        $date = $date ?? Carbon::now();
        $year = $date->year;
        $month = $date->month;
        
        // Validate that today is the appropriate cutoff day (unless bypassed)
        if (!$bypassDateValidation) {
            $this->validateCutoffDay($cutoffNumber, $date);
        }
        
        // Determine cutoff dates
        if ($cutoffNumber === 1) {
            // 1st cutoff: 1st to 15th
            $startDate = Carbon::create($year, $month, 1);
            $endDate = Carbon::create($year, $month, 15);
            $payslipDate = Carbon::create($year, $month, 15);
            $cutoffPeriod = '1st Cutoff (1-15)';
        } else {
            // 2nd cutoff: 16th to last day of month
            $startDate = Carbon::create($year, $month, 16);
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            $payslipDate = $endDate->copy();
            $cutoffPeriod = '2nd Cutoff (16-' . $endDate->day . ')';
        }
        
        $employees = User::where('type', \App\Enums\UserType::EMPLOYEE)
            ->whereHas('employeeDetail')
            ->with('employeeDetail')
            ->get();
        
        $successCount = 0;
        $skipCount = 0;
        $results = [];
        
        foreach ($employees as $employee) {
            try {
                // Check if payslip already exists for this cutoff
                $exists = Payslip::where('employee_detail_id', $employee->employeeDetail->id)
                    ->where('cutoff_number', $cutoffNumber)
                    ->whereYear('payslip_date', $year)
                    ->whereMonth('payslip_date', $month)
                    ->where('is_semi_monthly', true)
                    ->exists();
                
                if ($exists) {
                    $skipCount++;
                    $results[] = [
                        'employee' => $employee->fullname,
                        'status' => 'skipped',
                        'reason' => 'Payslip already exists for this cutoff'
                    ];
                    continue;
                }
                
                // Generate payslip
                $payslip = $this->createBiMonthlyPayslip(
                    $employee,
                    $startDate,
                    $endDate,
                    $payslipDate,
                    $cutoffNumber,
                    $cutoffPeriod
                );
                
                if ($sendEmail && $payslip) {
                    // TODO: Send email notification
                    // Mail::to($employee->email)->send(new PayslipGenerated($payslip));
                }
                
                $successCount++;
                $results[] = [
                    'employee' => $employee->fullname,
                    'status' => 'success',
                    'amount' => $payslip->net_pay ?? 0
                ];
                
            } catch (\Exception $e) {
                $skipCount++;
                $results[] = [
                    'employee' => $employee->fullname,
                    'status' => 'failed',
                    'reason' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => $successCount,
            'skipped' => $skipCount,
            'total' => $employees->count(),
            'details' => $results
        ];
    }
    
    /**
     * Create a bi-monthly payslip for an employee
     */
    protected function createBiMonthlyPayslip(
        User $employee,
        Carbon $startDate,
        Carbon $endDate,
        Carbon $payslipDate,
        int $cutoffNumber,
        string $cutoffPeriod
    ): ?Payslip {
        $employeeDetail = $employee->employeeDetail;
        
        if (!$employeeDetail || !$employeeDetail->salary) {
            return null;
        }
        
        // Monthly salary divided by 2 for bi-monthly
        $monthlySalary = floatval($employeeDetail->salary->monthly_salary ?? 0);
        $biMonthlyAmount = $monthlySalary / 2;
        
        // Get salary settings
        $salarySettings = \App\Settings\SalarySettings::first();
        
        // Calculate allowances (divided by 2 for bi-monthly)
        $allowances = 0;
        $allowanceItems = collect();
        
        if (!empty($salarySettings->enable_da_hra)) {
            // COLA
            if (!empty($salarySettings->da_percent)) {
                $colaAmount = ($monthlySalary * floatval($salarySettings->da_percent)) / 100 / 2;
                $allowanceItems->push([
                    'name' => 'COLA (Bi-Monthly)',
                    'amount' => $colaAmount
                ]);
                $allowances += $colaAmount;
            }
            
            // HRA
            if (!empty($salarySettings->hra_percent)) {
                $hraAmount = ($monthlySalary * floatval($salarySettings->hra_percent)) / 100 / 2;
                $allowanceItems->push([
                    'name' => 'HRA (Bi-Monthly)',
                    'amount' => $hraAmount
                ]);
                $allowances += $hraAmount;
            }
        }
        
        // Get custom allowances (divided by 2)
        $customAllowances = EmployeeAllowance::where('employee_detail_id', $employeeDetail->id)->get();
        foreach ($customAllowances as $allowance) {
            $allowanceItems->push([
                'name' => $allowance->name . ' (Bi-Monthly)',
                'amount' => floatval($allowance->amount) / 2
            ]);
            $allowances += floatval($allowance->amount) / 2;
        }
        
        // Calculate deductions (divided by 2 for bi-monthly)
        $deductions = 0;
        $deductionItems = collect();
        
        // SSS/PF (divided by 2)
        if (!empty($salarySettings->enable_provident_fund) && !empty($salarySettings->emp_pf_percentage)) {
            $pfAmount = ($monthlySalary * floatval($salarySettings->emp_pf_percentage)) / 100 / 2;
            $deductionItems->push([
                'name' => 'SSS/PF (Bi-Monthly)',
                'amount' => $pfAmount
            ]);
            $deductions += $pfAmount;
        }
        
        // PhilHealth/ESI (divided by 2)
        if (!empty($salarySettings->enable_esi_fund) && !empty($salarySettings->emp_esi_percentage)) {
            $esiAmount = ($monthlySalary * floatval($salarySettings->emp_esi_percentage)) / 100 / 2;
            $deductionItems->push([
                'name' => 'PhilHealth/ESI (Bi-Monthly)',
                'amount' => $esiAmount
            ]);
            $deductions += $esiAmount;
        }
        
        // Pag-IBIG (divided by 2)
        if (!empty($salarySettings->emp_pagibig_percentage)) {
            $pagibigAmount = ($monthlySalary * floatval($salarySettings->emp_pagibig_percentage)) / 100 / 2;
            $deductionItems->push([
                'name' => 'Pag-IBIG (Bi-Monthly)',
                'amount' => $pagibigAmount
            ]);
            $deductions += $pagibigAmount;
        }
        
        // Withholding Tax (TRAIN Law) - divided by 2
        if (!empty($salarySettings->enable_tax)) {
            $monthlyTax = \App\Helpers\TrainTaxCalculator::calculateMonthlyTax($monthlySalary);
            $biMonthlyTax = $monthlyTax / 2;
            
            if ($biMonthlyTax > 0) {
                $deductionItems->push([
                    'name' => 'Withholding Tax (Bi-Monthly)',
                    'amount' => $biMonthlyTax
                ]);
                $deductions += $biMonthlyTax;
            }
        }
        
        // Get custom deductions (divided by 2)
        $customDeductions = EmployeeDeduction::where('employee_detail_id', $employeeDetail->id)
            ->whereNotIn('name', [
                'Provident Fund (Auto)', 
                'ESI (Auto)', 
                'Pag-IBIG (Auto)', 
                'Withholding Tax (Auto)', 
                'Withholding Tax (TRAIN Law)',
                'Absent Deduction (Auto)', 
                'Undertime Deduction (Auto)'
            ])
            ->get();
            
        foreach ($customDeductions as $deduction) {
            $deductionItems->push([
                'name' => $deduction->name . ' (Bi-Monthly)',
                'amount' => floatval($deduction->amount) / 2
            ]);
            $deductions += floatval($deduction->amount) / 2;
        }
        
        // Calculate net pay
        $grossPay = $biMonthlyAmount + $allowances;
        $netPay = $grossPay - $deductions;
        
        // Create payslip
        $payslip = Payslip::create([
            'ps_id' => 'PS-' . strtoupper(uniqid()),
            'title' => $cutoffPeriod . ' - ' . $payslipDate->format('F Y'),
            'employee_detail_id' => $employeeDetail->id,
            'use_allowance' => true,
            'use_deduction' => true,
            'payslip_date' => $payslipDate,
            'type' => 'bi-monthly',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'net_pay' => round($netPay, 2),
            'cutoff_period' => $cutoffPeriod,
            'cutoff_number' => $cutoffNumber,
            'is_semi_monthly' => true,
        ]);
        
        // Create payslip items (allowances)
        foreach ($allowanceItems as $item) {
            PayslipItem::create([
                'payslip_id' => $payslip->id,
                'name' => $item['name'],
                'amount' => round($item['amount'], 2),
                'type' => 'allowance'
            ]);
        }
        
        // Create payslip items (deductions)
        foreach ($deductionItems as $item) {
            PayslipItem::create([
                'payslip_id' => $payslip->id,
                'name' => $item['name'],
                'amount' => round($item['amount'], 2),
                'type' => 'deduction'
            ]);
        }
        
        // Add base salary item
        PayslipItem::create([
            'payslip_id' => $payslip->id,
            'name' => 'Base Salary (Bi-Monthly)',
            'amount' => round($biMonthlyAmount, 2),
            'type' => 'earning'
        ]);
        
        return $payslip;
    }
    
    /**
     * Calculate which cutoff should run today
     */
    public static function getTodayCutoff(): ?int
    {
        $today = Carbon::now();
        $day = $today->day;
        
        // 15th = 1st cutoff payout
        if ($day === 15) {
            return 1;
        }
        
        // Last day of month = 2nd cutoff payout
        if ($today->isLastOfMonth()) {
            return 2;
        }
        
        return null;
    }
    
    /**
     * Validate that the current date is appropriate for the cutoff
     * 
     * @param int $cutoffNumber
     * @param Carbon $date
     * @throws \Exception
     */
    protected function validateCutoffDay(int $cutoffNumber, Carbon $date): void
    {
        $day = $date->day;
        $isLastDay = $date->isLastOfMonth();
        
        if ($cutoffNumber === 1) {
            // 1st cutoff should only be generated on the 15th
            if ($day !== 15) {
                throw new \Exception(
                    "1st cutoff payslips can only be generated on the 15th of the month. Today is the {$day}th."
                );
            }
        } elseif ($cutoffNumber === 2) {
            // 2nd cutoff should only be generated on the last day of the month
            if (!$isLastDay) {
                $lastDay = $date->copy()->endOfMonth()->day;
                throw new \Exception(
                    "2nd cutoff payslips can only be generated on the last day of the month ({$lastDay}th). Today is the {$day}th."
                );
            }
        } else {
            throw new \Exception("Invalid cutoff number: {$cutoffNumber}. Must be 1 or 2.");
        }
    }
}
