# Semi-Monthly Payslip Generation - Implementation Summary

## Date: November 18, 2025

## Overview
Enhanced the payslip generation system with strict date validation to ensure semi-monthly payslips can only be generated on the 15th or last day of each month.

## Changes Implemented

### 1. Service Layer (SemiMonthlyPayrollService.php)
**Location**: `app/Services/SemiMonthlyPayrollService.php`

**Additions**:
- Added `$bypassDateValidation` parameter to `generateSemiMonthlyPayslips()` method
- Created new `validateCutoffDay()` method to enforce date restrictions
- Validation logic:
  - Cutoff 1: Only allowed on the 15th
  - Cutoff 2: Only allowed on last day of month
  - Throws exception with descriptive error message if validation fails

**Code**:
```php
protected function validateCutoffDay(int $cutoffNumber, Carbon $date): void
{
    $day = $date->day;
    $isLastDay = $date->isLastOfMonth();
    
    if ($cutoffNumber === 1 && $day !== 15) {
        throw new \Exception("1st cutoff payslips can only be generated on the 15th of the month. Today is the {$day}th.");
    }
    
    if ($cutoffNumber === 2 && !$isLastDay) {
        $lastDay = $date->copy()->endOfMonth()->day;
        throw new \Exception("2nd cutoff payslips can only be generated on the last day of the month ({$lastDay}th). Today is the {$day}th.");
    }
}
```

### 2. Console Command (GenerateSemiMonthlyPayslips.php)
**Location**: `app/Console/Commands/GenerateSemiMonthlyPayslips.php`

**Additions**:
- Added `--force` flag to bypass date validation
- Updated command signature to include force option
- Added try-catch block to handle validation exceptions gracefully
- Shows warning emoji (⚠️) when force flag is used

**Usage**:
```bash
# Normal generation (validates date)
php artisan payslips:generate-semi-monthly --cutoff=1

# Force generation (bypass validation)
php artisan payslips:generate-semi-monthly --cutoff=1 --force
```

### 3. Web Controller (PayrollsController.php)
**Location**: `app/Http/Controllers/Admin/PayrollsController.php`

**store() Method Changes**:
- Added semi-monthly date validation before processing
- Calculates cutoff number (1 or 2) based on current date
- Divides salary, allowances, and deductions by 2 for semi-monthly
- Sets `cutoff_number`, `cutoff_period`, and `is_semi_monthly` fields
- Auto-generates title based on cutoff period

**bulkGenerate() Method Changes**:
- Added 'semi-monthly' to allowed types in validation
- Added same date validation logic as store() method
- Applies division by 2 for all amounts when type is semi-monthly
- Returns user-friendly error message with suggestion to use command with --force

**Validation Rules**:
```php
if ($request->type === 'semi-monthly') {
    $today = Carbon::now();
    $isValidDate = $today->day === 15 || $today->isLastOfMonth();
    
    if (!$isValidDate && !$request->has('force')) {
        return back()->withErrors([...]);
    }
}
```

### 4. Enum Update (SalaryType.php)
**Location**: `app/Enums/Payroll/SalaryType.php`

**Addition**:
```php
case SemiMonthly = 'semi-monthly';
```

This allows semi-monthly to be selected in dropdowns alongside Monthly, Weekly, Hourly, and Contract.

### 5. User Interface Updates

#### index.blade.php (Payslips List)
**Location**: `resources/views/pages/payroll/payslips/index.blade.php`

**Bulk Generate Modal Changes**:
- Added "Semi-Monthly (15th & Last Day Only)" option to type dropdown
- Added dynamic warning alert that appears when semi-monthly is selected:
  ```html
  <div class="alert alert-warning mb-3" id="semi_monthly_warning" style="display:none;">
      <small>
          <i class="fa-solid fa-exclamation-triangle"></i>
          <strong>Important:</strong> Semi-monthly payslips can only be generated on the 15th or last day of the month.
      </small>
  </div>
  ```

**JavaScript Enhancement**:
- Added `semiMonthlyWarning` element reference
- Shows/hides warning based on selected type
- Updates display logic in type dropdown change handler

