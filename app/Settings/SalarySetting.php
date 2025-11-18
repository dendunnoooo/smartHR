<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SalarySetting extends Settings
{

    public bool $enable_da_hra = false, $enable_provident_fund = false, $enable_esi_fund = false;

    // Additional payroll settings
    public bool $enable_tax = false;
    public bool $enable_late_deduction = false;
    public bool $enable_absent_deduction = false;
    public bool $enable_auto_payslip = false;
    public bool $enable_overtime = false;
    public bool $enable_undertime = false;

    // Percentages and amounts (default to '0' so old stored settings without these keys can be loaded)
    public string $da_percent = '0';
    public string $late_grace_minutes = '0';
    public string $late_deduction_per_minute = '0';
    public string $hra_percent = '0';
    public string $emp_pf_percentage = '0';
    public string $company_pf_percentage = '0';
    public string $emp_esi_percentage = '0';
    public string $company_esi_percentage = '0';
    public string $emp_pagibig_percentage = '0';
    public string $company_pagibig_percentage = '0';
    public string $emp_withholding_percentage = '0';
    public string $company_withholding_percentage = '0';
    public string $absent_deduction_amount = '0';
    public string $absent_deduction_percent = '0';
    public string $absent_calculation_method = 'calendar_days';
    public string $auto_payslip_day = '25';
    public string $auto_payslip_type = 'monthly';
    public bool $auto_payslip_send_email = false;
    public string $overtime_threshold_hours = '8';
    public string $overtime_rate_multiplier = '1.25';
    public string $overtime_calculation_method = 'calendar_days';
    public string $default_overtime_hours = '2';
    public string $undertime_threshold_hours = '8';
    public string $undertime_rate_multiplier = '1';
    public string $undertime_calculation_method = 'calendar_days';
    public string $default_undertime_hours = '1';

    public static function group(): string
    {
        return 'general_salary';
    }
}