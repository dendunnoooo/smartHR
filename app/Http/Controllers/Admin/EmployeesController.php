<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\EmployeeDataTable;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\User;
use App\Models\Payslip;
use Chatify\Facades\ChatifyMessenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pageTitle = __("Employees");
        $employees = User::where('type', UserType::EMPLOYEE)->get();
        return view('pages.employees.index', compact(
            'pageTitle',
            'employees'
        ));
    }

    /**
     * Display a listing of the resource.
     */
    public function list(EmployeeDataTable $dataTable)
    {
        $pageTitle = __("employees");
        return $dataTable->render('pages.employees.list', compact(
            'pageTitle',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::get();
        $designations = Designation::get();
        return view('pages.employees.create', compact(
            'departments',
            'designations'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'middlename' => 'nullable|string',
            'lastname' => 'required',
            'email' => ['required', 'email', 'unique:users,email,except,id', 'regex:/^[a-zA-Z0-9._%+-]+@smarthr\.com$/'],
            'password' => 'required|string|confirmed',
            'status' => 'required',
        ], [
            'email.regex' => 'Only @smarthr.com email addresses are allowed.'
        ]);
        $imageName = null;
        if ($request->hasFile('avatar')) {
            $imageName = time() . '.' . $request->avatar->extension();
            $request->avatar->move(public_path('storage/users'), $imageName);
        }
    $user = User::create([
            'type' => UserType::EMPLOYEE,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'username' => $request->username,
            'address' => $request->address,
            'country' => $request->country_name,
            'country_code' => $request->country_code,
            'dial_code' => $request->dial_code,
            'phone' => $request->phone,
            'avatar' => $imageName,
            'schedule_id' => $request->schedule_id,
            'created_by' => Auth::id(),
            'is_active' => !empty($request->status),
            'password' => Hash::make($request->password)
        ]);
        if (!empty($user)) {
            $user->assignRole(UserType::EMPLOYEE);
            $totalEmployees = User::where('type', UserType::EMPLOYEE)->where('is_active', true)->count();
            $empId = "EMP-" . pad_zeros(($totalEmployees + 1));
            EmployeeDetail::create([
                'emp_id' => $empId,
                'user_id' => $user->id,
                'department_id' => $request->department,
                'designation_id' => $request->designation,
            ]);
        }
        $notification = notify(__('Employee has been added'));
        return back()->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $employee)
    {
        $id = Crypt::decrypt($employee);
        $user = User::findOrFail($id);
        $employee = $user->employeeDetail;
        $pageTitle = __('Employee Profile');
        // compute latest payslip URL for this employee (if any)
        $payslipUrl = route('payslips.index');
        try{
            $latest = Payslip::where('employee_detail_id', $employee->id)->latest('id')->first();
            if($latest){
                $payslipUrl = route('payslips.show', ['payslip' => Crypt::encrypt($latest->id)]);
            }
        }catch(\Exception $e){
            // fallback to index
        }

        return view('pages.employees.show', compact(
            'employee',
            'user',
            'pageTitle',
            'payslipUrl'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $employee)
    {
        $userId = Crypt::decrypt($employee);
        $employee = User::findOrFail($userId);
        $departments = Department::get();
        $designations = Designation::get();
        return view('pages.employees.edit', compact(
            'departments',
            'designations',
            'employee'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $employee)
    {
        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => ['nullable', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@smarthr\.com$/'],
            'password' => 'nullable|string|confirmed',
            'status' => 'required',
        ], [
            'email.regex' => 'Only @smarthr.com email addresses are allowed.'
        ]);
        $user = $employee;
        $imageName = $user->avatar;
        if ($request->hasFile('avatar')) {
            $imageName = time() . '.' . $request->avatar->extension();
            $request->avatar->move(public_path('storage/users'), $imageName);
        }
        $user->update([
            'firstname' => $request->firstname ?? $user->firstname,
            'middlename' => $request->middlename ?? $user->middlename,
            'lastname' => $request->lastname ?? $user->lastname,
            'email' => $request->email ?? $user->email,
            'username' => $request->username ?? $user->username,
            'address' => $request->address ?? $user->address,
            'country' => $request->country_name ?? $user->country,
            'country_code' => $request->country_code ?? $user->country_code,
            'dial_code' => $request->dial_code ?? $user->dial_code,
            'phone' => $request->phone ?? $user->phone,
            'avatar' => $imageName,
            'schedule_id' => $request->schedule_id ?? $user->schedule_id,
            'is_active' => !empty($request->status) ?? $user->is_active,
            'password' => !empty($request->password) ? Hash::make($request->password) : $user->password
        ]);
        if (!empty($user)) {
            if(!$user->hasRole(UserType::EMPLOYEE)){
                $user->assignRole(UserType::EMPLOYEE);
            }
            $employeeDetails = $user->employeeDetail;
            if (!empty($employeeDetails) && empty($employeeDetails->emp_id)) {
                $totalEmployees = User::where('type', UserType::EMPLOYEE)->where('is_active', true)->count();
                $empId = "EMP-" . pad_zeros(($totalEmployees + 1));
            }
            EmployeeDetail::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'emp_id' => $empId ?? $employee->emp_id,
                'user_id' => $user->id,
                'department_id' => $request->department,
                'designation_id' => $request->designation,
            ]);
        }
        $notification = notify(__("Employee has been updated"));
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $employee)
    {
        $employee->delete();
        $notification = notify(__("Employee has been deleted"));
        return back()->with($notification);
    }
}
