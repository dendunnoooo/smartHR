# Semi-Monthly Payroll System

## Overview
The semi-monthly payroll system automatically splits an employee's monthly base salary into **two equal payouts** per month.

---

## System Configuration

### Database Structure
Added fields to `payslips` table:
- `cutoff_period` - Description of cutoff (e.g., "1st Cutoff (1-15)")
- `cutoff_number` - Numeric identifier (1 or 2)
- `is_semi_monthly` - Boolean flag for semi-monthly payslips

### Payment Schedule

| Cutoff | Period Coverage | Payment Date |
|--------|----------------|--------------|
| **1st Cutoff** | 1st to 15th of month | **15th** |
| **2nd Cutoff** | 16th to last day of month | **Last day of month** |

---

## Calculation Logic

### Base Formula
```
Semi-Monthly Amount = Monthly Salary ÷ 2
```

### Example Calculation

**Monthly Salary: ₱20,000**

#### 1st Cutoff (Paid on 15th)
- Coverage: November 1-15, 2025
- Base Semi-Monthly Pay: ₱10,000

**Allowances (all divided by 2):**
- COLA (5%): ₱1,000 ÷ 2 = ₱500
- HRA (3%): ₱600 ÷ 2 = ₱300

**Deductions (all divided by 2):**
- SSS/PF (4%): ₱800 ÷ 2 = ₱400
- PhilHealth (2%): ₱400 ÷ 2 = ₱200
- Pag-IBIG (2%): ₱400 ÷ 2 = ₱200
- Tax (TRAIN): ₱0 ÷ 2 = ₱0

**Net Pay (1st Cutoff): ₱10,000 + ₱800 - ₱800 = ₱10,000**

#### 2nd Cutoff (Paid on 30th)
- Coverage: November 16-30, 2025
- Base Semi-Monthly Pay: ₱10,000
- Same allowances and deductions as 1st cutoff
- **Net Pay (2nd Cutoff): ₱10,000**

**Total Monthly: ₱10,000 + ₱10,000 = ₱20,000**

---

## Automation

### Cron Schedule
The system automatically generates payslips at **2:00 AM** on:
- **15th of each month** (1st cutoff)
- **Last day of each month** (2nd cutoff)

### Schedule Configuration (routes/console.php)
```php
Schedule::command('payslips:generate-semi-monthly --cutoff=auto --send-email')
    ->dailyAt('02:00')
    ->when(function(){
        $settings = SalarySettings();
        if(!empty($settings->enable_semi_monthly_payroll)){
            $today = now();
            return $today->day === 15 || $today->isLastOfMonth();
        }
        return false;
    });
```

---

## Manual Command Usage

### Basic Usage
```bash
# Auto-detect cutoff based on today's date (must be 15th or last day)
php artisan payslips:generate-semi-monthly --cutoff=auto

# Force 1st cutoff generation
php artisan payslips:generate-semi-monthly --cutoff=1

# Force 2nd cutoff generation
php artisan payslips:generate-semi-monthly --cutoff=2
```

### Advanced Options
```bash
# Generate for specific date
php artisan payslips:generate-semi-monthly --cutoff=1 --date=2025-11-15

# Generate and send email notifications
php artisan payslips:generate-semi-monthly --cutoff=1 --send-email

# Verbose output with detailed results
php artisan payslips:generate-semi-monthly --cutoff=1 -v
```

---

## Admin Settings

Navigate to **Settings > Salary Settings** to configure:

### Semi-Monthly Payroll Toggle
- **Enable/Disable** semi-monthly payroll system
- When enabled, system automatically generates payslips on 15th and last day

### Email Notifications
- **Automatically send emails** to employees when payslips are generated

---

## Features

### ✅ Implemented
1. **Automatic Salary Splitting** - Monthly salary divided equally
2. **Equal Allowances/Deductions** - All amounts split 50/50
3. **TRAIN Tax Calculation** - Progressive tax applied and split
4. **Automated Schedule** - Runs on 15th and last day automatically
5. **Manual Override** - Force generation for any cutoff
6. **Email Notifications** - Optional email sending
7. **Duplicate Prevention** - Won't generate same cutoff twice
8. **Database Tracking** - All payslips properly recorded with cutoff info

