<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $types = LeaveType::orderBy('name')->get();
        return view('admin.leave_types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.leave_types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'accrual_rate_per_month' => 'nullable|numeric',
            'requires_approval' => 'nullable|boolean',
            'max_days' => 'nullable|integer|min:0',
        ]);

        $data['requires_approval'] = !empty($request->requires_approval);

        LeaveType::create($data);

        return redirect()->route('leave-types.index')->with('success', __('Leave type created'));
    }

    public function edit(LeaveType $leaveType)
    {
        return view('admin.leave_types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'accrual_rate_per_month' => 'nullable|numeric',
            'requires_approval' => 'nullable|boolean',
            'max_days' => 'nullable|integer|min:0',
        ]);

        $data['requires_approval'] = !empty($request->requires_approval);

        $leaveType->update($data);

        return back()->with('success', __('Leave type updated'));
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();
        return back()->with('success', __('Leave type deleted'));
    }
}
