<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class LeaveApprovalController extends Controller
{
    public function approve(Request $request, Leave $leave)
    {
        logger()->info('LeaveApprovalController@approve called', ['leave_id' => $leave->id, 'user_id' => auth()->id()]);
        $this->authorize('approve', $leave);

        if ($leave->status === 'approved') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => __('Leave is already approved')], 200);
            }
            return back()->with('info', __('Leave is already approved'));
        }

        logger()->info('Approving leave', ['leave_id' => $leave->id]);
        $leave->status = 'approved';
        $leave->save();

        $approval = LeaveApproval::create([
            'leave_id' => $leave->id,
            'user_id' => Auth::id(),
            'action' => 'approved',
            'comment' => null,
        ]);
        logger()->info('LeaveApproval created', ['id' => $approval->id ?? null]);

        // Apply balances if service exists (best-effort)
        try {
            if (class_exists('\App\Services\LeaveBalanceService')) {
                app(\App\Services\LeaveBalanceService::class)->applyApprovedLeave($leave);
            }
        } catch (\Throwable $e) {
            // swallow - non-critical
            logger()->error('Leave approval balance apply failed: ' . $e->getMessage());
        }

        try {
            if (class_exists('\\App\\Notifications\\LeaveApprovedNotification')) {
                logger()->info('Sending LeaveApprovedNotification', ['leave_id' => $leave->id]);
                $leave->user->notify(new \App\Notifications\LeaveApprovedNotification($leave));
                logger()->info('LeaveApprovedNotification dispatched', ['leave_id' => $leave->id]);
            } else {
                logger()->warning('LeaveApprovedNotification class not found; skipping notify', ['leave_id' => $leave->id]);
            }
        } catch (\Throwable $e) {
            logger()->error('Failed to notify user about leave approval: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('Leave approved'), 'status' => 'approved']);
        }

        return back()->with('success', __('Leave approved'));
    }

    public function reject(Request $request, Leave $leave)
    {
        logger()->info('LeaveApprovalController@reject called', ['leave_id' => $leave->id, 'user_id' => auth()->id()]);
        $this->authorize('approve', $leave);

        if ($leave->status === 'rejected') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => __('Leave is already rejected')], 200);
            }
            return back()->with('info', __('Leave is already rejected'));
        }

        $comment = $request->input('comment');
        logger()->info('Rejecting leave', ['leave_id' => $leave->id, 'comment_present' => !empty($comment)]);

        $leave->status = 'rejected';
        $leave->save();

        $approval = LeaveApproval::create([
            'leave_id' => $leave->id,
            'user_id' => Auth::id(),
            'action' => 'rejected',
            'comment' => $comment,
        ]);
        logger()->info('LeaveApproval (rejected) created', ['id' => $approval->id ?? null]);

        // Refund one leave credit to the employee if the app deducted one at creation
        try {
            if (Schema::hasColumn('users', 'leave_tokens') && $leave->user) {
                $isAdminLike = method_exists($leave->user, 'hasRole') && ($leave->user->hasRole('Admin') || $leave->user->hasRole('Super Admin'));
                if (!$isAdminLike) {
                    // refund 1 token (mirror store() which decrements by 1)
                    $leave->user->increment('leave_tokens', 1);
                    logger()->info('Refunded 1 leave credit to user', ['user_id' => $leave->user->getKey(), 'leave_id' => $leave->id]);
                }
            }
        } catch (\Throwable $e) {
            logger()->error('Failed to refund leave credit: ' . $e->getMessage());
        }

        try {
            if (! class_exists('\\App\\Notifications\\LeaveRejectedNotification')) {
                logger()->warning('LeaveRejectedNotification class not found; skipping notify', ['leave_id' => $leave->id]);
            } else {
                logger()->info('Preparing LeaveRejectedNotification', ['leave_id' => $leave->id, 'queue_driver' => config('queue.default')]);

                $notification = new \App\Notifications\LeaveRejectedNotification($leave, $comment);

                // If the configured queue driver is "sync", queued jobs run synchronously and can block the request.
                // In local/dev without a worker we'll instead write the database notification row directly (fast).
                if (config('queue.default') === 'sync') {
                    logger()->info('Queue driver is sync — creating database notification directly to avoid blocking', ['leave_id' => $leave->id]);
                    try {
                        $data = [];
                        if (method_exists($notification, 'toDatabase')) {
                            $data = $notification->toDatabase($leave->user);
                        } elseif (method_exists($notification, 'toArray')) {
                            $data = $notification->toArray($leave->user);
                        } else {
                            $data = ['message' => __('Your leave request was rejected by :admin', ['admin' => auth()->user()->name ?? 'admin'])];
                        }

                        \DB::table('notifications')->insert([
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'type' => get_class($notification),
                            'notifiable_type' => get_class($leave->user),
                            'notifiable_id' => $leave->user->getKey(),
                            'data' => json_encode($data),
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        logger()->info('Database notification row created', ['leave_id' => $leave->id, 'user_id' => $leave->user->getKey()]);
                    } catch (\Throwable $e) {
                        logger()->error('Failed to write database notification directly: ' . $e->getMessage());
                    }
                } else {
                    // This will dispatch a queued job because the notification implements ShouldQueue
                    logger()->info('Dispatching notification via notify()', ['leave_id' => $leave->id]);
                    $leave->user->notify($notification);
                    logger()->info('LeaveRejectedNotification dispatched', ['leave_id' => $leave->id]);
                }
            }
        } catch (\Throwable $e) {
            logger()->error('Failed to notify user about leave rejection: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('Leave rejected'), 'status' => 'rejected']);
        }

        return back()->with('success', __('Leave rejected'));
    }
}
