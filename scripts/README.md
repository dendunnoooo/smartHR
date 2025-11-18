# Scripts Directory

This directory contains utility and migration scripts for the SmartHR application.

## Directory Structure

### `/migrations`
Scripts for one-time database migrations and role updates:
- `change_admin1_role.php` - Change Admin1 role
- `change_to_admin.php` - Convert user to Admin role
- `change_to_hr_admin.php` - Convert user to HR Admin role
- `rename_hr_admin_to_admin.php` - Rename HR Admin role to Admin
- `rename_super_admin_to_hr_admin.php` - Rename Super Admin to HR Admin
- `initialize_all_salary_settings.php` - Initialize salary settings for all users
- `update_late_settings.php` - Update late/attendance settings

### `/utils`
Utility scripts for testing, data generation, and system checks:

**Attendance:**
- `create_sample_attendance.php` - Generate sample attendance records
- `delete_attendance.php` - Delete attendance records
- `assign_schedules.php` - Assign schedules to employees

**Leave Tokens:**
- `grant_tokens.php` - Grant leave tokens to specific users
- `grant_monthly_token.php` - Grant monthly leave tokens

**Testing & Verification:**
- `check_notifications.php` - Check notification system
- `check_salary_settings.php` - Verify salary settings
- `check_token_sync.php` - Check token synchronization
- `check_user_role.php` - Verify user roles
- `test_conversion_notifications.php` - Test notification conversions
- `info.php` - Display PHP info (phpinfo)

## Usage

Run scripts from the project root:

```bash
php scripts/utils/grant_tokens.php
php scripts/migrations/initialize_all_salary_settings.php
```

## Important Notes

- **Migration scripts** should only be run once during setup or updates
- **Utility scripts** can be run multiple times as needed
- Always backup your database before running migration scripts
- Test scripts in a development environment first
