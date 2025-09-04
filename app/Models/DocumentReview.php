<?php

// Model DocumentReview - Updated with Category Relationship
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'document_name',
        'document_link',
        'category_id', // Changed from 'category' to 'category_id'
        'description',
        'submission_date',
        'submitted_by',
        'approver_id',
        'status',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'created_by'
    ];

    protected $casts = [
        'submission_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    // Status options
    public static $statuses = [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'revision_required' => 'Revision Required'
    ];

    // Status colors for badges
    public static $status_colors = [
        'submitted' => 'info',
        'under_review' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'revision_required' => 'secondary'
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category()
    {
        return $this->belongsTo(DocumentReviewCategory::class, 'category_id');
    }

    public function contributors()
    {
        return $this->hasMany(DocumentContributor::class);
    }

    public function comments()
    {
        return $this->hasMany(DocumentReviewComment::class);
    }

    public function logs()
    {
        return $this->hasMany(DocumentReviewLog::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopePendingApproval($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    public function scopeForApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function getStatusBadgeAttribute()
    {
        $color = self::$status_colors[$this->status] ?? 'secondary';
        $text = self::$statuses[$this->status] ?? 'Unknown';
        return "<span class='badge bg-{$color} p-2 px-3 rounded'>{$text}</span>";
    }

    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : 'Uncategorized';
    }

    public function getCategoryBadgeAttribute()
    {
        return $this->category ? $this->category->badge : "<span class='badge bg-secondary'>Uncategorized</span>";
    }

    public function getIsCustomCategoryAttribute()
    {
        return $this->category ? !$this->category->is_predefined : false;
    }

    public function addLog($action, $details = null, $userId = null)
    {
        $this->logs()->create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'details' => $details
        ]);
    }

    public function approve($userId = null, $comment = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        if ($comment) {
            $this->comments()->create([
                'user_id' => $userId ?? auth()->id(),
                'comment' => $comment,
                'type' => 'approval'
            ]);
        }

        $this->addLog('approved', 'Work/Document approved', $userId);
    }

    public function reject($reason, $userId = null, $comment = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason
        ]);

        if ($comment) {
            $this->comments()->create([
                'user_id' => $userId ?? auth()->id(),
                'comment' => $comment,
                'type' => 'rejection'
            ]);
        }

        $this->addLog('rejected', $reason, $userId);
    }

    public function requireRevision($comment, $userId = null)
    {
        $this->update([
            'status' => 'revision_required'
        ]);

        if ($comment) {
            $this->comments()->create([
                'user_id' => $userId ?? auth()->id(),
                'comment' => $comment,
                'type' => 'revision'
            ]);
        }

        $this->addLog('revision_required', $comment, $userId);
    }

    public function underReview($comment, $userId = null)
    {
        $this->update([
            'status' => 'under_review'
        ]);

        if ($comment) {
            $this->comments()->create([
                'user_id' => $userId ?? auth()->id(),
                'comment' => $comment,
                'type' => 'review'
            ]);
        }

        $this->addLog('under_review', $comment, $userId);
    }

    // Static methods for statistics
    public static function getStatusStatistics($projectId = null)
    {
        $query = self::query();
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return [
            'total' => $query->count(),
            'submitted' => $query->byStatus('submitted')->count(),
            'under_review' => $query->byStatus('under_review')->count(),
            'approved' => $query->byStatus('approved')->count(),
            'rejected' => $query->byStatus('rejected')->count(),
            'revision_required' => $query->byStatus('revision_required')->count(),
        ];
    }

    public static function getCategoryStatistics($projectId)
    {
        return self::where('project_id', $projectId)
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($items) {
                return $items->count();
            });
    }

    public static function getRecentActivity($projectId = null, $limit = 10)
    {
        $query = self::with(['submitter', 'category', 'project'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->get();
    }

    public static function getPendingForApprover($approverId, $projectId = null)
    {
        $query = self::forApprover($approverId)
            ->pendingApproval()
            ->with(['project', 'submitter', 'category'])
            ->orderBy('submission_date', 'asc');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->get();
    }

    // Utility methods
    public function getDaysOldAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    public function getIsOverdueAttribute()
    {
        // Consider as overdue if submitted more than 3 days ago and still pending
        return $this->days_old > 3 && in_array($this->status, ['submitted', 'under_review']);
    }

    public function canBeEditedBy($userId)
    {
        // Submitter can edit if not approved
        if ($this->submitted_by == $userId && $this->status !== 'approved') {
            return true;
        }

        // Approver can always edit
        if ($this->approver_id == $userId) {
            return true;
        }

        return false;
    }

    public function canBeDeletedBy($userId)
    {
        // Only non-approved documents can be deleted
        if ($this->status === 'approved') {
            return false;
        }

        // Submitter can delete their own submission
        if ($this->submitted_by == $userId) {
            return true;
        }

        return false;
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->submitted_by = $model->submitted_by ?? auth()->id();
            $model->created_by = $model->created_by ?? auth()->id();
        });

        static::updating(function ($model) {
            // Auto-set status to under_review when certain actions happen
            if ($model->isDirty(['approver_id']) && $model->status === 'submitted') {
                $model->status = 'under_review';
            }
        });
    }
}