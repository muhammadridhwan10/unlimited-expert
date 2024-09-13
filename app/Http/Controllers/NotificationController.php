<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function markAsRead(Request $request)
    {
        $notificationIds = $request->input('notification_ids', []);
        Notification::whereIn('id', $notificationIds)->update(['is_read' => true]);

        return redirect()->back()->with('success', __('Notifications marked as read.'));
    }
}
