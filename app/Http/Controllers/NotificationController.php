<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the current user
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->get('show_entries', 10);
        
        // Get filter parameters
        $filters = [
            'type_filter' => $request->get('type_filter'),
            'status_filter' => $request->get('status_filter'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search'),
        ];

        // Build query for current user's notifications
        $notifications_query = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Apply filters
        $notifications_query = $this->applyFilters($notifications_query, $filters);
        
        $notifications = $notifications_query->paginate($perPage);

        // Get notification statistics
        $stats = $this->getNotificationStats(Auth::id());

        // Get notification types for filter dropdown
        $notificationTypes = Notification::select('type')
            ->where('user_id', Auth::id())
            ->distinct()
            ->pluck('type')
            ->map(function($type) {
                return [
                    'value' => $type,
                    'label' => $this->getTypeLabel($type)
                ];
            });

        // Append query parameters to pagination links
        $notifications->appends($request->query());

        return view('notifications.index', compact(
            'notifications',
            'stats',
            'notificationTypes'
        ));
    }

    /**
     * Mark specific notifications as read
     */
    public function markAsRead(Request $request)
    {
        $notificationIds = $request->input('notification_ids', []);
        
        // Ensure user can only mark their own notifications as read
        Notification::whereIn('id', $notificationIds)
            ->where('user_id', Auth::id())
            ->update(['is_read' => true, 'read_at' => now()]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Notifications marked as read.')
            ]);
        }

        return redirect()->back()->with('success', __('Notifications marked as read.'));
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => __('All notifications marked as read.')
        ]);
    }

    /**
     * Delete specific notifications
     */
    public function delete(Request $request)
    {
        $notificationIds = $request->input('notification_ids', []);
        
        // Ensure user can only delete their own notifications
        $deleted = Notification::whereIn('id', $notificationIds)
            ->where('user_id', Auth::id())
            ->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Notifications deleted successfully.'),
                'deleted_count' => $deleted
            ]);
        }

        return redirect()->back()->with('success', __('Notifications deleted successfully.'));
    }

    /**
     * Get unread notifications count for current user
     */
    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for header dropdown
     */
    public function getRecentNotifications(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'html' => $notification->toHtml(),
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'type' => $notification->type,
                    'type_label' => $this->getTypeLabel($notification->type)
                ];
            }),
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Show a specific notification and mark as read
     */
    public function show($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        // Mark as read if not already read
        if (!$notification->is_read) {
            $notification->update(['is_read' => true, 'read_at' => now()]);
        }

        // Redirect to the appropriate page based on notification type
        $redirectUrl = $this->getRedirectUrl($notification);

        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        return redirect()->route('notifications.index')
            ->with('info', __('Notification viewed.'));
    }

    /**
     * Apply filters to notification query
     */
    private function applyFilters($query, $filters)
    {
        // Type filter
        if (!empty($filters['type_filter'])) {
            $query->where('type', $filters['type_filter']);
        }

        // Status filter
        if (!empty($filters['status_filter'])) {
            if ($filters['status_filter'] === 'read') {
                $query->where('is_read', true);
            } elseif ($filters['status_filter'] === 'unread') {
                $query->where('is_read', false);
            }
        }

        // Date range filter
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay()
            ]);
        } elseif (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        } elseif (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        // Search filter
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where('data', 'LIKE', $searchTerm);
        }

        return $query;
    }

    /**
     * Get notification statistics
     */
    private function getNotificationStats($userId)
    {
        $total = Notification::where('user_id', $userId)->count();
        $unread = Notification::where('user_id', $userId)->where('is_read', false)->count();
        $read = $total - $unread;

        $today = Notification::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $thisWeek = Notification::where('user_id', $userId)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $read,
            'today' => $today,
            'this_week' => $thisWeek
        ];
    }

    /**
     * Get human-readable label for notification type
     */
    private function getTypeLabel($type)
    {
        $labels = [
            'create_project' => __('Project Created'),
            'create_overtime' => __('Overtime Created'),
            'create_medical_allowance' => __('Medical Allowance Created'),
            'comment_ticketing' => __('Ticket Comment'),
            'new_announcement' => __('Announcement'),
            'document_submitted' => __('Document Submitted'),
            'document_approved' => __('Document Approved'),
            'document_rejected' => __('Document Rejected'),
            'document_revision_required' => __('Document Revision Required'),
            'document_under_review' => __('Document Under Review'),
            'document_comment' => __('Document Comment'),
            'reimbursement_submitted' => __('Reimbursement Submitted'),
            'reimbursement_approved' => __('Reimbursement Approved'),
            'reimbursement_rejected' => __('Reimbursement Rejected'),
        ];

        return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Get redirect URL based on notification type and data
     */
    private function getRedirectUrl($notification)
    {
        $data = $notification->data;
        
        switch ($notification->type) {
            case 'create_project':
                if (isset($data->project_id)) {
                    return route('projects.show', \Crypt::encrypt($data->project_id));
                }
                break;
                
            case 'create_overtime':
                return route('overtime.index');
                
            case 'create_medical_allowance':
                return route('medical-allowance.index');
                
            case 'comment_ticketing':
                if (isset($data->id)) {
                    return route('support.reply', \Crypt::encrypt($data->id));
                }
                break;
                
            case 'document_submitted':
            case 'document_approved':
            case 'document_rejected':
            case 'document_revision_required':
            case 'document_under_review':
            case 'document_comment':
                if (isset($data->document_id) && isset($data->project_id)) {
                    return route('projects.document-review.show', [$data->project_id, $data->document_id]);
                }
                break;
                
            case 'reimbursement_submitted':
            case 'reimbursement_approved':
            case 'reimbursement_rejected':
                return route('reimbursment-personal.index');
        }

        return null;
    }
}