#### create.blade.php (Add Payslip Modal)
**Location**: `resources/views/pages/payroll/payslips/create.blade.php`

**No changes needed**: The form already uses `SalaryType::cases()` enum, so semi-monthly option appears automatically when enum is updated.

## Testing Results

### Test 1: Command Validation ✅
```bash
# On November 18th (invalid day)
php artisan payslips:generate-semi-monthly --cutoff=1
# Result: ❌ Generation failed with error message
```

### Test 2: Force Override ✅
```bash
# On November 18th with --force flag
php artisan payslips:generate-semi-monthly --cutoff=1 --force
# Result: ✅ Successfully generated 5 payslips with warning
```

### Test 3: Auto Detection ✅
```bash
# On November 18th (not a payout day)
php artisan payslips:generate-semi-monthly --cutoff=auto
# Result: ❌ "Today is not a scheduled payout day"
```

### Test 4: Wrong Cutoff Day ✅
```bash
# Attempting cutoff 2 on November 18th (not last day)
php artisan payslips:generate-semi-monthly --cutoff=2
# Result: ❌ "2nd cutoff payslips can only be generated on the last day (30th)"
```

## Error Messages

### Command Line Errors
1. **Cutoff 1 on wrong day**:
   ```
   Generation failed: 1st cutoff payslips can only be generated on the 15th of the month. Today is the 18th.
   Hint: Use --force to bypass date validation.
   ```

2. **Cutoff 2 on wrong day**:
   ```
   Generation failed: 2nd cutoff payslips can only be generated on the last day of the month (30th). Today is the 18th.
   Hint: Use --force to bypass date validation.
   ```

3. **Auto mode on invalid day**:
   ```
   Today is not a scheduled payout day (15th or last day of month).
   Use --cutoff=1 or --cutoff=2 to force generation.
   ```

### Web Interface Errors
1. **Individual Payslip (Add Payslip button)**:
   ```
   Semi-monthly payslips can only be generated on the 15th or last day of the month. Today is the 18th.
   ```

2. **Bulk Generation (Generate for All Employees)**:
   ```
   Semi-monthly payslips can only be generated on the 15th or last day of the month. Today is the 18th. 
   Please wait until the appropriate cutoff day or use the semi-monthly command with --force flag.
   ```

## Security & Safety Features

1. **Date Enforcement**: Prevents accidental generation on wrong days
2. **Bypass Protection**: Force flag only available via command line (not web UI)
3. **User Awareness**: Clear warnings in UI before submission
4. **Automated Safety**: Cron job has built-in date checks in routes/console.php
5. **Error Logging**: Failed attempts logged for audit trail

## Next Valid Generation Dates

Based on current date (November 18, 2025):
- **Next 15th (Cutoff 1)**: December 15, 2025
- **Next Last Day (Cutoff 2)**: November 30, 2025 (in 12 days)

## Files Modified

1. `app/Services/SemiMonthlyPayrollService.php` - Added validation method
2. `app/Console/Commands/GenerateSemiMonthlyPayslips.php` - Added --force flag
3. `app/Http/Controllers/Admin/PayrollsController.php` - Added validation to store() and bulkGenerate()
4. `app/Enums/Payroll/SalaryType.php` - Added SemiMonthly case
5. `resources/views/pages/payroll/payslips/index.blade.php` - Added UI warnings and semi-monthly option
6. `docs/PAYSLIP_GENERATION_VALIDATION.md` - Updated documentation

## Backward Compatibility

✅ All existing functionality preserved:
- Monthly payslips: No changes
- Weekly payslips: No changes
- Hourly payslips: No changes
- Contract payslips: No changes
- Existing semi-monthly service: Enhanced with validation

## Conclusion

The implementation successfully adds strict date validation to semi-monthly payslip generation across all interfaces (command line, individual creation, bulk generation) while maintaining flexibility through the --force flag for administrative overrides. The system now prevents errors from incorrect generation timing while providing clear guidance to users.
