<?php

use App\Jobs\AutoClockoutUnsignedAttendances;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::job(new AutoClockoutUnsignedAttendances())->cron("0 */8 * * *");

// Update annual holidays on January 1st at 12:01 AM
Schedule::command('holidays:update-annual')->yearlyOn(1, 1, '00:01');

// Automatic monthly payslip generation - runs daily at 1 AM and checks if today is the configured day
Schedule::command('payslips:generate-monthly --send-email')->dailyAt('01:00')->when(function(){
    $settings = SalarySettings();
    if(empty($settings->enable_auto_payslip)){
        return false;
    }
    $targetDay = $settings->auto_payslip_day ?? 25;
    return now()->day == $targetDay;
});

// Bi-Monthly Payroll Automation - runs on 15th and last day of month at 2 AM
Schedule::command('payslips:generate-bi-monthly --cutoff=auto --send-email')
    ->dailyAt('02:00')
    ->when(function(){
        $settings = SalarySettings();
        // Check if bi-monthly is enabled
        if(!empty($settings->enable_semi_monthly_payroll)){
            $today = now();
            // Run on 15th or last day of month
            return $today->day === 15 || $today->isLastOfMonth();
        }
        return false;
    });

// Grant leave credits for perfect weekly attendance - runs every Monday at 6 AM
Schedule::command('leave-tokens:grant')->weekly()->mondays()->at('06:00');

// Grant monthly tokens for perfect monthly attendance - runs on 1st of each month at 6 AM
Schedule::command('monthly-tokens:grant')->monthlyOn(1, '06:00');

// Rotate employee schedules based on configured rotation day - runs daily at 12:01 AM
Schedule::command('schedules:rotate')->dailyAt('00:01');