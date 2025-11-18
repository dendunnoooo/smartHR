<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <nav class="greedys sidebar-horizantal">
                {{-- Horizontal menu goes here --}}
            </nav>
            {{-- Vertical menu starts here  --}}
            {!! renderAppMenu() !!}
            {{-- Vertical Menu ends here  --}}
        </div>
    </div>
</div>
<!-- Two Col Sidebar -->
<div class="two-col-bar" id="two-col-bar">
    <div class="sidebar sidebar-twocol">
        <div class="sidebar-left slimscroll">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link" id="v-pills-dashboard-tab" title="Dashboard" data-bs-toggle="pill"
                    href="#v-pills-dashboard" role="tab" aria-controls="v-pills-dashboard" aria-selected="true">
                    <span class="material-icons-outlined"> home </span>
                </a>
                <a class="nav-link" id="v-pills-apps-tab" title="Apps" data-bs-toggle="pill" href="#v-pills-apps"
                    role="tab" aria-controls="v-pills-apps" aria-selected="false">
                    <span class="material-icons-outlined"> dashboard </span>
                </a>
                <a class="nav-link" id="v-pills-employees-tab" title="Employees" data-bs-toggle="pill"
                    href="#v-pills-employees" role="tab" aria-controls="v-pills-employees" aria-selected="false">
                    <span class="material-icons-outlined"> people </span>
                </a>
                <a class="nav-link" id="v-pills-leaves-tab" title="Leaves" data-bs-toggle="pill"
                    href="#v-pills-leaves" role="tab" aria-controls="v-pills-leaves" aria-selected="false">
                    <span class="material-icons-outlined"> beach_access </span>
                </a>
                <a class="nav-link" id="v-pills-attendance-tab" title="Attendance" data-bs-toggle="pill"
                    href="#v-pills-attendance" role="tab" aria-controls="v-pills-attendance" aria-selected="false">
                    <span class="material-icons-outlined"> schedule </span>
                </a>
                <!-- Employee payroll tab for viewing payslips -->
                <a class="nav-link" id="v-pills-employee-payroll-tab" title="Payroll" data-bs-toggle="pill"
                    href="#v-pills-employee-payroll" role="tab" aria-controls="v-pills-employee-payroll" aria-selected="false">
                    <span class="material-icons-outlined"> receipt_long </span>
                </a>
                <a class="nav-link" id="v-pills-leads-tab" title="Leads" data-bs-toggle="pill" href="#v-pills-leads"
                    role="tab" aria-controls="v-pills-leads" aria-selected="false">
                    <span class="material-icons-outlined"> leaderboard </span>
                </a>
                <a class="nav-link" id="v-pills-tickets-tab" title="Tickets" data-bs-toggle="pill"
                    href="#v-pills-tickets" role="tab" aria-controls="v-pills-tickets" aria-selected="false">
                    <span class="material-icons-outlined">
                        confirmation_number
                    </span>
                </a>
                {{-- Accounting menu removed --}}
                @if(!(auth()->check() && auth()->user()->type === \App\Enums\UserType::EMPLOYEE))
                <a class="nav-link" id="v-pills-payroll-tab" title="Payroll" data-bs-toggle="pill"
                    href="#v-pills-payroll" role="tab" aria-controls="v-pills-payroll" aria-selected="false">
                    <span class="material-icons-outlined"> request_quote </span>
                </a>
                @endif
                <a class="nav-link" id="v-pills-policies-tab" title="Policies" data-bs-toggle="pill"
                    href="#v-pills-policies" role="tab" aria-controls="v-pills-policies" aria-selected="false">
                    <span class="material-icons-outlined"> verified_user </span>
                </a>
                <a class="nav-link" id="v-pills-reports-tab" title="Reports" data-bs-toggle="pill"
                    href="#v-pills-reports" role="tab" aria-controls="v-pills-reports" aria-selected="false">
                    <span class="material-icons-outlined">
                        report_gmailerrorred
                    </span>
                </a>
                <a class="nav-link" id="v-pills-performance-tab" title="Performance" data-bs-toggle="pill"
                    href="#v-pills-performance" role="tab" aria-controls="v-pills-performance"
                    aria-selected="false">
                    <span class="material-icons-outlined"> shutter_speed </span>
                </a>
                <a class="nav-link" id="v-pills-goals-tab" title="Goals" data-bs-toggle="pill"
                    href="#v-pills-goals" role="tab" aria-controls="v-pills-goals" aria-selected="false">
                    <span class="material-icons-outlined"> track_changes </span>
                </a>
                <a class="nav-link" id="v-pills-training-tab" title="Training" data-bs-toggle="pill"
                    href="#v-pills-training" role="tab" aria-controls="v-pills-training" aria-selected="false">
                    <span class="material-icons-outlined"> checklist_rtl </span>
                </a>
                <a class="nav-link" id="v-pills-promotion-tab" title="Promotions" data-bs-toggle="pill"
                    href="#v-pills-promotion" role="tab" aria-controls="v-pills-promotion"
                    aria-selected="false">
                    <span class="material-icons-outlined"> auto_graph </span>
                </a>
                <a class="nav-link" id="v-pills-resignation-tab" title="Resignation" data-bs-toggle="pill"
                    href="#v-pills-resignation" role="tab" aria-controls="v-pills-resignation"
                    aria-selected="false">
                    <span class="material-icons-outlined">
                        do_not_disturb_alt
                    </span>
                </a>
                <a class="nav-link" id="v-pills-termination-tab" title="Termination" data-bs-toggle="pill"
                    href="#v-pills-termination" role="tab" aria-controls="v-pills-termination"
                    aria-selected="false">
                    <span class="material-icons-outlined">
                        indeterminate_check_box
                    </span>
                </a>
                {{-- Clients, Projects, Sales, and Assets menu items removed --}}
                <a class="nav-link" id="v-pills-jobs-tab" title="Jobs" data-bs-toggle="pill" href="#v-pills-jobs"
                    role="tab" aria-controls="v-pills-jobs" aria-selected="false">
                    <span class="material-icons-outlined"> work_outline </span>
                </a>
                <a class="nav-link" id="v-pills-knowledgebase-tab" title="Knowledgebase" data-bs-toggle="pill"
                    href="#v-pills-knowledgebase" role="tab" aria-controls="v-pills-knowledgebase"
                    aria-selected="false">
                    <span class="material-icons-outlined"> school </span>
                </a>
                <a class="nav-link" id="v-pills-activities-tab" title="Activities" data-bs-toggle="pill"
                    href="#v-pills-activities" role="tab" aria-controls="v-pills-activities"
                    aria-selected="false">
                    <span class="material-icons-outlined"> toggle_off </span>
                </a>
                <a class="nav-link" id="v-pills-users-tab" title="Users" data-bs-toggle="pill"
                    href="#v-pills-users" role="tab" aria-controls="v-pills-users" aria-selected="false">
                    <span class="material-icons-outlined"> group_add </span>
                </a>
                @hasanyrole('System Admin|HR Admin')
                <a class="nav-link" id="v-pills-settings-tab" title="Settings" data-bs-toggle="pill"
                    href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">
                    <span class="material-icons-outlined"> settings </span>
                </a>
                @endhasanyrole
                <a class="nav-link" id="v-pills-profile-tab" title="Profile" data-bs-toggle="pill"
                    href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                    <span class="material-icons-outlined"> manage_accounts </span>
                </a>
                <a class="nav-link" id="v-pills-authentication-tab" title="Authentication" data-bs-toggle="pill"
                    href="#v-pills-authentication" role="tab" aria-controls="v-pills-authentication"
                    aria-selected="false">
                    <span class="material-icons-outlined">
                        perm_contact_calendar
                    </span>
                </a>
                <a class="nav-link" id="v-pills-errorpages-tab" title="Error Pages" data-bs-toggle="pill"
                    href="#v-pills-errorpages" role="tab" aria-controls="v-pills-errorpages"
                    aria-selected="false">
                    <span class="material-icons-outlined"> announcement </span>
                </a>
                <a class="nav-link" id="v-pills-subscriptions-tab" title="Subscriptions" data-bs-toggle="pill"
                    href="#v-pills-subscriptions" role="tab" aria-controls="v-pills-subscriptions"
                    aria-selected="false">
                    <span class="material-icons-outlined"> loyalty </span>
                </a>
                <a class="nav-link active" id="v-pills-pages-tab" title="Pages" data-bs-toggle="pill"
                    href="#v-pills-pages" role="tab" aria-controls="v-pills-pages" aria-selected="false">
                    <span class="material-icons-outlined"> layers </span>
                </a>
                
                <a class="nav-link" id="v-pills-forms-tab" title="Forms" data-bs-toggle="pill"
                    href="#v-pills-forms" role="tab" aria-controls="v-pills-forms" aria-selected="false">
                    <span class="material-icons-outlined"> view_day </span>
                </a>
                <a class="nav-link" id="v-pills-tables-tab" title="Tables" data-bs-toggle="pill"
                    href="#v-pills-tables" role="tab" aria-controls="v-pills-tables" aria-selected="false">
                    <span class="material-icons-outlined"> table_rows </span>
                </a>
                <a class="nav-link" id="v-pills-documentation-tab" title="Documentation" data-bs-toggle="pill"
                    href="#v-pills-documentation" role="tab" aria-controls="v-pills-documentation"
                    aria-selected="false">
                    <span class="material-icons-outlined"> description </span>
                </a>
                <a class="nav-link" id="v-pills-changelog-tab" title="Changelog" data-bs-toggle="pill"
                    href="#v-pills-changelog" role="tab" aria-controls="v-pills-changelog"
                    aria-selected="false">
                    <span class="material-icons-outlined"> sync_alt </span>
                </a>
                <a class="nav-link" id="v-pills-multilevel-tab" title="Multilevel" data-bs-toggle="pill"
                    href="#v-pills-multilevel" role="tab" aria-controls="v-pills-multilevel"
                    aria-selected="false">
                    <span class="material-icons-outlined"> library_add_check </span>
                </a>
            </div>
        </div>

        <div class="sidebar-right">
            <div class="tab-content" id="v-pills-tabContent">
                <!-- Attendance Pane -->
                <div class="tab-pane fade" id="v-pills-attendance" role="tabpanel" aria-labelledby="v-pills-attendance-tab">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">{{ __('Attendance') }}</h5>
                            <p class="card-text">{{ __('Track your attendance, clock in/out, and view attendance history') }}</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('attendances.index') }}" class="btn btn-primary">{{ __('View Attendance') }}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Payroll Pane -->
                <div class="tab-pane fade" id="v-pills-employee-payroll" role="tabpanel" aria-labelledby="v-pills-employee-payroll-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">{{ __('Payslips') }}</h5>
                        @php
                            $payslips = collect();
                            $employeeDetailId = null;
                            
                            try{
                                // try for authenticated employee
                                $authUser = \Illuminate\Support\Facades\Auth::user();
                                if($authUser && $authUser->type === \App\Enums\UserType::EMPLOYEE && !empty($authUser->employeeDetail->id)){
                                    $employeeDetailId = $authUser->employeeDetail->id;
                                }
                                // if we're viewing a particular employee's profile
                                if(isset($employee) && !empty($employee->id)){
                                    $employeeDetailId = $employee->id;
                                }
                                
                                // Get all payslips for this employee
                                if($employeeDetailId){
                                    $payslips = \App\Models\Payslip::where('employee_detail_id', $employeeDetailId)
                                        ->orderBy('payslip_date', 'desc')
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();
                                }
                            }catch(\Exception $e){
                                // fail silently
                            }
                        @endphp
                        
                        @if($payslips->isEmpty())
                            <p class="text-muted text-center">{{ __('No payslips found') }}</p>
                            <div class="text-center">
                                <a href="{{ route('payslips.index') }}" class="btn btn-sm btn-primary">{{ __('View All Payslips') }}</a>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($payslips as $payslip)
                                <a href="{{ route('payslips.show', ['payslip' => \Illuminate\Support\Facades\Crypt::encrypt($payslip->id)]) }}" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                @if($payslip->title)
                                                    {{ $payslip->title }}
                                                @else
                                                    {{ __('Payslip') }} {{ $payslip->ps_id }}
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar me-1"></i>{{ $payslip->payslip_date ? \Carbon\Carbon::parse($payslip->payslip_date)->format('M Y') : format_date($payslip->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">{{ $payslip->currency ?? '₱' }}{{ number_format($payslip->net_pay, 2) }}</span>
                                            @if($payslip->is_semi_monthly)
                                                <br><small class="text-muted">{{ __('Bi-Monthly') }}</small>
                                            @else
                                                <br><small class="text-muted">{{ ucfirst(str_replace('-', ' ', $payslip->type->value ?? $payslip->type)) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('payslips.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
                            </div>
                        @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- /Two Col Sidebar -->