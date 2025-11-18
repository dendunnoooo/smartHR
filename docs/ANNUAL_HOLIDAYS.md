# Annual Holidays Feature

## Overview
The SmartHR system now supports annual holidays that automatically repeat every year. When you mark a holiday as "Repeat Annually", the system will automatically update the holiday dates to the current year.

## How to Use

### Creating an Annual Holiday

1. Navigate to **Holidays** page
2. Click **Add Holiday** button
3. Fill in the holiday details:
   - **Name**: e.g., "Christmas Day", "New Year's Day"
   - **Start Date**: The date the holiday begins
   - **End Date**: The date the holiday ends
   - **Color**: Choose a color for calendar display
   - **Description**: Optional description
   - **Repeat Annually**: Toggle this ON to make it repeat every year

4. Click **Submit**

### Example Annual Holidays

Here are some common holidays you can add:

- **New Year's Day**: January 1st
- **Valentine's Day**: February 14th
- **Independence Day**: July 4th (if applicable)
- **Christmas Day**: December 25th
- **New Year's Eve**: December 31st

## How It Works

### Automatic Updates

The system automatically updates annual holiday dates using the following logic:

1. **Scheduled Task**: Every January 1st at 12:01 AM, the system runs the `holidays:update-annual` command
2. **Date Calculation**: For each annual holiday:
   - If the holiday is from a past year, it updates to the current year
   - The month and day remain the same
   - The duration (multi-day holidays) is preserved

### Manual Update

You can manually trigger the annual holidays update by running:

```bash
php artisan holidays:update-annual
```

This is useful if:
- You're testing the feature
- You need to update holidays mid-year
- You're setting up the system for the first time

### Calendar Display

Annual holidays are displayed in the calendar with:
- An "(Annual)" indicator in the event title
- The dates automatically adjusted to show the current year
- The same color you assigned when creating the holiday

### List View

In the holidays list:
- Annual holidays show a blue "Annual" badge next to their name
- You can edit them to change dates, description, or toggle the annual repeat
- Deleting an annual holiday removes it completely (it won't repeat next year)

## Technical Details

### Database Structure

The `holidays` table includes:
- `is_annual`: Boolean field (true/false)
- `startDate`: The holiday start date
- `endDate`: The holiday end date

### Scheduled Task

The automatic update is configured in `routes/console.php`:

```php
Schedule::command('holidays:update-annual')->yearlyOn(1, 1, '00:01');
```

This runs on January 1st at 12:01 AM every year.

### Console Command

Command: `php artisan holidays:update-annual`

Location: `app/Console/Commands/UpdateAnnualHolidays.php`

Features:
- Updates all annual holidays from past years to current year
- Preserves holiday duration for multi-day holidays
- Displays progress and results
- Safe to run multiple times (won't duplicate holidays)

## Best Practices

1. **Create Once**: Create annual holidays once with the correct month and day
2. **Multi-Day Holidays**: For holidays spanning multiple days (e.g., Christmas Eve + Day), set the start and end dates accordingly
3. **Regular Updates**: The system handles updates automatically, but you can run the command manually after initial setup
4. **Calendar Check**: View the Holidays Calendar to verify annual holidays appear correctly

## Troubleshooting

**Q: Annual holiday isn't showing in the calendar**
- A: Make sure "Repeat Annually" is enabled in the holiday settings
- A: Run `php artisan holidays:update-annual` to manually update

**Q: Holiday date is wrong after automatic update**
- A: Edit the holiday and set the correct month/day, then run the update command

**Q: How do I stop a holiday from repeating?**
- A: Edit the holiday and toggle OFF the "Repeat Annually" option

## Cron Setup (Production)

For the automatic updates to work in production, ensure your cron is configured:

### Linux/Mac
```bash
* * * * * cd /path/to/smarthr && php artisan schedule:run >> /dev/null 2>&1
```

### Windows Task Scheduler
Create a task that runs every minute:
```
Program: C:\xampp\php\php.exe
Arguments: C:\xampp\htdocs\smarthr\artisan schedule:run
```

This ensures all scheduled tasks (including holiday updates) run automatically.
