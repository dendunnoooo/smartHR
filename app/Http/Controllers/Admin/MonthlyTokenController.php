<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyToken;
use App\Models\TokenConversion;
use App\Models\TokenSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TokenConversionRequested;

class MonthlyTokenController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Admin') || $user->hasRole('HR Admin');
        
        if ($isAdmin) {
            $conversions = TokenConversion::with(['user', 'approver'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            
            $employees = User::where('type', \App\Enums\UserType::EMPLOYEE)
                ->with('monthlyToken')
                ->get();
            
            return view('settings.monthly_tokens.index', compact('conversions', 'employees'));
        } else {
            $monthlyToken = MonthlyToken::firstOrCreate(
                ['user_id' => $user->id],
                ['tokens' => 0, 'earned_tokens' => 0, 'converted_tokens' => 0]
            );
            
            $conversions = TokenConversion::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $tokenToCash = TokenSetting::get('token_to_cash', 500);
            $tokenToCredits = TokenSetting::get('token_to_leave_credits', 2);
            
            return view('settings.monthly_tokens.employee', compact('monthlyToken', 'conversions', 'tokenToCash', 'tokenToCredits'));
        }
    }

    public function convert(Request $request)
    {
        $request->validate([
            'tokens' => 'required|integer|min:1',
            'conversion_type' => 'required|in:cash,leave_credits',
        ]);

        $user = Auth::user();
        $monthlyToken = MonthlyToken::where('user_id', $user->id)->first();

        if (!$monthlyToken || $monthlyToken->tokens < $request->tokens) {
            return back()->with('error', __('Insufficient tokens for conversion.'));
        }

        $tokenToCash = TokenSetting::get('token_to_cash', 500);
        $tokenToCredits = TokenSetting::get('token_to_leave_credits', 2);

        $conversion = new TokenConversion();
        $conversion->user_id = $user->id;
        $conversion->tokens_converted = $request->tokens;
        $conversion->conversion_type = $request->conversion_type;
        $conversion->converted_at = now();
        $conversion->status = 'pending';

        if ($request->conversion_type === 'cash') {
            $conversion->cash_amount = $request->tokens * $tokenToCash;
        } else {
            $conversion->leave_credits_added = $request->tokens * $tokenToCredits;
        }

        if ($monthlyToken->convertTokens($request->tokens, $request->conversion_type)) {
            $conversion->save();

            // Notify HR Staff and HR Head
            try {
                $hrUsers = User::role(['Admin', 'HR Admin'])->get();
                Notification::send($hrUsers, new TokenConversionRequested($conversion));
            } catch (\Exception $e) {
                // Silently fail if notification fails (e.g., mail server not configured)
                \Log::warning('Failed to send token conversion notification: ' . $e->getMessage());
            }

            return back()->with('success', __('Token conversion request submitted successfully!'));
        }

        return back()->with('error', __('Failed to convert tokens.'));
    }

    public function approve(Request $request, $id)
    {
        $conversion = TokenConversion::findOrFail($id);
        
        if ($conversion->status !== 'pending') {
            return back()->with('error', __('This request has already been processed.'));
        }

        $conversion->status = 'approved';
        $conversion->approved_by = Auth::id();
        $conversion->approved_at = now();
        $conversion->notes = $request->notes;
        $conversion->save();

        // If converting to leave credits, add them to user account
        if ($conversion->conversion_type === 'leave_credits') {
            $user = $conversion->user;
            $leaveToken = $user->leaveToken;
            
            if ($leaveToken) {
                $leaveToken->tokens += $conversion->leave_credits_added;
                $leaveToken->earned_tokens += $conversion->leave_credits_added;
                $leaveToken->save();
            }
        }

        // Notify the employee
        try {
            $conversion->user->notify(new \App\Notifications\TokenConversionApproved($conversion));
        } catch (\Exception $e) {
            \Log::warning('Failed to send approval notification: ' . $e->getMessage());
        }

        return back()->with('success', __('Conversion request approved successfully!'));
    }

    public function reject(Request $request, $id)
    {
        $conversion = TokenConversion::findOrFail($id);
        
        if ($conversion->status !== 'pending') {
            return back()->with('error', __('This request has already been processed.'));
        }

        $conversion->status = 'rejected';
        $conversion->approved_by = Auth::id();
        $conversion->approved_at = now();
        $conversion->notes = $request->notes;
        $conversion->save();

        // Refund tokens
        $monthlyToken = MonthlyToken::where('user_id', $conversion->user_id)->first();
        if ($monthlyToken) {
            $monthlyToken->tokens += $conversion->tokens_converted;
            $monthlyToken->converted_tokens -= $conversion->tokens_converted;
            $monthlyToken->save();
        }

        // Notify the employee
        try {
            $conversion->user->notify(new \App\Notifications\TokenConversionRejected($conversion));
        } catch (\Exception $e) {
            \Log::warning('Failed to send rejection notification: ' . $e->getMessage());
        }

        return back()->with('success', __('Conversion request rejected and tokens refunded.'));
    }

    public function settings()
    {
        $settings = TokenSetting::all();
        return view('settings.monthly_tokens.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'token_to_cash' => 'required|numeric|min:0',
            'token_to_leave_credits' => 'required|integer|min:1',
        ]);

        TokenSetting::set('token_to_cash', $request->token_to_cash);
        TokenSetting::set('token_to_leave_credits', $request->token_to_leave_credits);

        return back()->with('success', __('Token settings updated successfully!'));
    }
}
