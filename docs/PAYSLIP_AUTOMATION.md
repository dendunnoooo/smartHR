# Payslip Automation Documentation

## Overview
This system now supports automatic payslip calculation and generation for all employees with a single click or on a scheduled basis.

## Features Implemented

### 1. Bulk Payslip Generation (One-Click)
Generate payslips for all active employees at once from the Payslips page.

**How to use:**
1. Navigate to **Payroll > Payslips**
2. Click the **"Generate for All Employees"** button (green button next to "Add Payslip")
3. In the modal:
   - Select payslip type (Monthly, Weekly, or Hourly)
   - Set the payslip date
   - For Hourly: specify from/to dates
   - For Weekly: specify number of weeks
   - Check "Send email notification" to email all employees
4. Click **"Generate Payslips"**

**What happens:**
- The system iterates through all active employees who have salary details configured
- Automatically calculates:
  - Base salary (from employee salary settings)
  - COLA and HRA allowances (if enabled in salary settings)
  - Provident Fund, ESI, Pag-IBIG deductions (if enabled)
  - Withholding tax (if enabled)
  - Any custom allowances/deductions for each employee
- Creates a payslip record for each employee
- Optionally sends email notifications with payslip details
- Shows summary: "Successfully generated X payslips. Y employees skipped."

**Employees are skipped if:**
- They don't have salary details configured in their profile
- An error occurs during calculation (logged in the response)

---

### 2. Absent Deduction Settings
Automatically deduct from employee salaries for absent days based on attendance records.

