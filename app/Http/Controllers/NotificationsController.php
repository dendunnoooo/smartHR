<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{
    /**
     * Mark a notification as read and redirect to its URL if present.
     */
    public function read($id)
    {
        $user = Auth::user();
        if(!$user) return redirect()->back();

    $notification = DatabaseNotification::where('id', $id)->where('notifiable_id', $user->id)->first();
    if(!$notification) return redirect()->back();

    $notification->markAsRead();
    $url = $notification->data['url'] ?? null;
    return $url ? redirect($url) : redirect()->back();
    }
}
