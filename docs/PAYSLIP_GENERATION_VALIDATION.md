# Payslip Generation Validation

## Overview
The semi-monthly payslip generation system now includes strict date validation to ensure payslips are only generated on the appropriate cutoff days. This validation applies to:
- Manual command generation (`php artisan payslips:generate-semi-monthly`)
- "Add Payslip" button (individual employee)
- "Generate for All Employees" button (bulk generation)

## Validation Rules

### 1st Cutoff (1-15)
- **Generation Day**: 15th of the month ONLY
- **Payslip Period**: Covers work from 1st to 15th
- **Payment Date**: 15th

### 2nd Cutoff (16-end)
- **Generation Day**: Last day of the month ONLY
- **Payslip Period**: Covers work from 16th to last day
- **Payment Date**: Last day of the month

## Command Usage

### Automatic Mode (Recommended)
```bash
php artisan payslips:generate-semi-monthly --cutoff=auto
```
- Automatically detects if today is 15th or last day
- Fails with error if run on any other day
- Used by the scheduled cron job

### Manual Mode with Validation
```bash
# Generate 1st cutoff (only works on 15th)
php artisan payslips:generate-semi-monthly --cutoff=1

# Generate 2nd cutoff (only works on last day)
php artisan payslips:generate-semi-monthly --cutoff=2
```

### Force Mode (Override Validation)
```bash
# Bypass validation for testing or special cases
php artisan payslips:generate-semi-monthly --cutoff=1 --force
php artisan payslips:generate-semi-monthly --cutoff=2 --force
```

**⚠️ Warning**: Use `--force` flag only when absolutely necessary (testing, corrections, special circumstances).

## Error Messages

### Running on Wrong Day
```
Generation failed: 1st cutoff payslips can only be generated on the 15th of the month. Today is the 18th.
Hint: Use --force to bypass date validation.
```

### Auto Mode on Non-Payout Day
```
Today is not a scheduled payout day (15th or last day of month).
Use --cutoff=1 or --cutoff=2 to force generation.
```

## Scheduled Automation

The cron job runs daily at 2:00 AM and automatically checks the date:

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

- Only runs if `enable_semi_monthly_payroll` setting is enabled
- Only executes on 15th or last day of the month
- Uses `--cutoff=auto` to detect the correct cutoff automatically
- Sends email notifications to employees

## Service Layer Validation

The `SemiMonthlyPayrollService` includes a `validateCutoffDay()` method:

```php
protected function validateCutoffDay(int $cutoffNumber, Carbon $date): void
{
    $day = $date->day;
    $isLastDay = $date->isLastOfMonth();
    
    if ($cutoffNumber === 1 && $day !== 15) {
        throw new \Exception("1st cutoff payslips can only be generated on the 15th...");
    }
    
    if ($cutoffNumber === 2 && !$isLastDay) {
        throw new \Exception("2nd cutoff payslips can only be generated on the last day...");
    }
}
```

Validation can be bypassed programmatically by passing `$bypassDateValidation = true`:

```php
$results = $payrollService->generateSemiMonthlyPayslips(
    cutoffNumber: 1,
    date: now(),
    sendEmail: true,
    bypassDateValidation: true // Skip validation
);
```

## Benefits

1. **Prevents Errors**: Ensures payslips aren't generated on incorrect dates
2. **Compliance**: Maintains consistency with payroll schedule
3. **Auditability**: Clear error messages when attempted on wrong days
4. **Flexibility**: `--force` flag available for legitimate exceptions
5. **Automation Safety**: Cron job won't accidentally run on wrong days
6. **UI Integration**: Web interface shows warnings and prevents invalid submissions

## Web Interface Validation

### Add Payslip Button
When creating individual payslips through the web interface:
1. Semi-Monthly option is available in the Type dropdown
2. Selecting "Semi-Monthly (15th & Last Day Only)" shows no additional fields
3. Attempting to submit on wrong day shows validation error:
   ```
   Semi-monthly payslips can only be generated on the 15th or last day of the month. Today is the 18th.
   ```

### Generate for All Employees Button
When bulk generating payslips:
1. Semi-Monthly option is available in the Type dropdown
2. A warning alert appears when Semi-Monthly is selected:
   ```
   ⚠️ Important: Semi-monthly payslips can only be generated on the 15th or last day of the month. 
   Attempting to generate on other days will result in an error.
   ```
3. Attempting to submit on wrong day redirects back with error message:
   ```
   Semi-monthly payslips can only be generated on the 15th or last day of the month. Today is the 18th. 
   Please wait until the appropriate cutoff day or use the semi-monthly command with --force flag.
   ```

### UI Features
- **Dynamic Warning**: Warning alert appears/disappears based on selected type
- **Clear Labeling**: Dropdown option labeled "Semi-Monthly (15th & Last Day Only)"
- **Error Messages**: User-friendly error messages with specific dates
- **Form Preservation**: Input data retained after validation error

## Testing Scenarios

### Test 1: Valid Generation Day
```bash
# On November 15th
php artisan payslips:generate-semi-monthly --cutoff=1
# ✅ Success
```

### Test 2: Invalid Generation Day
```bash
# On November 18th
php artisan payslips:generate-semi-monthly --cutoff=1
# ❌ Fails with validation error
```

### Test 3: Force Override
```bash
# On November 18th
php artisan payslips:generate-semi-monthly --cutoff=1 --force
# ✅ Success with warning
```

### Test 4: Auto Detection on Valid Day
```bash
# On November 30th (last day)
php artisan payslips:generate-semi-monthly --cutoff=auto
# ✅ Detects cutoff=2 and generates
```

### Test 5: Auto Detection on Invalid Day
```bash
# On November 18th
php artisan payslips:generate-semi-monthly --cutoff=auto
# ❌ Fails - not a payout day
```

## Best Practices

1. **Use Auto Mode**: Always use `--cutoff=auto` for scheduled jobs
2. **Avoid Force**: Only use `--force` for testing or corrections
3. **Monitor Logs**: Check scheduled task logs to ensure proper execution
4. **Test Monthly**: Verify generation works on both cutoff days
5. **Document Overrides**: If using `--force`, document the reason

## Related Documentation

- [Semi-Monthly Payroll System](./SEMI_MONTHLY_PAYROLL.md)
- [Payslip Automation](./PAYSLIP_AUTOMATION.md)
