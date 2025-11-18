# System Administrator Account

## Overview
The System Administrator account provides full system-level access and management capabilities for the SmartHR platform. This is a special account type designed for IT administrators who manage the entire system infrastructure, user accounts, and system configurations.

## Account Details

**Default Credentials:**
- **Email:** `sysadmin@smarthr.com`
- **Username:** `sysadmin`
- **Password:** `SysAdmin@2025`
- **User Type:** `System Admin`

⚠️ **IMPORTANT:** Change the default password immediately after first login!

## Account Features

### 1. System Admin Dashboard
A dedicated control panel with comprehensive system overview:
- Total users count
- Employee statistics
- Department and designation counts
- System health monitoring
- Recent system activity logs

**Access:** Available immediately after login at `/dashboard`

### 2. Full System Access
System Admins have unrestricted access to:

#### User Management
- View, create, edit, and delete all user accounts
- Manage employee records
- Assign roles and permissions
- View user activity and login history

#### Organization Structure
- Manage departments
- Manage designations
- Configure organizational hierarchy
- Set up holiday calendars

#### Attendance System
- View all attendance records
- Generate attendance reports
- Manage leave tokens
- Configure attendance settings

#### Payroll Management
- Access all payslip records
- Configure salary settings
- Manage overtime/undertime calculations
- Set up allowances and deductions
- Generate payroll reports

#### System Configuration
- General settings
- Salary calculation settings
- Email configurations
- Backup management
- Module configurations

### 3. Permissions & Authorization
The System Admin has:
- Bypass all permission checks (superuser privileges)
- Access to all routes and controllers
- Ability to manage roles and permissions
- Full database access through the application

## Creating the System Admin Account

### Using Seeder (Recommended)
```bash
php artisan db:seed --class=SystemAdminSeeder
```

### Manual Creation
If you need to create manually:
```php
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Support\Facades\Hash;

$admin = User::create([
    'firstname' => 'System',
    'lastname' => 'Administrator',
    'email' => 'sysadmin@smarthr.com',
    'username' => 'sysadmin',
    'type' => UserType::SYSTEM_ADMIN,
    'password' => Hash::make('YourSecurePassword'),
    'is_active' => true,
]);

$role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'System Admin']);
$admin->assignRole($role);
```

## User Type Implementation

### Enum Value
Location: `app/Enums/UserType.php`
```php
enum UserType: string
{
    case SYSTEM_ADMIN = 'System Admin';
    case SUPERADMIN = 'Super Admin';
    case EMPLOYEE = 'Employee';
    case CLIENT = 'Client';
}
```

### Blade Directives
Use in views to show/hide content:

```blade
@systemadmin
    <!-- Content only visible to System Admins -->
    <div>System Admin Panel</div>
@endsystemadmin
```

### Authorization Gates
Location: `app/Providers/AppServiceProvider.php`
```php
Gate::before(function ($user, $ability) {
    if ($user->type === \App\Enums\UserType::SYSTEM_ADMIN) {
        return true; // Grant all permissions
    }
    return null;
});
```

## Dashboard Routing

The system automatically routes System Admins to their dedicated dashboard:

Location: `app/Http/Controllers/DashboardController.php`
```php
if(auth()->user()->type === UserType::SYSTEM_ADMIN) {
    // Load system admin dashboard with statistics
    return view('pages.system-admin.dashboard', $this->data);
}
```

## Security Features

### Access Control
- System Admin type is checked at multiple levels:
  1. User model type field
  2. Spatie role assignment
  3. Laravel Gate authorization
  4. Blade directive checks

### Authentication
- Standard Laravel authentication
- Session-based security
- Password hashing with bcrypt
- CSRF protection on all forms

### Best Practices
1. **Change default password immediately**
2. Use strong, unique password
3. Enable two-factor authentication (if available)
4. Regularly review system logs
5. Limit system admin accounts to essential personnel only
6. Don't use system admin for daily operations - create regular admin accounts

## Dashboard Components

### Statistics Cards
- **Total Users:** Count of all user accounts
- **Employees:** Active employee count
- **Departments:** Number of departments
- **System Health:** Operational status

### Management Sections
1. **User Management**
   - All Users list
   - Employee management
   - Roles & Permissions

2. **Organization Structure**
   - Departments
   - Designations
   - Holiday calendar

3. **System Settings**
   - General settings
   - Salary configurations
   - Backup management

4. **Attendance Management**
   - View records
   - Generate reports
   - Leave token management

5. **Payroll System**
   - Payslip management
   - Allowances & deductions
   - Payroll reports

### Quick Actions
Shortcut buttons for common tasks:
- Create New User
- Add New Employee
- Create Department
- Add Holiday

## Differences: System Admin vs Super Admin

| Feature | System Admin | Super Admin |
|---------|-------------|-------------|
| **Purpose** | System-level management | HR/Business operations |
| **Dashboard** | System control panel | Business dashboard |
| **Focus** | Technical administration | Business management |
| **Default Account** | Created manually | Created during setup |
| **Permissions** | Full system access | Full business access |
| **Typical User** | IT Administrator | HR Manager |

## Troubleshooting

### Cannot Login
1. Verify account exists: `php artisan tinker`
   ```php
   User::where('email', 'sysadmin@smarthr.com')->first()
   ```
2. Check if active: `is_active` should be `true`
3. Verify user type: `type` should be `SYSTEM_ADMIN`

### Access Denied
1. Clear application cache: `php artisan cache:clear`
2. Clear config cache: `php artisan config:clear`
3. Verify role assignment:
   ```php
   $user = User::where('email', 'sysadmin@smarthr.com')->first();
   $user->roles; // Should include "System Admin"
   ```

### Dashboard Not Loading
1. Clear view cache: `php artisan view:clear`
2. Check route: `php artisan route:list | grep dashboard`
3. Verify controller logic in `DashboardController.php`

## System Admin Tasks

### Daily Tasks
- Monitor system health
- Review user activity
- Check system logs
- Verify backups

### Weekly Tasks
- Review new user accounts
- Update system configurations
- Check attendance records
- Generate system reports

### Monthly Tasks
- User account audit
- Permission review
- System optimization
- Database maintenance
- Backup verification

## Related Files

### Controllers
- `app/Http/Controllers/DashboardController.php` - Dashboard routing
- `app/Http/Controllers/Admin/*` - Admin area controllers

### Views
- `resources/views/pages/system-admin/dashboard.blade.php` - System admin dashboard
- `resources/views/layouts/app.blade.php` - Main layout

### Models
- `app/Models/User.php` - User model with type
- `app/Enums/UserType.php` - User type enum

### Configuration
- `app/Providers/AppServiceProvider.php` - Authorization gates
- `config/auth.php` - Authentication config

### Database
- `database/seeders/SystemAdminSeeder.php` - Account seeder

## Support

For issues or questions about the System Admin account:
1. Check application logs: `storage/logs/laravel.log`
2. Review error messages in browser console
3. Verify PHP and database errors
4. Check Laravel version compatibility

## Version History
- **v1.0** (2025-11-15): Initial System Admin implementation
  - Created SYSTEM_ADMIN user type
  - Built dedicated dashboard
  - Implemented full system access
  - Added Blade directives
  - Created seeder and documentation
