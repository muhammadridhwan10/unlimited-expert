<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Create notification for reimbursement submission
     */
    public function createReimbursementSubmissionNotification($reimbursement)
    {
        // Notify approver
        if ($reimbursement->approval) {
            $data = [
                'reimbursement_id' => $reimbursement->id,
                'employee_name' => $reimbursement->employee->name ?? 'Unknown',
                'amount' => $reimbursement->amount,
                'type' => $reimbursement->reimbursment_type,
                'date' => $reimbursement->date,
                'updated_by' => Auth::id()
            ];

            Notification::createNotification(
                $reimbursement->approval,
                'reimbursement_submitted',
                $data,
                Notification::PRIORITY_NORMAL
            );
        }
    }

    /**
     * Create notification for reimbursement approval/rejection
     */
    public function createReimbursementStatusNotification($reimbursement, $status)
    {
        // Notify employee who submitted the reimbursement
        if ($reimbursement->employee && $reimbursement->employee->user_id) {
            $data = [
                'reimbursement_id' => $reimbursement->id,
                'type' => $reimbursement->reimbursment_type,
                'amount' => $reimbursement->amount,
                'status' => $status,
                'updated_by' => Auth::id()
            ];

            $notificationType = $status === 'Paid' ? 'reimbursement_approved' : 'reimbursement_rejected';
            $priority = $status === 'Paid' ? Notification::PRIORITY_NORMAL : Notification::PRIORITY_HIGH;

            Notification::createNotification(
                $reimbursement->employee->user_id,
                $notificationType,
                $data,
                $priority
            );
        }
    }

    /**
     * Create notification for project creation
     */
    public function createProjectNotification($project, $userIds)
    {
        foreach ($userIds as $userId) {
            $data = [
                'project_id' => $project->id,
                'name' => $project->project_name,
                'updated_by' => Auth::id()
            ];

            Notification::createNotification(
                $userId,
                'create_project',
                $data,
                Notification::PRIORITY_NORMAL
            );
        }
    }

    /**
     * Create notification for overtime submission
     */
    public function createOvertimeNotification($overtime)
    {
        // Get all users who should be notified (HR, managers, etc.)
        $notifyUsers = User::whereIn('type', ['admin', 'company', 'senior accounting'])->pluck('id');

        foreach ($notifyUsers as $userId) {
            $data = [
                'overtime_id' => $overtime->id,
                'name' => $overtime->employee->name ?? 'Unknown',
                'date' => $overtime->date,
                'hours' => $overtime->hours,
                'updated_by' => Auth::id()
            ];

            Notification::createNotification(
                $userId,
                'create_overtime',
                $data,
                Notification::PRIORITY_NORMAL
            );
        }
    }

    /**
     * Create notification for medical allowance
     */
    public function createMedicalAllowanceNotification($medicalAllowance)
    {
        // Get all users who should be notified
        $notifyUsers = User::whereIn('type', ['admin', 'company', 'senior accounting'])->pluck('id');

        foreach ($notifyUsers as $userId) {
            $data = [
                'medical_allowance_id' => $medicalAllowance->id,
                'name' => $medicalAllowance->employee->name ?? 'Unknown',
                'amount' => $medicalAllowance->amount,
                'updated_by' => Auth::id()
            ];

            Notification::createNotification(
                $userId,
                'create_medical_allowance',
                $data,
                Notification::PRIORITY_NORMAL
            );
        }
    }

    /**
     * Create notification for announcements
     */
    public function createAnnouncementNotification($announcement, $userIds = null)
    {
        // If no specific users provided, notify all active users
        if (!$userIds) {
            $userIds = User::where('is_active', 1)->pluck('id');
        }

        foreach ($userIds as $userId) {
            $data = [
                'announcement_id' => $announcement->id ?? null,
                'name' => $announcement->title ?? $announcement->name ?? 'New Announcement',
                'updated_by' => Auth::id()
            ];

            Notification::createNotification(
                $userId,
                'new_announcement',
                $data,
                Notification::PRIORITY_HIGH
            );
        }
    }

    /**
     * Create notification for ticket comments
     */
    public function createTicketCommentNotification($ticket, $comment)
    {
        // Notify ticket creator and assigned users
        $notifyUsers = collect();
        
        if ($ticket->created_by && $ticket->created_by != Auth::id()) {
            $notifyUsers->push($ticket->created_by);
        }
        
        if ($ticket->assigned_to && $ticket->assigned_to != Auth::id()) {
            $notifyUsers->push($ticket->assigned_to);
        }

        foreach ($notifyUsers->unique() as $userId) {
            $data = [
                'ticket_id' => $ticket->id,
                'id' => $ticket->id, // for backward compatibility
                'comment' => $comment,
                'updated_by' => Auth::id()
            ];

            Notification::createNotification(
                $userId,
                'comment_ticketing',
                $data,
                Notification::PRIORITY_NORMAL
            );
        }
    }

    /**
     * Clean up old notifications
     */
    public function cleanupOldNotifications($daysOld = 30)
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return Notification::where('created_at', '<', $cutoffDate)
            ->where('is_read', true)
            ->delete();
    }

    /**
     * Get notification statistics for a user
     */
    public function getUserNotificationStats($userId)
    {
        $stats = [
            'total' => Notification::where('user_id', $userId)->count(),
            'unread' => Notification::where('user_id', $userId)->where('is_read', false)->count(),
            'today' => Notification::where('user_id', $userId)->whereDate('created_at', today())->count(),
            'this_week' => Notification::where('user_id', $userId)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        $stats['read'] = $stats['total'] - $stats['unread'];

        return $stats;
    }

    /**
     * Mark notifications as read for specific type
     */
    public function markTypeAsRead($userId, $type)
    {
        return Notification::where('user_id', $userId)
            ->where('type', $type)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Get notifications by priority
     */
    public function getNotificationsByPriority($userId, $priority, $limit = 10)
    {
        return Notification::where('user_id', $userId)
            ->where('priority', $priority)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create bulk notifications
     */
    public function createBulkNotifications($userIds, $type, $data, $priority = Notification::PRIORITY_NORMAL)
    {
        $notifications = [];
        $timestamp = now();

        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'data' => json_encode($data),
                'is_read' => false,
                'priority' => $priority,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ];
        }

        return Notification::insert($notifications);
    }
}