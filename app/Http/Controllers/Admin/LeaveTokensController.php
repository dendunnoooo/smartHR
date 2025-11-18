<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class LeaveTokensController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Return JSON tokens for a given user id.
     */
    public function tokens($userId)
    {
        $user = Auth::user();
        // allow admins or the same user to view tokens
        $isAdmin = method_exists($user, 'hasRole') && ($user->hasRole('Admin') || $user->hasRole('Super Admin') || stripos(implode(',', $user->getRoleNames()->toArray()), 'admin') !== false);
        if (!$isAdmin && $user->id !== intval($userId)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        if (!Schema::hasColumn('users', 'leave_tokens')) {
            return response()->json(['tokens' => null]);
        }

        $target = User::find($userId);
        if (!$target) {
            return response()->json(['tokens' => null]);
        }

        return response()->json(['tokens' => intval($target->leave_tokens ?? 0)]);
    }

    public function index()
    {
        $user = Auth::user();
        if (!method_exists($user, 'hasRole') || !($user->hasRole('Admin') || $user->hasRole('Super Admin') || stripos(implode(',', $user->getRoleNames()->toArray()), 'admin') !== false)) {
            abort(403);
        }

        // only include users that have an "employee" role (case-insensitive)
        // fall back to an empty collection if roles relationship isn't available
        try {
            $employees = User::whereHas('roles', function($q){
                $q->whereRaw('LOWER(name) LIKE ?', ['%employee%']);
            })->orderBy('firstname')->get();
        } catch (\Exception $e) {
            // if roles relationship is not defined, return an empty collection to avoid showing non-employees
            $employees = collect();
        }

        // Ensure a specific system employee account is always included in the list if it exists
        try {
            $special = User::where('email', 'employee@smarthr.com')->first();
            if ($special && !$employees->contains('id', $special->id)) {
                // append to the collection so it appears in the select
                $employees->push($special);
            }
        } catch (\Exception $e) {
            // ignore lookup errors
        }

        return view('settings.leave_tokens', compact('employees'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!method_exists($user, 'hasRole') || !($user->hasRole('Admin') || $user->hasRole('Super Admin') || stripos(implode(',', $user->getRoleNames()->toArray()), 'admin') !== false)) {
            abort(403);
        }

        // If the database column hasn't been created yet, give a helpful message instead of throwing a SQL error
        if (!Schema::hasColumn('users', 'leave_tokens')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => __('Database column "leave_credits" not found. Run your migrations (php artisan migrate).')], 422);
            }
            return redirect()->route('settings.leave_tokens')->with('error', __('Database column "leave_credits" not found. Run your migrations (php artisan migrate).'));
        }

        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'leave_tokens' => 'required|integer|min:0|max:1000',
        ]);

        $u = User::find($data['user_id']);
        $u->leave_tokens = intval($data['leave_tokens']);
        $u->save();

        return redirect()->route('settings.leave_tokens')->with('success', __('Leave credits updated'));
    }
}
