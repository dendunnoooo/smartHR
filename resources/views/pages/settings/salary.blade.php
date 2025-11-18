@extends('pages.settings.index')

@section('page-header-section')
    <!-- Page Header -->
    <x-breadcrumb>
        <x-slot name="title">{{ __('Salary Settings') }}</x-slot>
    </x-breadcrumb>
    <!-- /Page Header -->
@endsection

@section('page-section')
    <form action="{{ route('settings.salary.update') }}" method="post" enctype="multipart/form-data">
        @csrf

        <!-- Allowances -->
        <div class="settings-widget">
            <div class="h3 card-title with-switch">{{ __('Allowances') }}
                <div class="onoffswitch">
                    <input type="hidden" name="enable_da_hra" value="0">
                    <input type="checkbox" name="enable_da_hra" class="onoffswitch-checkbox" id="switch_hra" value="1" {{ !empty($settings->enable_da_hra) ? 'checked':'' }}>
                    <label class="onoffswitch-label" for="switch_hra">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <p class="text-muted">{{ __('Toggle to enable common allowances which are applied to payroll calculations. You can provide a percentage or a fixed monthly amount in the fields below.') }}</p>
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('COLA (Cost of Living Allowance)') }} (%)</label>
                        <input type="text" class="form-control" name="da_percent" value="{{ $settings->da_percent }}">
                        <small class="form-text text-muted">{{ __('Percentage or fixed amount added to the basic salary to help employees cope with living costs. Example: ₱1,000/month or 5% of basic salary') }}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Housing / Rent Allowance') }} (%)</label>
                        <input class="form-control" type="text" name="hra_percent" value="{{ $settings->hra_percent }}">
                        <small class="form-text text-muted">{{ __('Optional company-provided benefit for accommodation or rent support. Leave blank if not applicable.') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- SSS / Provident Fund Settings -->
        <div class="settings-widget">
            <div class="h3 card-title with-switch">{{ __('SSS / Provident Fund Contribution Settings') }}
                <div class="onoffswitch">
                    <input type="hidden" name="enable_pf" value="0">
                    <input type="checkbox" name="enable_pf" class="onoffswitch-checkbox" id="switch_pf" value="1" {{ !empty($settings->enable_provident_fund) ? 'checked': ''}}>
                    <label class="onoffswitch-label" for="switch_pf">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <p class="text-muted">{{ __('Automatic SSS / Provident Fund deductions applied to payroll when enabled.') }}</p>
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Employee Share') }} (%)</label>
                        <input class="form-control" type="text" name="emp_pf" value="{{ $settings->emp_pf_percentage }}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Employer Share') }} (%)</label>
                        <input class="form-control" type="text" name="company_pf" value="{{ $settings->company_pf_percentage }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- PhilHealth / ESI Settings -->
        <div class="settings-widget">
            <div class="h3 card-title with-switch">{{ __('PhilHealth / ESI Settings') }}
                <div class="onoffswitch">
                    <input type="hidden" name="enable_esi" value="0">
                    <input type="checkbox" name="enable_esi" class="onoffswitch-checkbox" id="switch_esi" value="1" {{ !empty($settings->enable_esi_fund) ? 'checked': '' }}>
                    <label class="onoffswitch-label" for="switch_esi">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <p class="text-muted">{{ __('PhilHealth / ESI contributions — set employee and employer percentage shares. These are deducted/paid automatically when enabled.') }}</p>
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Employee Share') }} (%)</label>
                        <input class="form-control" type="text" name="emp_esi" value="{{ $settings->emp_esi_percentage }}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Employer Share') }} (%)</label>
                        <input class="form-control" type="text" name="company_esi" value="{{ $settings->company_esi_percentage }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Pag-IBIG / Other Fund Settings -->
        <div class="settings-widget">
            <div class="h3 card-title">{{ __('Pag-IBIG / Other Fund Settings') }}</div>
            <p class="text-muted">{{ __('Set contributions for Pag-IBIG or other statutory funds. These may be applied as deductions or employer contributions according to local rules.') }}</p>
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Employee Share') }} (%)</label>
                        <input class="form-control" type="text" name="emp_pagibig" value="{{ $settings->emp_pagibig_percentage ?? '' }}">
                        <small class="form-text text-muted">{{ __('Optional: configure employee share (if supported).') }}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Employer Share') }} (%)</label>
                        <input class="form-control" type="text" name="company_pagibig" value="{{ $settings->company_pagibig_percentage ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Withholding Tax (TRAIN Law) -->
        <div class="settings-widget">
            <div class="h3 card-title with-switch">{{ __('Withholding Tax (TRAIN Law - RA 10963)') }}
                <div class="onoffswitch">
                    <input type="hidden" name="enable_tax" value="0">
                    <input type="checkbox" name="enable_tax" class="onoffswitch-checkbox" id="switch_tax" value="1" {{ !empty($settings->enable_tax) ? 'checked' : '' }}>
                    <label class="onoffswitch-label" for="switch_tax">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <p class="text-muted">{{ __('Automatically calculate and deduct withholding tax based on Philippine TRAIN Law (Tax Reform for Acceleration and Inclusion). Tax is calculated from monthly gross salary and applied using progressive tax brackets.') }}</p>
            
            <div class="alert alert-info">
                <strong><i class="fa-solid fa-info-circle"></i> {{ __('TRAIN Law Tax Brackets (Fixed by Law)') }}</strong>
                <table class="table table-sm table-bordered mt-2 mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Annual Taxable Income') }}</th>
                            <th>{{ __('Tax Rate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>₱0 – ₱250,000</td>
                            <td>0%</td>
                        </tr>
                        <tr>
                            <td>₱250,001 – ₱400,000</td>
                            <td>15% of excess over ₱250,000</td>
                        </tr>
                        <tr>
                            <td>₱400,001 – ₱800,000</td>
                            <td>₱22,500 + 20% of excess over ₱400,000</td>
                        </tr>
                        <tr>
                            <td>₱800,001 – ₱2,000,000</td>
                            <td>₱102,500 + 25% of excess over ₱800,000</td>
                        </tr>
                        <tr>
                            <td>₱2,000,001 – ₱8,000,000</td>
                            <td>₱402,500 + 30% of excess over ₱2,000,000</td>
                        </tr>
                        <tr>
                            <td>Above ₱8,000,000</td>
                            <td>₱2,202,500 + 35% of excess over ₱8,000,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-success">
                <small>
                    <i class="fa-solid fa-calculator"></i>
                    <strong>{{ __('Example Calculations:') }}</strong><br>
                    • {{ __('Monthly Salary: ₱20,000 → Annual: ₱240,000 → Monthly Tax: ₱0.00 (Below threshold)') }}<br>
                    • {{ __('Monthly Salary: ₱25,000 → Annual: ₱300,000 → Monthly Tax: ₱625.00') }}<br>
                    • {{ __('Monthly Salary: ₱50,000 → Annual: ₱600,000 → Monthly Tax: ₱2,708.33') }}<br>
                    • {{ __('Monthly Salary: ₱100,000 → Annual: ₱1,200,000 → Monthly Tax: ₱10,875.00') }}
                </small>
            </div>

            <div class="alert alert-warning">
                <small>
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <strong>{{ __('Important Notes:') }}</strong><br>
                    • {{ __('Tax brackets are mandated by Philippine law and cannot be modified') }}<br>
                    • {{ __('System automatically calculates monthly withholding tax from annual brackets') }}<br>
                    • {{ __('Tax is computed based on gross monthly salary (before other deductions)') }}<br>
                    • {{ __('This is withholding tax only - employees file annual tax returns separately') }}
                </small>
            </div>
        </div>

        <!-- Absent Deduction Settings -->
        <div class="settings-widget">
            <div class="h3 card-title with-switch">{{ __('Absent Deduction Settings') }}
                <div class="onoffswitch">
                    <input type="hidden" name="enable_absent_deduction" value="0">
                    <input type="checkbox" name="enable_absent_deduction" class="onoffswitch-checkbox" id="switch_absent" value="1" {{ !empty($settings->enable_absent_deduction) ? 'checked' : '' }}>
                    <label class="onoffswitch-label" for="switch_absent">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <p class="text-muted">{{ __('Automatically deduct from salary for absent days. The system will check attendance records and calculate deductions based on the configured daily rate.') }}</p>
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Daily Deduction Amount') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ LocaleSettings('currency_symbol') }}</span>
                            <input class="form-control" type="number" step="0.01" name="absent_deduction_amount" value="{{ $settings->absent_deduction_amount ?? '500.00' }}" placeholder="500.00">
                        </div>
                        <small class="form-text text-muted">{{ __('Fixed amount to deduct per absent day (e.g., 500 = ₱500 per day)') }}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Or Use Percentage of Daily Rate') }} (%)</label>
                        <input class="form-control" type="number" step="0.01" name="absent_deduction_percent" value="{{ $settings->absent_deduction_percent ?? '' }}" placeholder="100">
                        <small class="form-text text-muted">{{ __('Percentage of daily salary rate (leave blank to use fixed amount above)') }}</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Calculation Method') }}</label>
                        <select class="form-control" name="absent_calculation_method">
                            <option value="calendar_days" {{ (!empty($settings->absent_calculation_method) && $settings->absent_calculation_method === 'calendar_days') ? 'selected' : '' }}>{{ __('Calendar Days (30 days/month)') }}</option>
                            <option value="working_days" {{ (!empty($settings->absent_calculation_method) && $settings->absent_calculation_method === 'working_days') ? 'selected' : '' }}>{{ __('Working Days Only (22 days/month)') }}</option>
                        </select>
                        <small class="form-text text-muted">{{ __('Method to calculate daily rate: Monthly Salary ÷ Days per Month') }}</small>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <small>
                    <i class="fa-solid fa-info-circle"></i>
                    <strong>{{ __('How it works:') }}</strong><br>
                    {{ __('Daily Rate = Monthly Salary ÷ Days per Month') }}<br>
                    {{ __('Absent Deduction = Number of Absent Days × (Fixed Amount OR Daily Rate × Percentage)') }}<br>
                    {{ __('Example: If salary is ₱20,000/month and employee is absent 2 days with ₱500 fixed deduction = ₱1,000 total deduction') }}
                </small>
            </div>
        </div>

        <!-- Semi-Monthly Payroll System -->
        <div class="settings-widget">
            <div class="h3 card-title with-switch">{{ __('Semi-Monthly Payroll System') }}
                <div class="onoffswitch">
                    <input type="hidden" name="enable_semi_monthly_payroll" value="0">
                    <input type="checkbox" name="enable_semi_monthly_payroll" class="onoffswitch-checkbox" id="switch_semi_monthly" value="1" {{ !empty($settings->enable_semi_monthly_payroll) ? 'checked' : '' }}>
                    <label class="onoffswitch-label" for="switch_semi_monthly">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <p class="text-muted">{{ __('Automatically split monthly salary into two equal semi-monthly payouts. System generates payslips on the 15th and last day of each month.') }}</p>
            
            <div class="alert alert-info">
                <strong><i class="fa-solid fa-calculator"></i> {{ __('Semi-Monthly Calculation') }}</strong>
                <div class="mt-2">
                    <p class="mb-2"><strong>{{ __('Formula:') }}</strong> Semi-Monthly Amount = Monthly Salary ÷ 2</p>
                    <p class="mb-2"><strong>{{ __('Payout Schedule:') }}</strong></p>
                    <ul class="mb-0">
                        <li><strong>1st Cutoff:</strong> Covers 1st to 15th → Payment on 15th</li>
                        <li><strong>2nd Cutoff:</strong> Covers 16th to end of month → Payment on last day</li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-success">
                <small>
                    <i class="fa-solid fa-info-circle"></i>
                    <strong>{{ __('Example:') }}</strong><br>
                    • {{ __('Monthly Salary: ₱20,000') }}<br>
                    • {{ __('1st Payout (15th): ₱10,000') }}<br>
                    • {{ __('2nd Payout (Last day): ₱10,000') }}<br>
                    <br>
                    {{ __('All allowances, deductions, and taxes are also split equally between the two payouts.') }}
                </small>
            </div>

            <div class="alert alert-warning">
                <small>
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <strong>{{ __('Automated Schedule:') }}</strong><br>
                    • {{ __('System automatically runs at 2:00 AM on the 15th of each month (1st cutoff)') }}<br>
                    • {{ __('System automatically runs at 2:00 AM on the last day of each month (2nd cutoff)') }}<br>
                    • {{ __('Manual generation: php artisan payslips:generate-semi-monthly --cutoff=1 --send-email') }}<br>
                    • {{ __('Force specific cutoff: Use --cutoff=1 or --cutoff=2') }}
                </small>
            </div>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="semi_monthly_send_email" value="1" id="semi_monthly_email" {{ !empty($settings->semi_monthly_send_email) ? 'checked' : '' }}>
                <label class="form-check-label" for="semi_monthly_email">
                    {{ __('Automatically send email notifications for semi-monthly payslips') }}
                </label>
            </div>
        </div>

        <!-- Automated Payslip Generation -->
        <div class="settings-widget">
            <div class="h3 card-title with-switch">{{ __('Automated Payslip Generation') }}
                <div class="onoffswitch">
                    <input type="hidden" name="enable_auto_payslip" value="0">
                    <input type="checkbox" name="enable_auto_payslip" class="onoffswitch-checkbox" id="switch_auto_payslip" value="1" {{ !empty($settings->enable_auto_payslip) ? 'checked' : '' }}>
                    <label class="onoffswitch-label" for="switch_auto_payslip">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <p class="text-muted">{{ __('Automatically generate and send payslips to all employees on a specific day each month.') }}</p>
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Generation Day of Month') }}</label>
                        <input class="form-control" type="number" name="auto_payslip_day" min="1" max="31" value="{{ $settings->auto_payslip_day ?? '25' }}" placeholder="25">
                        <small class="form-text text-muted">{{ __('Day of the month (1-31) to automatically generate payslips') }}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Payslip Type') }}</label>
                        <select class="form-control" name="auto_payslip_type">
                            <option value="monthly" {{ (!empty($settings->auto_payslip_type) && $settings->auto_payslip_type === 'monthly') ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                            <option value="weekly" {{ (!empty($settings->auto_payslip_type) && $settings->auto_payslip_type === 'weekly') ? 'selected' : '' }}>{{ __('Weekly') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="auto_payslip_send_email" value="1" id="auto_send_email" {{ !empty($settings->auto_payslip_send_email) ? 'checked' : '' }}>
                <label class="form-check-label" for="auto_send_email">
                    {{ __('Automatically send email notifications to employees') }}
                </label>
            </div>

            <div class="alert alert-info">
                <small>
                    <i class="fa-solid fa-info-circle"></i>
                    {{ __('To enable automation, you must configure a cron job to run:') }}
                    <code>php artisan schedule:run</code>
                    <br>
                    {{ __('Or run the command manually:') }}
                    <code>php artisan payslips:generate-monthly --send-email</code>
                </small>
            </div>

            <div class="alert alert-warning">
                <small>
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <strong>{{ __('Newly Employed Policy:') }}</strong> {{ __('Employees hired within the last 7 days will be automatically excluded from payslip generation and included in the next cycle.') }}
                </small>
            </div>
        </div>

        <!-- Overtime Settings -->
        <div class="settings-widget">
            <div class="h3 card-title with-switch">{{ __('Overtime Settings') }}
                <div class="onoffswitch">
                    <input type="hidden" name="enable_overtime" value="0">
                    <input type="checkbox" name="enable_overtime" class="onoffswitch-checkbox" id="switch_overtime" value="1" {{ !empty($settings->enable_overtime) ? 'checked' : '' }}>
                    <label class="onoffswitch-label" for="switch_overtime">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <p class="text-muted">{{ __('Automatically calculate overtime pay based on hours worked beyond the regular hours threshold.') }}</p>
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Regular Hours Threshold') }}</label>
                        <input class="form-control" type="number" step="0.5" name="overtime_threshold_hours" value="{{ $settings->overtime_threshold_hours ?? '8' }}" placeholder="8">
                        <small class="form-text text-muted">{{ __('Hours worked beyond this are considered overtime (default: 8 hours)') }}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Overtime Rate Multiplier') }}</label>
                        <input class="form-control" type="number" step="0.01" name="overtime_rate_multiplier" value="{{ $settings->overtime_rate_multiplier ?? '1.25' }}" placeholder="1.25">
                        <small class="form-text text-muted">{{ __('Multiplier for overtime pay (1.25 = 125%, 1.5 = 150%, 2.0 = 200%)') }}</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Hourly Rate Calculation') }}</label>
                        <select class="form-control" name="overtime_calculation_method">
                            <option value="calendar_days" {{ (!empty($settings->overtime_calculation_method) && $settings->overtime_calculation_method === 'calendar_days') ? 'selected' : '' }}>{{ __('Monthly Salary ÷ 30 days ÷ 8 hours') }}</option>
                            <option value="working_days" {{ (!empty($settings->overtime_calculation_method) && $settings->overtime_calculation_method === 'working_days') ? 'selected' : '' }}>{{ __('Monthly Salary ÷ 22 days ÷ 8 hours') }}</option>
                        </select>
                        <small class="form-text text-muted">{{ __('Method to calculate hourly rate for overtime') }}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Default Overtime Hours (Sample)') }}</label>
                        <input class="form-control" type="number" step="0.5" name="default_overtime_hours" value="{{ $settings->default_overtime_hours ?? '2' }}" placeholder="2">
                        <small class="form-text text-muted">{{ __('Sample overtime hours for testing (can be changed per employee)') }}</small>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <small>
                    <i class="fa-solid fa-info-circle"></i>
                    <strong>{{ __('How it works:') }}</strong><br>
                    {{ __('Hourly Rate = Monthly Salary ÷ Days per Month ÷ 8 hours') }}<br>
                    {{ __('Overtime Pay = Overtime Hours × Hourly Rate × Multiplier') }}<br>
                    {{ __('Example: ₱20,000/month ÷ 30 days ÷ 8 hours = ₱83.33/hour. 2 OT hours × ₱83.33 × 1.25 = ₱208.33 overtime pay') }}
                </small>
            </div>

            <div class="alert alert-warning">
                <small>
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <strong>{{ __('Philippine Labor Standards:') }}</strong><br>
                    {{ __('Regular Day OT: +25% (1.25×), Night Shift (10PM-6AM): +10%, Rest Day: +30%, Holiday: varies by type') }}
                </small>
            </div>
        </div>

        <!-- Undertime Settings -->
        <div class="settings-widget">
            <div class="h3 card-title with-switch">{{ __('Undertime Settings') }}
                <div class="onoffswitch">
                    <input type="hidden" name="enable_undertime" value="0">
                    <input type="checkbox" name="enable_undertime" class="onoffswitch-checkbox" id="switch_undertime" value="1" {{ !empty($settings->enable_undertime) ? 'checked' : '' }}>
                    <label class="onoffswitch-label" for="switch_undertime">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <p class="text-muted">{{ __('Automatically deduct pay for hours worked less than the required daily hours.') }}</p>
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Required Daily Hours') }}</label>
                        <input class="form-control" type="number" step="0.5" name="undertime_threshold_hours" value="{{ $settings->undertime_threshold_hours ?? '8' }}" placeholder="8">
                        <small class="form-text text-muted">{{ __('Minimum hours required per day (default: 8 hours)') }}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Undertime Deduction Rate') }}</label>
                        <input class="form-control" type="number" step="0.01" name="undertime_rate_multiplier" value="{{ $settings->undertime_rate_multiplier ?? '1' }}" placeholder="1">
                        <small class="form-text text-muted">{{ __('Deduction multiplier (1 = 100%, 0.5 = 50% of hourly rate)') }}</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Hourly Rate Calculation') }}</label>
                        <select class="form-control" name="undertime_calculation_method">
                            <option value="calendar_days" {{ (!empty($settings->undertime_calculation_method) && $settings->undertime_calculation_method === 'calendar_days') ? 'selected' : '' }}>{{ __('Monthly Salary ÷ 30 days ÷ 8 hours') }}</option>
                            <option value="working_days" {{ (!empty($settings->undertime_calculation_method) && $settings->undertime_calculation_method === 'working_days') ? 'selected' : '' }}>{{ __('Monthly Salary ÷ 22 days ÷ 8 hours') }}</option>
                        </select>
                        <small class="form-text text-muted">{{ __('Method to calculate hourly rate for undertime') }}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Default Undertime Hours (Sample)') }}</label>
                        <input class="form-control" type="number" step="0.5" name="default_undertime_hours" value="{{ $settings->default_undertime_hours ?? '1' }}" placeholder="1">
                        <small class="form-text text-muted">{{ __('Sample undertime hours for testing (can be changed per employee)') }}</small>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <small>
                    <i class="fa-solid fa-info-circle"></i>
                    <strong>{{ __('How it works:') }}</strong><br>
                    {{ __('Hourly Rate = Monthly Salary ÷ Days per Month ÷ 8 hours') }}<br>
                    {{ __('Undertime Deduction = Undertime Hours × Hourly Rate × Multiplier') }}<br>
                    {{ __('Example: ₱20,000/month ÷ 30 days ÷ 8 hours = ₱83.33/hour. 1 UT hour × ₱83.33 × 1.0 = ₱83.33 deduction') }}
                </small>
            </div>

            <div class="alert alert-warning">
                <small>
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <strong>{{ __('Note:') }}</strong><br>
                    {{ __('Undertime deductions are applied when employees work less than the required daily hours. This is separate from absent days.') }}
                </small>
            </div>
        </div>

        <!-- Late Deduction Settings -->
        <div class="settings-widget">
            <div class="h3 card-title with-switch">{{ __('Late Arrival Deduction Settings') }}
                <div class="onoffswitch">
                    <input type="hidden" name="enable_late_deduction" value="0">
                    <input type="checkbox" name="enable_late_deduction" class="onoffswitch-checkbox" id="switch_late_deduction" value="1" {{ !empty($settings->enable_late_deduction) ? 'checked' : '' }}>
                    <label class="onoffswitch-label" for="switch_late_deduction">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <p class="text-muted">{{ __('Automatically deduct pay for late clock-ins beyond the grace period.') }}</p>
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Grace Period (Minutes)') }}</label>
                        <input class="form-control" type="number" step="1" name="late_grace_minutes" value="{{ $settings->late_grace_minutes ?? '0' }}" placeholder="0">
                        <small class="form-text text-muted">{{ __('Minutes of tardiness before deduction starts (default: 0 = no grace)') }}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-block mb-3">
                        <label class="col-form-label">{{ __('Deduction Per Minute (Optional)') }}</label>
                        <input class="form-control" type="number" step="0.01" name="late_deduction_per_minute" value="{{ $settings->late_deduction_per_minute ?? '0' }}" placeholder="0">
                        <small class="form-text text-muted">{{ __('Fixed amount per minute late. Leave 0 to auto-calculate based on hourly rate.') }}</small>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <small>
                    <i class="fa-solid fa-info-circle"></i>
                    <strong>{{ __('How it works:') }}</strong><br>
                    {{ __('1. System tracks every late clock-in with minutes difference') }}<br>
                    {{ __('2. Late minutes beyond grace period are summed for the payroll period') }}<br>
                    {{ __('3. If deduction per minute = 0, hourly rate ÷ 60 is used') }}<br>
                    {{ __('4. Late Deduction = Total Late Minutes × Deduction Per Minute') }}<br>
                    {{ __('Example: 60 late minutes × ₱1.39/min (from ₱83.33/hr) = ₱83.40 deduction') }}
                </small>
            </div>

            <div class="alert alert-warning">
                <small>
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <strong>{{ __('Note:') }}</strong><br>
                    {{ __('Grace period applies to EACH late instance. If grace = 10 min and employee is 15 min late, only 5 minutes are deducted.') }}<br>
                    {{ __('This is separate from undertime. Late = clock-in after schedule start. Undertime = total hours worked less than required.') }}
                </small>
            </div>
        </div>

        <div class="submit-section">
            <button class="btn btn-primary submit-btn">{{ __('Save') }}</button>
        </div>
    </form>
@endsection

@push('page-scripts')
@endpush
