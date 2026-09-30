<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(15);
        return view('user.notifications.index', compact('notifications'));
    }

    public function markRead(string $id)
    {
        $n = Auth::user()->notifications()->findOrFail($id);
        $n->markAsRead();
        return back()->with('success', 'Marked as read.');
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All marked as read.');
    }
}
