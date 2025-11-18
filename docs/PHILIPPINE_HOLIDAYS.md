# Philippine Holidays 2025

## Overview
The system has been pre-populated with official Philippine holidays for 2025, categorized as **Regular Holidays** and **Special Non-Working Holidays** according to Philippine law. All holidays are set to repeat annually.

## Regular Holidays (13)

Regular holidays provide employees with full pay even if no work is performed. If an employee works on a regular holiday, they receive 200% of their daily rate.

1. **New Year's Day** - January 1
2. **Maundy Thursday** - March 28, 2025 *(movable)*
3. **Good Friday** - March 29, 2025 *(movable)*
4. **Araw ng Kagitingan (Day of Valor)** - April 9
5. **Labor Day** - May 1
6. **Independence Day** - June 12
7. **Eid al-Adha (Feast of Sacrifice)** - June 7, 2025 *(movable)*
8. **Ninoy Aquino Day** - August 21
9. **National Heroes Day** - August 25 *(last Monday of August)*
10. **All Saints' Day** - November 1
11. **Bonifacio Day** - November 30
12. **Christmas Day** - December 25
13. **Rizal Day** - December 30

## Special Non-Working Holidays (8)

Special non-working holidays follow the "no work, no pay" principle unless there is a company policy or practice of paying wages on these days. If an employee works, they receive 130% of their daily rate.

1. **Chinese New Year** - January 29, 2025 *(movable)*
2. **EDSA People Power Revolution Anniversary** - February 25
3. **Black Saturday** - March 30, 2025 *(movable)*
4. **Eid al-Fitr (End of Ramadan)** - March 31, 2025 *(movable)*
5. **All Souls' Day** - November 2
6. **Feast of the Immaculate Conception of Mary** - December 8
7. **Christmas Eve** - December 24
8. **Last Day of the Year (New Year's Eve)** - December 31

## Movable Holidays

Some holidays have dates that change each year based on lunar calendars or other factors:

- **Maundy Thursday & Good Friday** - Based on Easter (lunar calendar)
- **Black Saturday** - Day after Good Friday
- **Chinese New Year** - Based on Chinese lunar calendar
- **Eid al-Fitr** - Based on Islamic lunar calendar (end of Ramadan)
- **Eid al-Adha** - Based on Islamic lunar calendar

### Updating Movable Holidays

For holidays with movable dates:
1. Check official government announcements at the start of each year
2. Edit the holiday in the system
3. Update the start and end dates to match the current year

The system's automatic annual update will maintain the month/day, so manual updates are needed for these holidays.

## How to Use in Payroll

### Regular Holiday Pay Calculation
- **No work**: 100% pay
- **Work performed**: 200% of daily rate for the first 8 hours
- **Overtime**: Additional 30% of hourly rate

### Special Non-Working Holiday Pay Calculation
- **No work**: No pay (unless company policy provides otherwise)
- **Work performed**: 130% of daily rate for the first 8 hours
- **Overtime**: Additional 30% of hourly rate

## Managing Holidays

### Viewing Holidays
- Navigate to **Holidays** page
- View as list or calendar
- Holidays show badges:
  - **Annual** (blue) - Repeats every year
  - **Regular** (red) - Regular holiday
  - **Special** (yellow) - Special non-working holiday

### Adding New Holidays
1. Click **Add Holiday**
2. Fill in:
   - Name
   - Start Date
   - End Date (same as start for single-day holidays)
   - Type: Regular Holiday or Special Non-Working Holiday
   - Color (for calendar display)
   - Description
   - Toggle "Repeat Annually" if applicable
3. Click Submit

### Editing Holidays
1. Click the edit icon next to any holiday
2. Modify the details
3. Save changes

### Automatic Updates
The system automatically updates all annual holidays to the current year on January 1st. For movable holidays, manual updates are recommended based on official government proclamations.

## Seeder Command

To re-populate or reset the Philippine holidays:

```bash
php artisan db:seed --class=PhilippineHolidaysSeeder
```

This command uses `updateOrCreate`, so it's safe to run multiple times without creating duplicates.

## References

Holiday classifications are based on:
- Republic Act No. 9492 (Holiday Economics Act)
- Presidential Proclamations
- Department of Labor and Employment (DOLE) advisories

## Notes

1. The President may declare additional special non-working days throughout the year
2. Local government units may declare additional local holidays
3. Companies may observe additional company-specific holidays
4. Always verify with official government sources for the most current holiday schedule

---

**Last Updated**: November 2025
**Total Holidays**: 21 (13 Regular + 8 Special Non-Working)
