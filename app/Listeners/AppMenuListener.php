<?php

namespace App\Listeners;

use App\Enums\UserType;
use App\Events\AppMenuEvent;
use Spatie\Menu\Laravel\Link;
use Spatie\Menu\Laravel\Menu;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\Menu\Laravel\Html;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AppMenuListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppMenuEvent $event): void
    {
    $menu = $event->menu;
    // compute current user and common flags once to avoid calling auth()->user() inside closures
    $user = Auth::user();
    $isEmployee = $user ? ($user->type === UserType::EMPLOYEE) : false;
    // use Gate facade for permission checks to avoid static analysis on User methods
    $canViewPayrollItems = Gate::any(['view-PayrollAllowances','view-PayrollDeductions']);

    $menu->html('<span>Main</span>',['class' => 'menu-title']);

        $menu->add(
            Link::toRoute('dashboard', '<i class="la la-dashboard"></i> <span> ' . __('Dashboard') . '</span>')->setActive(route_is('dashboard'))
        );
        $activeClass = route_is(["app.chat"]) ? "active" : "";
        $menu
            ->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-cube"></i><span> ' . __("Apps") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->add(
                        Link::toRoute('app.chat', __('Chat'))->addClass(route_is(['app.chat']) ? 'active' : '')
                        )
                    ->addParentClass('submenu')
            );
       
        if(Gate::any([
            'view-employees','view-attendances','view-departments','view-designations','view-holidays'
        ])){
            $menu->html('<span>Employees</span>', ['class' => 'menu-title']);
            $activeClass = route_is(['employees.index','employees.list','departments.index','designations.index','holidays.*']) ? "active" : "";
            $menu
                ->submenu(
                    Html::raw('<a href="#" class="' . $activeClass . '" class="noti-dot"><i class="la la-user"></i> <span> ' . __('Employees') . '</span><span class="menu-arrow"></span></a>'),
                    Menu::new()
                        ->addParentClass('submenu')
                        ->addIfCan('view-employees',Link::toRoute('employees.index', __('Employees'))->addClass(route_is(['employees.index','employees.list']) ? 'active' : ''))
                        ->addIfCan('view-attendances',Link::toRoute('attendances.index', __('Attendance'))->addClass(route_is(['attendances.index']) ? 'active' : ''))
                        ->addIfCan('view-departments',Link::toRoute('departments.index', __('Departments'))->addClass(route_is('departments.index') ? 'active' : ''))
                        ->addIfCan('view-designations',Link::toRoute('designations.index', __('Designations'))->addClass(route_is('designations.index') ? 'active' : ''))
                        ->add(Link::toRoute('schedules.index', __('Schedules'))->addClass(route_is('schedules.*') ? 'active' : ''))
                        ->addIfCan('view-holidays',Link::toRoute('holidays.index', __('Holidays'))->addClass(route_is('holidays.*') ? 'active' : ''))
                        ->addIf($isEmployee,
                            Link::toRoute('payslips.index', __('Payslip'))->addClass(route_is(['payslips.*']) ? 'active' : '')
                        )
                );
        }
        $menu->add(
            Link::toRoute('tickets.index', '<i class="la la-ticket"></i> <span>' . __('Tickets') . '</span>')->setActive(route_is('tickets.*'))
        );
        // Leaves - visible to all authenticated users (employees, admins, superadmins)
        // Build a submenu so we can show a Leave Credits entry for admins
        $leavesActive = route_is(['leaves.*']);
        // determine admin-like flag
        $canManageTokens = false;
        if ($user) {
            if (method_exists($user, 'hasRole') && ($user->hasRole('Admin') || $user->hasRole('Super Admin'))) {
                $canManageTokens = true;
            }
            if (!$canManageTokens && method_exists($user, 'getRoleNames')) {
                foreach ($user->getRoleNames() as $rn) {
                    if (stripos($rn, 'admin') !== false) { $canManageTokens = true; break; }
                }
            }
        }

        $menu->submenu(
            Html::raw('<a href="#" class="' . ($leavesActive ? 'active' : '') . '"><i class="la la-calendar"></i> <span> ' . __('Leaves') . '</span><span class="menu-arrow"></span></a>'),
            Menu::new()
                ->addParentClass('submenu')
                ->add(Link::toRoute('leaves.index', __('Open Leaves'))->addClass($leavesActive ? 'active' : ''))
                ->addIf($canManageTokens, Link::toRoute('settings.leave_tokens', __('Leave Credits')))
                ->add(Link::toRoute('monthly-tokens.index', __('Monthly Tokens'))->addClass(route_is('monthly-tokens.*') ? 'active' : ''))
        );
        // Add Attendance direct link for all users
        $menu->add(
            Link::toRoute('attendances.index', '<i class="la la-clock-o"></i> <span>' . __('Attendance') . '</span>')->setActive(route_is('attendances.*'))
        );
        // Add Payslip direct link for employee users
        $menu->addIf($isEmployee,
            Link::toRoute('payslips.index', '<i class="la la-file-text"></i> <span>' . __('Payslip') . '</span>')->setActive(route_is('payslips.*'))
        );
        // Show "My Assigned Tickets" for non-employee users (admins/users)
        if(!$isEmployee){
            $menu->add(
                Link::toRoute('assigned-tickets', '<i class="la la-ticket"></i> <span>' . __('My Assigned Tickets') . '</span>')->setActive(route_is('assigned-tickets'))
            );
        }
    // Only show the full Payroll menu for non-employee users who have payroll permissions
    if(!$isEmployee && Gate::any(['view-PayrollAllowances','view-PayrollDeductions','view-payrolls','view-payslips'])){
            $payrollActive = route_is(['payroll.*','payslips.*','allowances.*','deductions.*']);
            $menu
            ->submenu(
                Html::raw('<a href="#" class="' . $payrollActive . '"><i class="la la-money"></i><span> ' . __("Payroll") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->addIf($canViewPayrollItems,
                        Link::toRoute('payroll.items', __('Payroll Items'))->addClass(route_is(['payroll.items']) ? 'active' : '')
                    )
                    ->addIfCan("view-payslips",
                        Link::toRoute('payslips.index', __('Payslip'))->addClass(route_is(['payslips.*']) ? 'active' : '')
                    )
                    ->addParentClass('submenu')
            );
        }
        $menu->addIfCan(
            'view-users',
            Link::toRoute('users.index', '<i class="la la-user-plus"></i> <span>' . __('Users') . '</span>')->setActive(route_is('users.index'))
        );
        $menu->addIfCan(
            'view-backups',
            Link::toRoute('backups.index', '<i class="la la-cloud-upload"></i> <span>' . __('Backups') . '</span>')->setActive(route_is('backups.index'))
        );
        $menu->addIfCan(
            'view-settings',
            Link::toRoute('settings.index', '<i class="la la-cog"></i> <span>' . __('Settings') . '</span>')->setActive(route_is('settings.index'))
        );
        // Clients and Assets menu entries removed per UI customization request.

    }
}
