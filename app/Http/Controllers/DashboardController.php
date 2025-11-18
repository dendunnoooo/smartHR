<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Enums\UserType;
use App\Helpers\AppMenu;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Modules\Sales\Models\Expense;
use Modules\Sales\Models\Invoice;
use LaravelLang\LocaleList\Locale;
use Modules\Sales\Models\Estimate;
use Modules\Accounting\Models\Budget;
use App\Http\Controllers\BaseController;

class DashboardController extends BaseController
{

    public function index()
    {
        $this->data['pageTitle'] = __('Dashboard');
        
        // System Admin Dashboard
        if(auth()->user()->type === UserType::SYSTEM_ADMIN)
        {
            $this->data['totalUsers'] = User::count();
            $this->data['totalEmployees'] = User::where('type', UserType::EMPLOYEE)->count();
            $this->data['totalDepartments'] = \App\Models\Department::count();
            $this->data['totalDesignations'] = \App\Models\Designation::count();
            return view('pages.system-admin.dashboard', $this->data);
        }
        
        // Employee Dashboard
        if(auth()->user()->type === UserType::EMPLOYEE)
        {
            // compute latest payslip url for the logged in employee
            try{
                $user = auth()->user();
                if($user && !empty($user->employeeDetail->id) && class_exists('\\App\\Models\\Payslip')){
                    $latest = \App\Models\Payslip::where('employee_detail_id', $user->employeeDetail->id)->latest('id')->first();
                    $this->data['payslipUrl'] = $latest ? route('payslips.show', ['payslip' => Crypt::encrypt($latest->id)]) : route('payslips.index');
                }else{
                    $this->data['payslipUrl'] = route('payslips.index');
                }
            }catch(\Exception $e){
                $this->data['payslipUrl'] = route('payslips.index');
            }
            return view('pages.employees.dashboard',$this->data);
        }
        $projects = null;
        if(!empty(module('Project')) && module('Project')->isEnabled()){
            $projects = \Modules\Project\Models\Project::get();
            $recentProjects = \Modules\Project\Models\Project::whereMonth('created_at', Carbon::today())->get();
        }
        $clients = User::where('type', UserType::CLIENT)->get();
        $thisMonthClients = User::where('type', UserType::CLIENT)->whereMonth('created_at', Carbon::today())->get();
        $employees = User::where('type', UserType::EMPLOYEE)->get();
        $tickets = Ticket::get();
    // Tickets assigned to the current user (for "My Assigned Tickets" in main dashboard)
    $myAssignedTickets = Ticket::where('user_id', auth()->id())->get();

        if(module('Sales') && module('Sales')->isEnabled()){

            // If Payslip model exists prefer aggregating payslip net_pay as the "expenses" (cash paid out)
            if (class_exists(\App\Models\Payslip::class)) {
                // Use payslip_date when present
                $this->data['thisMonthExpenses'] = \App\Models\Payslip::whereMonth('payslip_date', Carbon::now())->sum('net_pay');
                $this->data['prevMonthExpenses'] = \App\Models\Payslip::whereMonth('payslip_date', Carbon::now()->subMonth())->sum('net_pay');

                $month = 1;
                $expense_collection = collect();
                $budget_collection = collect();
                $invoice_collection = collect();
                $estimates_collection = collect();
                while ($month <= 12) {
                    $sum = \App\Models\Payslip::whereMonth('payslip_date', $month)->sum('net_pay');
                    $count = \App\Models\Payslip::whereMonth('payslip_date', $month)->count();
                    $expense_collection->push(collect(['sum' => $sum, 'count' => $count]));

                    $budget_collection->push(
                        Budget::whereMonth('created_at', $month)->get()
                    );
                    $invoice_collection->push(
                        Invoice::whereMonth('created_at', $month)->get()
                    );
                    $estimates_collection->push(
                        Estimate::whereMonth('created_at', $month)->get()
                    );
                    $month += 1;
                }

                $this->data['monthly_expense'] = $expense_collection;
                // Prepare chart-friendly array for legacy Expense collections
                $this->data['monthly_expense_chart'] = $expense_collection->map(function($item, $idx){
                    $monthIndex = $idx + 1;
                    $sum = 0;
                    $count = 0;
                    if ($item instanceof \Illuminate\Support\Collection) {
                        $sum = $item->sum('amount');
                        $count = $item->count();
                    }
                    return collect(['m' => \Carbon\Carbon::create(0,$monthIndex,1)->format('M'), 'sum' => $sum, 'count' => $count]);
                });
                    // Prepare a simple chart-friendly array of month labels and sums/counts
                    $this->data['monthly_expense_chart'] = $expense_collection->map(function($item, $idx){
                        $monthIndex = $idx + 1;
                        $sum = 0;
                        $count = 0;
                        if (is_array($item) || ($item instanceof \Illuminate\Support\Collection && ($item->has('sum') || array_key_exists('sum', $item->toArray())))) {
                            $sum = $item['sum'] ?? ($item->get('sum') ?? 0);
                            $count = $item['count'] ?? ($item->get('count') ?? 0);
                        } elseif ($item instanceof \Illuminate\Support\Collection) {
                            $sum = $item->sum('amount');
                            $count = $item->count();
                        }
                        return collect(['m' => \Carbon\Carbon::create(0,$monthIndex,1)->format('M'), 'sum' => $sum, 'count' => $count]);
                    });
                $this->data['budget_collection'] = $budget_collection;
                $this->data['invoice_collection'] = $invoice_collection;
                $this->data['estimates_collection'] = $estimates_collection;
                $this->data['invoices'] = Invoice::get();
            } else {
                // Regular Expense model behavior
                $this->data['thisMonthExpenses'] = Expense::whereMonth('created_at', Carbon::now())->sum('amount');
                $this->data['prevMonthExpenses'] = Expense::whereMonth('created_at', Carbon::now()->subMonth())->sum('amount');
                $this->data['thisMonthEstimates'] = Estimate::whereMonth('created_at', Carbon::now())->sum('grand_total');
                $this->data['prevMonthEstimates'] = Estimate::whereMonth('created_at', Carbon::now()->subMonth())->sum('grand_total');
                $this->data['thisMonthInvoices'] = Invoice::whereMonth('created_at', Carbon::now())->sum('grand_total');
                $this->data['prevMonthInvoices'] = Invoice::whereMonth('created_at', Carbon::now()->subMonth())->sum('grand_total');
                $this->data['invoices'] = Invoice::get();

                $month = 1;
                $expense_collection = collect();
                $budget_collection = collect();
                $invoice_collection = collect();
                $estimates_collection = collect();
                while ($month <= 12) {
                    $expense_collection->push(
                        Expense::whereMonth('created_at', $month)->get()
                    );
                    $budget_collection->push(
                        Budget::whereMonth('created_at', $month)->get()
                    );
                    $invoice_collection->push(
                        Invoice::whereMonth('created_at', $month)->get()
                    );
                    $estimates_collection->push(
                        Estimate::whereMonth('created_at', $month)->get()
                    );
                    $month += 1;
                }
                $this->data['monthly_expense'] = $expense_collection;
                $this->data['budget_collection'] = $budget_collection;
                $this->data['invoice_collection'] = $invoice_collection;
                $this->data['estimates_collection'] = $estimates_collection;
            }
        }

        $budgets = null;

        // Prepare JS-ready monthly chart data (simple array of {y,a,b})
        if (!empty($this->data['monthly_expense_chart'])) {
            $this->data['monthly_expense_chart_js'] = collect($this->data['monthly_expense_chart'])->map(function($e){
                return [
                    'y' => $e['m'] ?? ($e->get('m') ?? null),
                    'a' => $e['sum'] ?? ($e->get('sum') ?? 0),
                    'b' => $e['count'] ?? ($e->get('count') ?? 0),
                ];
            })->values()->all();
        } else {
            $this->data['monthly_expense_chart_js'] = [];
        }

        if(module('Accounting') && module('Accounting')->isEnabled()){
        
            $budgets = Budget::get(); 
        }

        
        //attendances
        $absentees = User::where('type', UserType::EMPLOYEE)->whereDoesntHave('attendances', function($query){
            return $query->whereDay('created_at', Carbon::today())->take(1);
        })->get();

        $this->data['absentees'] = $absentees;
        $this->data['thisMonthTotalEmployees'] = User::where('type', UserType::EMPLOYEE)->whereMonth('created_at', Carbon::now())->count() ?? 0;
    $this->data['prevMonthTotalEmployees'] = User::where('type', UserType::EMPLOYEE)->whereMonth('created_at', Carbon::now()->subMonth())->count() ?? 0;
        $this->data['clients'] = (!empty($clients) && $clients->count() > 0) ? $clients: null;
        $this->data['thisMonthClients'] = $thisMonthClients;
        $this->data['employees'] = (!empty($employees) && $employees->count() > 0) ? $employees: null;
        $this->data['tickets'] = (!empty($tickets) && $tickets->count() > 0) ? $tickets: null;
    $this->data['myAssignedTickets'] = (!empty($myAssignedTickets) && $myAssignedTickets->count() > 0) ? $myAssignedTickets : null;
        $this->data['projects'] = $projects;
        $this->data['recentProjects'] = $recentProjects;
        return view('pages.dashboard', $this->data);
    }
}
