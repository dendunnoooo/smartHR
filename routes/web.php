<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\AllowancesController;
use App\Http\Controllers\ConferenceController;
use App\Http\Controllers\DeductionsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Admin\AssetsController;
use App\Http\Controllers\Admin\ChatAppController;
use App\Http\Controllers\Admin\ClientsController;
use App\Http\Controllers\Admin\TicketsController;
use App\Http\Controllers\Admin\HolidaysController;
use App\Http\Controllers\Admin\PayrollsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\EmployeesController;
use App\Http\Controllers\Admin\FamilyInfoController;
use App\Http\Controllers\Admin\AttendancesController;
use App\Http\Controllers\Admin\DepartmentsController;
use App\Http\Controllers\Admin\DesignationsController;
use App\Http\Controllers\Admin\EmployeeDetailsController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveApprovalController;
use App\Http\Controllers\Admin\LeaveTypeController;

include __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::any('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('profile', [UserProfileController::class, 'index'])->name('profile');
    Route::get('profile/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [UserProfileController::class, 'update']);

    Route::group(['prefix' => 'apps'], function(){
        Route::get('chat/{contact?}', [ChatAppController::class, 'index'])->name('app.chat');
        Route::delete('delete-chat/{receiver}', [ChatAppController::class, 'destroy'])->name('chat.delete-conversation');
    });

    Route::resource('users', UsersController::class);
    Route::resource('employees', EmployeesController::class);
    Route::resource('clients', ClientsController::class);
    Route::get('client-list', [ClientsController::class, 'list'])->name('clients.list');
    Route::get('employee/personal-info/{employeeDetail}', [EmployeeDetailsController::class, 'personalInfo'])->name('employee.personal-info');
    Route::post('employee/personal-info/{employeeDetail}', [EmployeeDetailsController::class, 'updatePersonalInfo']);
    Route::get('employee/emergency-contacts/{employeeDetail}', [EmployeeDetailsController::class, 'emergencyContacts'])->name('employee.emergency-contacts');
    Route::post('employee/emergency-contacts/{employeeDetail}', [EmployeeDetailsController::class, 'updateEmergencyContacts']);
    Route::get('employee/experience/{employeeDetail}', [EmployeeDetailsController::class, 'workExperience'])->name('employee.experience');
    Route::post('employee/experience/{employeeDetail}', [EmployeeDetailsController::class, 'updateWorkExperience']);
    Route::delete('delete-experience/{experience}', [EmployeeDetailsController::class, 'deleteWorkExperience'])->name('employee.experience.delete');
    Route::get('employee/education/{employeeDetail}', [EmployeeDetailsController::class, 'education'])->name('employee.education');
    Route::post('employee/education/{employeeDetail}', [EmployeeDetailsController::class, 'updateEducation']);
    Route::delete('del-employee-education', [EmployeeDetailsController::class, 'deleteEducation'])->name('employee.education.delete');
    Route::post('employee-salary-setting/{employeeDetail}', [EmployeeDetailsController::class, 'salarySetting'])->name('employee.salary-setting');
    Route::group(['prefix' => 'payroll'], function(){
        Route::get('items',[PayrollsController::class, 'items'])->name('payroll.items'); 
        Route::resource('allowances', AllowancesController::class)->except(['show']);
        Route::resource('deductions', DeductionsController::class)->except(['show']);
        Route::post('payslips/bulk-generate', [PayrollsController::class, 'bulkGenerate'])->name('payslips.bulk-generate');
        Route::resource('payslips', PayrollsController::class);
    });

    Route::get('employees-list', [EmployeesController::class, 'list'])->name('employees.list');
    Route::resource('departments', DepartmentsController::class)->except(['show']);
    Route::resource('designations', DesignationsController::class)->except(['show']);
    Route::resource('schedules', \App\Http\Controllers\Admin\SchedulesController::class)->except(['show']);
    Route::resource('holidays', HolidaysController::class);
    Route::get('holidays-calendar', [HolidaysController::class, 'calendar'])->name('holidays.calendar');
    Route::resource('family-information', FamilyInfoController::class);
    Route::resource('assets', AssetsController::class);
    Route::get('backups', fn() => view('pages.backups',[ 'pageTitle' => __('Backups')]))->name('backups.index');
    Route::get('attendance', [AttendancesController::class, 'index'])->name('attendances.index');
    Route::get('attendance-details/{attendance}', [AttendancesController::class, 'attendanceDetails'])->name('attendance.details');
    Route::resource('tickets', TicketsController::class);
    Route::get('assigned-tickets', [TicketsController::class, 'assignedTickets'])->name('assigned-tickets');
    Route::post('assign-ticket', [TicketsController::class, 'assignUser'])->name('ticket.assign-user');
    // Close ticket (marks ticket as closed)
    Route::post('tickets/{ticket}/close', [TicketsController::class, 'close'])->name('tickets.close');

    // Leaves (employee)
    Route::get('leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('leaves/create', [LeaveController::class, 'create'])->name('leaves.create');
    Route::post('leaves', [LeaveController::class, 'store'])->name('leaves.store');
    Route::get('leaves/{leave}', [LeaveController::class, 'show'])->name('leaves.show');
    Route::post('leaves/{leave}/cancel', [LeaveController::class, 'cancel'])->name('leaves.cancel');

    // Leave approvals (admin/super)
    Route::post('leaves/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leaves.approve');
    Route::post('leaves/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leaves.reject');

    // Permanent delete for leaves
    Route::delete('leaves/{leave}', [\App\Http\Controllers\LeaveController::class, 'destroy'])->name('leaves.destroy');

    // Leave attachments download
    Route::get('leaves/attachments/{attachment}', [\App\Http\Controllers\LeaveController::class, 'downloadAttachment'])->name('leaves.attachments.download');

    // Admin leave types management
    Route::resource('leave-types', LeaveTypeController::class);

    // Reports
    Route::get('reports/leaves', [\App\Http\Controllers\Reports\LeaveReportController::class, 'index'])->name('reports.leaves.index');
    Route::post('reports/leaves/export', [\App\Http\Controllers\Reports\LeaveReportController::class, 'export'])->name('reports.leaves.export');

    // Notifications: mark as read and redirect
    Route::get('notifications/{id}/read', [\App\Http\Controllers\NotificationsController::class, 'read'])->name('notifications.read');

    Route::get('app-logs', fn() => redirect()->to('log-viewer'))->name('app.logs');

    //settings
    Route::prefix('settings')->group(function () {
        Route::get('company', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');

        Route::get('locale', [SettingsController::class, 'locale'])->name('settings.locale');
        Route::post('locale', [SettingsController::class, 'updateLocale'])->name('settings.locale.update');
        Route::get('theme', [SettingsController::class, 'theme'])->name('settings.theme');
        Route::post('theme', [SettingsController::class, 'updateTheme'])->name('settings.theme.update');
        Route::get('invoice', [SettingsController::class, 'invoice'])->name('settings.invoice');
        Route::post('invoice', [SettingsController::class, 'updateInvoice'])->name('settings.invoice.update');
        Route::get('salary', [SettingsController::class, 'salary'])->name('settings.salary');
    // Leave credits management (Admins)
    Route::get('leave-tokens', [\App\Http\Controllers\Admin\LeaveTokensController::class, 'index'])->name('settings.leave_tokens');
    Route::post('leave-tokens', [\App\Http\Controllers\Admin\LeaveTokensController::class, 'update'])->name('settings.leave_tokens.update');
        // JSON endpoint to fetch tokens for a user (used by the leave form)
        Route::get('users/{id}/leave-tokens', [\App\Http\Controllers\Admin\LeaveTokensController::class, 'tokens'])->name('settings.users.leave_tokens');
    
    // Monthly attendance tokens
    Route::get('monthly-tokens', [\App\Http\Controllers\Admin\MonthlyTokenController::class, 'index'])->name('monthly-tokens.index');
    Route::post('monthly-tokens/convert', [\App\Http\Controllers\Admin\MonthlyTokenController::class, 'convert'])->name('monthly-tokens.convert');
    Route::post('monthly-tokens/{id}/approve', [\App\Http\Controllers\Admin\MonthlyTokenController::class, 'approve'])->name('monthly-tokens.approve');
    Route::post('monthly-tokens/{id}/reject', [\App\Http\Controllers\Admin\MonthlyTokenController::class, 'reject'])->name('monthly-tokens.reject');
    Route::get('monthly-tokens/settings', [\App\Http\Controllers\Admin\MonthlyTokenController::class, 'settings'])->name('monthly-tokens.settings');
    Route::post('monthly-tokens/settings', [\App\Http\Controllers\Admin\MonthlyTokenController::class, 'updateSettings'])->name('monthly-tokens.settings.update');
    
        Route::post('salary', [SettingsController::class, 'updateSalarySettings'])->name('settings.salary.update');
        Route::get('mail', [SettingsController::class, 'email'])->name('settings.mail');
        Route::post('mail', [SettingsController::class, 'updateEmail'])->name('settings.mail.update');
    });
});