### 📊 Output Information
Each generated payslip includes:
- **Cutoff Period** - "1st Cutoff (1-15)" or "2nd Cutoff (16-30)"
- **Cutoff Number** - 1 or 2
- **Payment Date** - 15th or last day of month
- **Period Coverage** - Start and end dates
- **Semi-Monthly Flag** - Marked as semi-monthly payslip
- **Detailed Breakdown** - All earnings, allowances, and deductions

---

## Testing

### Test 1st Cutoff
```bash
php artisan payslips:generate-semi-monthly --cutoff=1 -v
```

**Expected Output:**
- Generates payslips for all active employees
- Coverage: 1st to 15th of current month
- Payment date: 15th
- Shows success count and detailed results

### Test 2nd Cutoff
```bash
php artisan payslips:generate-semi-monthly --cutoff=2 --date=2025-11-30 -v
```

**Expected Output:**
- Generates payslips for all active employees
- Coverage: 16th to 30th of November
- Payment date: November 30, 2025
- Shows success count and detailed results

### Test Auto-Detection
```bash
# Only works if run on 15th or last day of month
php artisan payslips:generate-semi-monthly --cutoff=auto
```

---

## How It Works

### Service Class: `SemiMonthlyPayrollService`
Located in `app/Services/SemiMonthlyPayrollService.php`

**Key Methods:**
1. `generateSemiMonthlyPayslips()` - Main generation logic
2. `createSemiMonthlyPayslip()` - Create individual payslip
3. `getTodayCutoff()` - Auto-detect if today is payout day

### Command: `GenerateSemiMonthlyPayslips`
Located in `app/Console/Commands/GenerateSemiMonthlyPayslips.php`

**Features:**
- Interactive progress bar
- Detailed result table
- Verbose mode for debugging
- Error handling and reporting

---

## Database Structure

### Payslips Table
```sql
-- New fields added
cutoff_period VARCHAR(255)     -- "1st Cutoff (1-15)" or "2nd Cutoff (16-30)"
cutoff_number INT              -- 1 or 2
is_semi_monthly BOOLEAN        -- TRUE for semi-monthly payslips
```

### PayslipItems Table
- Links to payslip_id
- Stores all earnings, allowances, and deductions
- Type: 'earning', 'allowance', 'deduction'

---

## Benefits

1. **Improved Cash Flow** - Employees receive income twice per month
2. **Better Budgeting** - Smaller, more frequent payments
3. **Automated Process** - No manual intervention needed
4. **Accurate Calculations** - All deductions properly split
5. **Tax Compliance** - TRAIN Law tax automatically applied
6. **Audit Trail** - Complete database records with cutoff info
7. **Flexible Override** - Manual generation when needed

---

## Important Notes

⚠️ **All amounts are split equally** - This includes:
- Base salary
- Allowances (COLA, HRA, custom)
- Deductions (SSS, PhilHealth, Pag-IBIG, tax, custom)

⚠️ **Duplicate Prevention** - System checks if payslip already exists for a specific cutoff before generating

⚠️ **Email Requirement** - Must configure email settings for notifications to work

⚠️ **Cron Job Required** - Add this to your server cron:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Troubleshooting

### Payslips show ₱0.00
- **Cause**: Employees don't have salary configured
- **Solution**: Go to Employees > Edit > Salary tab and set monthly salary

### Cron not running
- **Cause**: Laravel scheduler not configured on server
- **Solution**: Add cron job (see Important Notes section)

### Duplicate payslips
- **Cause**: Command run multiple times for same cutoff
- **Solution**: System prevents duplicates automatically. Existing payslips will be skipped

### Wrong cutoff date
- **Cause**: Using --date parameter incorrectly
- **Solution**: Use format YYYY-MM-DD (e.g., 2025-11-15)

---

## Future Enhancements

Possible additions:
- Pro-rated calculations for new employees (first cutoff)
- Attendance-based adjustments per cutoff
- Different rates for 1st vs 2nd cutoff
- Advance payout option
- Loan deductions spread across cutoffs
- Overtime integration per cutoff period

---

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Run command with `-v` flag for verbose output
3. Verify database migrations ran successfully
4. Check salary settings are enabled