**How to configure:**
1. Navigate to **Settings > Salary Settings**
2. Scroll to the **"Absent Deduction Settings"** section
3. Toggle the switch to **ON**
4. Configure:
   - **Daily Deduction Amount**: Fixed amount per absent day (e.g., ₱500)
     - OR
   - **Percentage of Daily Rate**: Percentage of calculated daily salary (e.g., 100% = full day's pay)
   - **Calculation Method**:
     - **Calendar Days**: Monthly salary ÷ 30 days
     - **Working Days Only**: Monthly salary ÷ 22 days (excludes weekends)
5. Click **Save**

**Example Scenarios:**

*Scenario 1: Fixed Amount*
- Setting: ₱500 per absent day
- Employee absent: 3 days
- Deduction: 3 × ₱500 = **₱1,500**

*Scenario 2: Percentage*
- Monthly Salary: ₱20,000
- Method: Working Days (22 days)
- Daily Rate: ₱20,000 ÷ 22 = ₱909.09
- Percentage: 100%
- Employee absent: 2 days
- Deduction: 2 × (₱909.09 × 100%) = **₱1,818.18**

*Scenario 3: Half-Day Rate*
- Percentage: 50%
- Daily Rate: ₱909.09
- Employee absent: 1 day
- Deduction: 1 × (₱909.09 × 50%) = **₱454.55**

**How it works:**
- The system checks attendance records in the `attendances` table
- Counts days with attendance records as "present days"
- Calculates: `Absent Days = Total Days in Period - Present Days`
- Applies deduction automatically when generating payslips
- Deduction appears as "Absent Deduction (Auto)" in the payslip

**Sample Configuration Values:**
- **Daily Amount**: ₱500.00 (typical daily rate for entry-level)
- **Percentage**: 100% (full day's pay deduction)
- **Method**: Calendar Days (for monthly salary employees)

---

### 3. Automated Payslip Generation (Scheduled)
Automatically generate and send payslips on a specific day each month.

**How to configure:**
1. Navigate to **Settings > Salary Settings**
2. Scroll to the **"Automated Payslip Generation"** section
3. Toggle the switch to **ON**
4. Configure:
   - **Generation Day of Month**: 1-31 (e.g., 25 = payslips generated on the 25th each month)
   - **Payslip Type**: Monthly or Weekly
   - Check **"Automatically send email notifications"** if you want employees to receive emails
5. Click **Save**

**Setting up the scheduler:**

For automation to work, you must configure a system cron job to run Laravel's scheduler.

#### On Linux/macOS (cPanel, VPS, or local server):
Add this cron job entry (edit with `crontab -e`):
```bash
* * * * * cd /path/to/smarthr && php artisan schedule:run >> /dev/null 2>&1
```

#### On Windows (XAMPP):
Use Windows Task Scheduler:
1. Open Task Scheduler
2. Create a new task:
   - Trigger: Daily at a specific time (e.g., 1:00 AM)
   - Action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `C:\xampp\htdocs\smarthr\artisan schedule:run`
3. Save the task

**What the scheduler does:**
- Runs daily at 1:00 AM
- Checks if today's day matches the configured "Generation Day of Month"
- If automation is enabled and the day matches, it runs:
  ```bash
  php artisan payslips:generate-monthly --send-email
  ```
- Generates payslips for all employees and sends email notifications

---

### 4. Manual Command (CLI)
You can also run the payslip generation manually via command line.

**Command:**
```bash
php artisan payslips:generate-monthly [options]
```

**Options:**
- `--type=monthly` (default) or `--type=weekly` or `--type=hourly`
- `--date=YYYY-MM-DD` (default: today)
- `--send-email` — Send email notifications to employees

**Examples:**
```bash
# Generate monthly payslips for today and send emails
php artisan payslips:generate-monthly --send-email

# Generate weekly payslips for a specific date
php artisan payslips:generate-monthly --type=weekly --date=2025-11-30 --send-email

# Generate without sending emails
php artisan payslips:generate-monthly
```

**Output:**
The command shows a progress bar and final summary:
```
Generating monthly payslips for 2025-11-15...
[████████████████████████████] 100%
✓ Successfully generated 45 payslips.
⚠ Skipped 3 employees.
```

---

## Calculation Details

**Net Pay Formula:**
```
Net Pay = Base Salary + Total Allowances - Total Deductions
```

**Allowances Applied (if enabled in Salary Settings):**
- COLA (Cost of Living Allowance): `base_salary * da_percent / 100`
- HRA (Housing/Rent Allowance): `base_salary * hra_percent / 100`
- Any custom employee-specific allowances

**Deductions Applied (if enabled in Salary Settings):**
- Provident Fund (SSS): `base_salary * emp_pf_percentage / 100`
- ESI (PhilHealth): `base_salary * emp_esi_percentage / 100`
- Pag-IBIG: `base_salary * emp_pagibig_percentage / 100`
- Withholding Tax (BIR): `base_salary * emp_withholding_percentage / 100`
- **Absent Deduction**: `absent_days × (fixed_amount OR daily_rate × percentage)`
  - Daily Rate = `base_salary / days_per_month` (30 for calendar days, 22 for working days)
  - Absent Days = Total days in period - Days with attendance records
  - Example: 2 absent days × ₱500 = ₱1,000 deduction
- Any custom employee-specific deductions

**For Hourly Payslips:**
```
Net Pay = (Total Hours * Base Salary) + Total Allowances - Total Deductions
```

**For Weekly Payslips:**
```
Net Pay = (Number of Weeks * Base Salary) + Total Allowances - Total Deductions
```

---

## Troubleshooting

### "X employees skipped"
- Check that skipped employees have salary details configured in their profile
- Navigate to **Employees > [Employee Name] > Bank Statutory** tab
- Ensure "Salary basis" and "Salary amount" are set

### Automation not running
- Verify the cron job is set up correctly (run `crontab -l` on Linux)
- Check that "Automated Payslip Generation" is enabled in Salary Settings
- Verify the configured day of month matches today's date
- Check Laravel logs: `storage/logs/laravel.log`

### Emails not sending
- Verify mail settings in `.env` (MAIL_HOST, MAIL_PORT, etc.)
- Test email with: `php artisan tinker` then `Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'))`
- For local testing, use MailHog or Mailtrap

### Route not found error
- Clear caches: `php artisan route:clear && php artisan config:clear`
- Verify the route exists: `php artisan route:list --name=payslips.bulk`

---

## Permissions
The bulk generation button and automation settings require the `create-payslip` permission.

---

## Notes
- Payslips are automatically assigned sequential IDs (PS-0001, PS-0002, etc.)
- Each payslip links to the allowances/deductions used in calculation
- Employees can view their latest payslip from the dashboard
- Admins can view, edit, and delete payslips from the Payslips page
- Email notifications use the `PayslipCreatedNotification` class

---

## Support
For issues or questions, check the application logs or contact your system administrator.
