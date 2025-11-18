<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewLeaveSubmitted;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Leave::with(['user','leaveType'])->latest();

        // Determine if this user should see all leave requests (admin-like roles)
        $isAdminView = false;
        if ($user) {
            if (method_exists($user, 'hasRole')) {
                // common role names we expect
                $adminNames = ['admin', 'administrator', 'super admin', 'super-admin', 'superadmin'];
                foreach ($adminNames as $r) {
                    if ($user->hasRole($r)) {
                        $isAdminView = true;
                        break;
                    }
                }
            }
            // fallback: check any assigned role names for the substring 'admin'
            if (!$isAdminView && method_exists($user, 'getRoleNames')) {
                $roles = $user->getRoleNames(); // returns a collection
                foreach ($roles as $roleName) {
                    if (stripos($roleName, 'admin') !== false) {
                        $isAdminView = true;
                        break;
                    }
                }
            }
        }

        if ($isAdminView) {
            $leaves = $query->get();
        } else {
            $leaves = $query->where('user_id', $user->id)->get();
        }

        return view('leaves.index', compact('leaves'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Try to load leave types if the model exists. Some environments may not have leave types seeded.
        $types = [];
        if (class_exists('\App\\Models\\LeaveType')) {
            $types = \App\Models\LeaveType::orderBy('name')->get();
        }
        return view('leaves.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'leave_type_id' => 'required|integer|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'day_type' => 'required|in:full,half',
            'reason' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:5120',
        ]);
        // compute date_filed (today) and total_days server-side for safety
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        // reject impossible ranges where start is after end
        if ($start->gt($end)) {
            return back()
                ->withErrors(['start_date' => __('The start date must be before or equal to the end date.')])
                ->withInput();
        }

        $days = $start->diffInDays($end) + 1;
        $total = $data['day_type'] === 'half' ? ($days * 0.5) : $days;

        // disallow impossible/oversized requests. Respect per-leave-type `max_days` when present;
        // otherwise fall back to sensible defaults (uses mappings for common types).
        $leaveType = \App\Models\LeaveType::find($data['leave_type_id']);
        $maxAllowedDays = 31; // default fallback
        if ($leaveType && !is_null($leaveType->max_days)) {
            $maxAllowedDays = (int) $leaveType->max_days;
        } else {
            // fallback mapping for common leave types when DB value not set
            if ($leaveType) {
                $name = strtolower((string) ($leaveType->name ?? ''));
                $code = strtolower((string) ($leaveType->code ?? ''));
                if (strpos($name, 'matern') !== false || strpos($code, 'matern') !== false) {
                    $maxAllowedDays = 110;
                } elseif (strpos($name, 'patern') !== false || strpos($code, 'patern') !== false) {
                    $maxAllowedDays = 7;
                } elseif (strpos($name, 'sick') !== false || strpos($code, 'sick') !== false) {
                    // user requested 12-15 days; use the upper bound as maximum
                    $maxAllowedDays = 15;
                } elseif (strpos($name, 'annual') !== false || strpos($code, 'annual') !== false) {
                    $maxAllowedDays = 15;
                }
            }
        }

        if ($days > $maxAllowedDays) {
            return back()
                ->withErrors(['start_date' => __('Requested leave duration exceeds the allowed maximum of :days days for this leave type.', ['days' => $maxAllowedDays])])
                ->withInput();
        }

        $data['date_filed'] = Carbon::today()->toDateString();
        $data['total_days'] = $total;

        // Check leave credits: employees (non-admins) must have at least 1 credit to request a leave.
        $currentUser = Auth::user();
        $targetUserId = $currentUser->id;
        // If an admin is creating a leave for another user, allow specifying user_id in request
        if ($currentUser && method_exists($currentUser, 'hasRole') && ($currentUser->hasRole('Admin') || $currentUser->hasRole('Super Admin')) && $request->filled('user_id')) {
            $targetUserId = intval($request->input('user_id')) ?: $targetUserId;
        }

        $targetUser = \App\Models\User::find($targetUserId);
        $hasLeaveTokensColumn = Schema::hasColumn('users', 'leave_tokens');
        if ($targetUser) {
            // Only restrict non-admin employees and only if the column exists
            $isAdminLike = (method_exists($targetUser, 'hasRole') && ($targetUser->hasRole('Admin') || $targetUser->hasRole('Super Admin')));
            if (!$isAdminLike && $hasLeaveTokensColumn) {
                $tokens = intval($targetUser->leave_tokens ?? 0);
                if ($tokens <= 0) {
                    return back()->withErrors(['leave_tokens' => __('Insufficient leave credits. Contact your administrator.')])->withInput();
                }
            }
        }

        $leave = Leave::create(array_merge($data, ['user_id' => $targetUserId, 'status' => 'pending']));

            // Notify admins/super-admins that a new leave was submitted
            try {
                $admins = \App\Models\User::whereHas('roles', function($q){
                    $q->whereRaw('LOWER(name) LIKE ?', ['%admin%']);
                })->get();
                if ($admins && $admins->count() > 0) {
                    Notification::send($admins, new NewLeaveSubmitted($leave));
                }
            } catch (\Exception $e) {
                // fail silently: notification shouldn't block the leave creation
                \Log::warning('Failed to send leave notification: ' . $e->getMessage());
            }

        // Deduct one token for the target user if they are not admin-like
        if ($targetUser && !(method_exists($targetUser, 'hasRole') && ($targetUser->hasRole('Admin') || $targetUser->hasRole('Super Admin'))) && $hasLeaveTokensColumn) {
            // decrement only if the DB column exists
            $targetUser->decrement('leave_tokens', 1);
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('private/leaves');
                LeaveAttachment::create([
                    'leave_id' => $leave->id,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('leaves.index')->with('success', __('Leave application submitted'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave)
    {
        $this->authorize('view', $leave);
        return view('leaves.show', compact('leave'));
    }

    /**
     * Cancel the specified leave.
     */
    public function cancel(Request $request, Leave $leave)
    {
        $this->authorize('cancel', $leave);
        $leave->status = 'cancelled';
        $leave->save();
        return redirect()->route('leaves.index')->with('success', __('Leave cancelled'));
    }

    /**
     * Download an attachment for a leave.
     */
    public function downloadAttachment(LeaveAttachment $attachment)
    {
        $leave = $attachment->leave;
        $this->authorize('view', $leave);
        if (Storage::exists($attachment->path)) {
            return Storage::download($attachment->path, $attachment->original_name ?? 'attachment');
        }
        abort(404);
    }

    /**
     * Permanently delete the specified leave.
     */
    public function destroy(\Illuminate\Http\Request $request, Leave $leave)
    {
        // reuse cancel authorization rules for deletion (admins or owner when pending)
        $this->authorize('cancel', $leave);
        $leave->delete();
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('Leave request deleted')]);
        }
        return redirect()->route('leaves.index')->with('success', __('Leave request deleted'));
    }
}
