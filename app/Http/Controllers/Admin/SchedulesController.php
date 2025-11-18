<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class SchedulesController extends Controller
{
    public function index()
    {
        $pageTitle = __('Schedules');
        $schedules = Schedule::withCount('users')->get();
        
        return view('pages.schedules.index', compact('pageTitle', 'schedules'));
    }

    public function create()
    {
        $pageTitle = __('Create Schedule');
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        return view('pages.schedules.create', compact('pageTitle', 'days'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'work_hours' => 'required|integer|min:1|max:24',
            'days' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['days'] = json_encode($request->days ?? []);
        $validated['is_active'] = $request->has('is_active');

        Schedule::create($validated);

        return redirect()->route('schedules.index')->with(notify(__('Schedule created successfully'), 'success'));
    }

    public function edit(Schedule $schedule)
    {
        $pageTitle = __('Edit Schedule');
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        return view('pages.schedules.edit', compact('pageTitle', 'schedule', 'days'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'work_hours' => 'required|integer|min:1|max:24',
            'days' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'rotation_day' => 'nullable|integer|min:1|max:31',
            'next_schedule_id' => 'nullable|exists:schedules,id',
        ]);

        $validated['days'] = json_encode($request->days ?? []);
        $validated['is_active'] = $request->has('is_active');

        $schedule->update($validated);

        return redirect()->route('schedules.index')->with(notify(__('Schedule updated successfully'), 'success'));
    }

    public function destroy(Schedule $schedule)
    {
        // Check if schedule is assigned to users
        if ($schedule->users()->count() > 0) {
            return back()->with(notify(__('Cannot delete schedule that is assigned to employees'), 'error'));
        }

        $schedule->delete();

        return redirect()->route('schedules.index')->with(notify(__('Schedule deleted successfully'), 'success'));
    }
}
