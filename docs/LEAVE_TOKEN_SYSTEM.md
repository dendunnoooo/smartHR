# Leave Token System Documentation

## Overview
The Leave Token System is an employee reward program that grants leave tokens to employees who maintain perfect weekly attendance. This incentivizes consistent attendance and provides tangible benefits to dedicated employees.

## How It Works

### Token Earning
- Employees earn **1 leave token** for every complete week of attendance
- A complete week is defined as **5 consecutive workdays (Monday-Friday)** with attendance records
- Tokens are automatically granted every Monday at 6:00 AM for the previous week
- Duplicate grants are prevented using the `last_granted_week` tracking field

### Token Balance Tracking
Each employee has a LeaveToken record that tracks:
- **Available Tokens** (`tokens`): Current balance available for use
- **Earned Tokens** (`earned_tokens`): Lifetime total tokens earned
- **Used Tokens** (`used_tokens`): Lifetime total tokens used
- **Last Granted Week** (`last_granted_week`): Week start date of last grant

## Database Structure

### Table: `leave_tokens`
```sql
CREATE TABLE leave_tokens (
    id BIGINT UNSIGNED PRIMARY KEY,
    user_id BIGINT UNSIGNED (Foreign Key to users.id, CASCADE on delete),
    tokens INT DEFAULT 0 COMMENT 'Available leave tokens',
    earned_tokens INT DEFAULT 0 COMMENT 'Total tokens earned',
    used_tokens INT DEFAULT 0 COMMENT 'Total tokens used',
    last_granted_week DATE NULL COMMENT 'Last week start date when token was granted',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Implementation Components

### 1. Model: `app/Models/LeaveToken.php`
**Methods:**
- `user()`: Relationship to User model
- `addTokens(int $amount = 1)`: Adds tokens and updates earned total
- `useTokens(int $amount = 1)`: Decrements tokens if available, updates used total

### 2. Command: `app/Console/Commands/GrantLeaveTokens.php`
**Signature:** `leave-tokens:grant`

**Options:**
- `--week=last`: Check last week (default)
- `--week=current`: Check current week

**Process:**
1. Calculates previous week date range (Monday-Friday)
2. Gets all active employees
3. For each employee:
   - Gets or creates LeaveToken record
   - Checks if token already granted for that week
   - Counts attendance records (needs exactly 5 days)
   - Grants 1 token if eligible
4. Displays summary statistics

**Example Output:**
```
Checking weekly attendance for leave token eligibility...
Checking week: Nov 03 - Nov 07, 2025
✓ Granted token to John Doe (Total: 3)
✓ Granted token to Jane Smith (Total: 5)
Summary:
  Tokens Granted: 2
  Employees Skipped: 2
  Total Employees Checked: 4
```

### 3. Scheduled Task: `routes/console.php`
```php
// Runs every Monday at 6:00 AM
Schedule::command('leave-tokens:grant')->weekly()->mondays()->at('06:00');
```

### 4. User Interface: Employee Dashboard
**Location:** `resources/views/pages/employees/dashboard.blade.php`

**Features:**
- Displays available token balance (large, prominent)
- Shows lifetime earned tokens
- Shows lifetime used tokens
- Includes informational alert explaining how to earn tokens

**Widget Structure:**
- Colorful icon (ticket symbol in orange)
- Three columns showing statistics:
  - Available (green) - current balance
  - Total Earned (blue) - lifetime earned
  - Total Used (info) - lifetime used
- Info alert with earning instructions

## Manual Command Execution

### Grant tokens for last week:
```bash
php artisan leave-tokens:grant
```

### Grant tokens for current week:
```bash
php artisan leave-tokens:grant --week=current
```

### Test with verbose output:
```bash
php artisan leave-tokens:grant -v
```

## Business Rules

### Eligibility Criteria
1. Employee must be active (`is_active = true`)
2. Employee must have exactly 5 attendance records for Monday-Friday
3. Week must not have already been rewarded (checked via `last_granted_week`)
4. Employee must be of type `EMPLOYEE` (not admin, HR, etc.)

### Token Usage (Future Integration)
- Tokens can be used when requesting leave
- When leave request is approved, `useTokens()` method is called
- Tokens cannot be used if balance is insufficient
- Usage is tracked in `used_tokens` field for audit purposes

## Integration Points

### Current Integrations
✅ User model relationship (`leaveToken()`)
✅ Employee dashboard widget
✅ Automated weekly granting (scheduled)
✅ Manual command execution

### Future Integrations
- [ ] Leave request form (option to use token)
- [ ] Leave approval process (token deduction)
- [ ] Token history/transaction log
- [ ] Admin panel to view employee token balances
- [ ] Reports showing token usage statistics
- [ ] Notifications when tokens are earned

## Configuration

### Settings Location
Currently hardcoded in command logic:
- Week definition: Monday-Friday (5 days)
- Token grant amount: 1 token per perfect week
- Schedule: Monday 6:00 AM

### Future Configuration Options
Could be moved to `settings` table:
- Enable/disable token system
- Token amount per perfect week
- Week definition (5 days vs 6 days)
- Minimum attendance percentage
- Token expiration policy
- Maximum token accumulation

## Testing

### Manual Testing Steps
1. Create attendance records for an employee (Mon-Fri)
2. Run command: `php artisan leave-tokens:grant`
3. Check employee dashboard for updated token balance
4. Verify database record in `leave_tokens` table

### Sample Data Creation
Use seeders to create test attendance:
```php
// Create 5 consecutive days of attendance
$employee = User::find(1);
$monday = Carbon::now()->previous(Carbon::MONDAY);

for ($i = 0; $i < 5; $i++) {
    Attendance::create([
        'user_id' => $employee->id,
        'startDate' => $monday->copy()->addDays($i),
        'endDate' => $monday->copy()->addDays($i),
    ]);
}
```

## Troubleshooting

### No tokens granted
**Possible causes:**
- Employee doesn't have 5 attendance records for the week
- Token already granted for that week
- Employee is not active
- Attendance dates don't fall on Monday-Friday

**Solution:** Check attendance records count and dates

### Duplicate grants
**Prevention:** `last_granted_week` field prevents duplicates
**Check:** Query `SELECT user_id, last_granted_week FROM leave_tokens`

### Command not running automatically
**Check:**
1. Verify Laravel scheduler is set up in cron: `* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1`
2. Run `php artisan schedule:list` to see scheduled tasks
3. Check logs: `storage/logs/laravel.log`

## Related Documentation
- [PAYSLIP_AUTOMATION.md](PAYSLIP_AUTOMATION.md) - Automated payslip generation
- [PHILIPPINE_HOLIDAYS.md](PHILIPPINE_HOLIDAYS.md) - Holiday management system
- [ANNUAL_HOLIDAYS.md](ANNUAL_HOLIDAYS.md) - Annual holiday repeat system

## Version History
- **v1.0** (2025-11-15): Initial implementation with weekly granting and dashboard widget
