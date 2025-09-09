<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'is_read',
        'read_at',
        'priority',
        'expires_at'
    ];

    protected $dates = [
        'read_at',
        'expires_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array'
    ];

    // Notification priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new notification
     */
    public static function createNotification($userId, $type, $data, $priority = self::PRIORITY_NORMAL, $expiresAt = null)
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'data' => is_array($data) ? $data : json_decode($data, true),
            'is_read' => false,
            'priority' => $priority,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Create notification for reimbursement
     */
    public static function createReimbursementNotification($userId, $type, $reimbursementData, $priority = self::PRIORITY_NORMAL)
    {
        $data = [
            'reimbursement_id' => $reimbursementData->id,
            'employee_name' => $reimbursementData->employee->name ?? 'Unknown',
            'amount' => $reimbursementData->amount,
            'type' => $reimbursementData->reimbursment_type,
            'status' => $reimbursementData->status,
            'date' => $reimbursementData->date,
            'updated_by' => auth()->id()
        ];

        return self::createNotification($userId, $type, $data, $priority);
    }

    /**
     * Create notification for document review
     */
    public static function createDocumentNotification($userId, $type, $documentData, $priority = self::PRIORITY_NORMAL)
    {
        $data = [
            'document_id' => $documentData->id,
            'project_id' => $documentData->project_id,
            'document_name' => $documentData->document_name,
            'status' => $documentData->status,
            'updated_by' => auth()->id()
        ];

        return self::createNotification($userId, $type, $data, $priority);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for priority
     */
    public function scopeOfPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for unexpired notifications
     */
    public function scopeUnexpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Check if notification is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColor()
    {
        switch ($this->priority) {
            case self::PRIORITY_URGENT:
                return 'danger';
            case self::PRIORITY_HIGH:
                return 'warning';
            case self::PRIORITY_NORMAL:
                return 'primary';
            case self::PRIORITY_LOW:
                return 'secondary';
            default:
                return 'primary';
        }
    }

    /**
     * Generate HTML for notification display
     */
    public function toHtml()
    {
        $data = $this->data;
        $link = '#';
        $icon = 'fa fa-bell';
        $icon_color = 'bg-' . $this->getPriorityColor();
        $text = '';
        $usr = null;

        if (isset($data['updated_by']) && !empty($data['updated_by'])) {
            $usr = User::find($data['updated_by']);
        }

        // Handle different notification types
        switch ($this->type) {
            case 'create_project':
                $link = route('projects.show', \Crypt::encrypt($data['project_id']));
                $text = ($usr ? $usr->name . " " : "") . __('created a new project') . " <b class='font-weight-bold'>" . $data['name'] . "</b>";
                $icon = "fa fa-plus";
                break;

            case 'create_overtime':
                $link = route('overtime.index');
                $text = ($usr ? $usr->name . " " : "") . __('created overtime') . " <b class='font-weight-bold'>" . $data['name'] . "</b>";
                $icon = "fa fa-clock";
                break;

            case 'create_medical_allowance':
                $link = route('medical-allowance.index');
                $text = ($usr ? $usr->name . " " : "") . __('created medical allowance') . " <b class='font-weight-bold'>" . $data['name'] . "</b>";
                $icon = "fa fa-medical-bag";
                break;

            case 'comment_ticketing':
                $link = route('support.reply', \Crypt::encrypt($data['id']));
                $text = ($usr ? $usr->name . " " : "") . __('comment ticket');
                $icon = "fa fa-comments";
                break;

            case 'new_announcement':
                $link = route('home');
                $text = $data['name'];
                $icon = "fa fa-bullhorn";
                break;

            case 'reimbursement_submitted':
                $link = route('reimbursment-personal.index');
                $text = ($usr ? $usr->name . " " : "") . __('submitted reimbursement') . " <b class='font-weight-bold'>" . $data['type'] . "</b> " . __('for amount') . " Rp " . number_format($data['amount']);
                $icon = "fa fa-money-bill";
                break;

            case 'reimbursement_approved':
                $link = route('reimbursment-personal.index');
                $text = __('Your reimbursement') . " <b class='font-weight-bold'>" . $data['type'] . "</b> " . __('has been approved');
                $icon = "fa fa-check-circle";
                $icon_color = "bg-success";
                break;

            case 'reimbursement_rejected':
                $link = route('reimbursment-personal.index');
                $text = __('Your reimbursement') . " <b class='font-weight-bold'>" . $data['type'] . "</b> " . __('has been rejected');
                $icon = "fa fa-times-circle";
                $icon_color = "bg-danger";
                break;

            case 'document_submitted':
                $link = route('projects.document-review.show', [$data['project_id'], $data['document_id']]);
                $text = ($usr ? $usr->name . " " : "") . __('submitted document') . " <b class='font-weight-bold'>" . $data['document_name'] . "</b> " . __('for review');
                $icon = "fa fa-file-alt";
                break;

            case 'document_approved':
                $link = route('projects.document-review.show', [$data['project_id'], $data['document_id']]);
                $text = __('Your document') . " <b class='font-weight-bold'>" . $data['document_name'] . "</b> " . __('has been approved');
                $icon = "fa fa-check-circle";
                $icon_color = "bg-success";
                break;

            case 'document_rejected':
                $link = route('projects.document-review.show', [$data['project_id'], $data['document_id']]);
                $text = __('Your document') . " <b class='font-weight-bold'>" . $data['document_name'] . "</b> " . __('has been rejected');
                $icon = "fa fa-times-circle";
                $icon_color = "bg-danger";
                break;

            case 'document_revision_required':
                $link = route('projects.document-review.show', [$data['project_id'], $data['document_id']]);
                $text = __('Revision required for document') . " <b class='font-weight-bold'>" . $data['document_name'] . "</b>";
                $icon = "fa fa-edit";
                $icon_color = "bg-warning";
                break;

            case 'document_under_review':
                $link = route('projects.document-review.show', [$data['project_id'], $data['document_id']]);
                $text = __('Document') . " <b class='font-weight-bold'>" . $data['document_name'] . "</b> " . __('is now under review');
                $icon = "fa fa-search";
                $icon_color = "bg-info";
                break;

            case 'document_comment':
                $link = route('projects.document-review.show', [$data['project_id'], $data['document_id']]);
                $text = ($usr ? $usr->name . " " : "") . __('commented on document') . " <b class='font-weight-bold'>" . $data['document_name'] . "</b>";
                $icon = "fa fa-comments";
                break;

            case 'leave_submitted':
                $text = ($usr ? $usr->name . " " : "") . __('submitted leave request');
                $icon = "fa fa-calendar";
                break;

            case 'leave_approved':
                $text = __('Your leave request has been approved');
                $icon = "fa fa-check-circle";
                $icon_color = "bg-success";
                break;

            case 'leave_rejected':
                $text = __('Your leave request has been rejected');
                $icon = "fa fa-times-circle";
                $icon_color = "bg-danger";
                break;

            case 'overtime_submitted':
                $text = ($usr ? $usr->name . " " : "") . __('submitted overtime request');
                $icon = "fa fa-clock";
                break;

            case 'overtime_approved':
                $text = __('Your overtime request has been approved');
                $icon = "fa fa-check-circle";
                $icon_color = "bg-success";
                break;

            case 'project_comment':
                $text = ($usr ? $usr->name . " " : "") . __('commented on project');
                $icon = "fa fa-comments";
                break;

            case 'project_invitation':
                $text = ($usr ? $usr->name . " " : "") . __('invited you to project');
                $icon = "fa fa-user-plus";
                break;

            default:
                $text = __('New notification');
                break;
        }

        $date = $this->created_at->diffForHumans();
        $readClass = $this->is_read ? '' : 'notification-unread';
        
        $html = '<a href="' . $link . '" class="list-group-item list-group-item-action ' . $readClass . '" data-notification-id="' . $this->id . '">
                    <div class="d-flex align-items-center">
                        <div>
                            <span class="avatar ' . $icon_color . ' text-white rounded-circle"><i class="' . $icon . '"></i></span>
                        </div>
                        <div class="flex-fill ml-3 ms-3">
                            <div class="h6 text-sm mb-0">' . $text . '</div>
                            <small class="text-muted text-xs">' . $date . '</small>
                        </div>
                        <div class="ml-2">
                            <span class="badge badge-' . $this->getPriorityColor() . '">' . ucfirst($this->priority) . '</span>
                        </div>
                    </div>
                </a>';

        return $html;
    }

    /**
     * Mark this notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Clean up expired notifications
     */
    public static function cleanupExpired()
    {
        return self::where('expires_at', '<', now())->delete();
    }
}