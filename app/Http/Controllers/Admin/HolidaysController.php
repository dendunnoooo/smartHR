<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\HolidayDataTable;
use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidaysController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(HolidayDataTable $dataTable)
    {
        $pageTitle = __("Holidays");
        return $dataTable->render('pages.holidays.index',compact(
            'pageTitle',
        ));
    }

    public function calendar(){
        $pageTitle = __("Holidays Calendar");
        $currentYear = now()->year;
        
        $events = Holiday::get()->map(function(Holiday $holiday) use ($currentYear) {
            $startDate = \Carbon\Carbon::parse($holiday->startDate);
            $endDate = \Carbon\Carbon::parse($holiday->endDate);
            
            // If it's an annual holiday and from a past year, show it in current year
            if ($holiday->is_annual && $startDate->year < $currentYear) {
                $duration = $startDate->diffInDays($endDate);
                $startDate = \Carbon\Carbon::createFromDate($currentYear, $startDate->month, $startDate->day);
                $endDate = $startDate->copy()->addDays($duration);
            }
            
            return [
                'title' => $holiday->name . ($holiday->is_annual ? ' (Annual)' : ''),
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'className' => 'bg-'.(!empty($holiday->color) ? $holiday->color->value : 'primary'),
            ];
        });
        
        return view('pages.holidays.calendar',compact(
            'pageTitle','events'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.holidays.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'description' => 'nullable|max:255',
            'type' => 'nullable|string',
        ]);
        Holiday::create([
            'name' => $request->name,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'description' => $request->description,
            'is_annual' => !empty($request->is_annual) ? true: false,
            'color'  => $request->color,
            'type' => $request->type,
        ]);
        $notification = notify(__("Holiday has been created"));
        return back()->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        return view('pages.holidays.edit',compact(
            'holiday'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'description' => 'nullable|max:255',
            'type' => 'nullable|string',
        ]);
        $holiday->update([
            'name' => $request->name,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'description' => $request->description,
            'is_annual' => !empty($request->is_annual) ? true: false,
            'color'  => $request->color,
            'type' => $request->type,
        ]);
        $notification = notify(__("Holiday has been updated"));
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        $notification = notify(__("Holiday has been deleted"));
        return back()->with($notification);
    }
}
