<?php
// app/Models/PsychotestSchedule.php - Updated
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PsychotestSchedule extends Model
{
    protected $fillable = [
        'candidate',
        'username',
        'password',
        'start_time',
        'end_time',
        'status',
        'instructions',
        'duration_minutes',
        'selected_categories', // New field
        'email_sent',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'email_sent' => 'boolean',
        'selected_categories' => 'array', // New cast
    ];

    public static $status = [
        'scheduled' => 'Scheduled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
    ];

    public function candidates()
    {
        return $this->belongsTo(JobApplication::class, 'candidate');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions()
    {
        return $this->hasMany(PsychotestSession::class, 'schedule_id');
    }

    public function answers()
    {
        return $this->hasMany(PsychotestAnswer::class, 'schedule_id');
    }

    public function result()
    {
        return $this->hasOne(PsychotestResult::class, 'schedule_id');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // Get categories for this schedule
    public function getCategories()
    {
        if ($this->selected_categories && !empty($this->selected_categories)) {
            // Return specific selected categories
            return PsychotestCategory::whereIn('id', $this->selected_categories)
                ->active()
                ->ordered()
                ->get();
        } else {
            // Return default categories based on job
            $jobTitle = $this->candidates->jobs->title ?? '';
            return PsychotestCategory::active()
                ->ordered()
                ->where(function($query) use ($jobTitle) {
                    $query->where('is_job_specific', false)
                          ->orWhere(function($q) use ($jobTitle) {
                              $q->where('is_job_specific', true);
                              if ($jobTitle) {
                                  $this->addJobSpecificFilter($q, $jobTitle);
                              }
                          });
                })
                ->get();
        }
    }

    private function addJobSpecificFilter($query, $jobTitle)
    {
        $jobTitleLower = strtolower($jobTitle);
        $keywords = ['auditor', 'audit', 'tax', 'taxation', 'accounting', 'akuntan', 'perpajakan'];
        
        foreach ($keywords as $keyword) {
            if (strpos($jobTitleLower, $keyword) !== false) {
                $query->whereJsonContains('target_job_keywords', $keyword);
                break;
            }
        }
    }

    // Create sessions for selected categories
    public function createSessions()
    {
        $categories = $this->getCategories();
        
        foreach ($categories as $category) {
            PsychotestSession::firstOrCreate([
                'schedule_id' => $this->id,
                'category_id' => $category->id,
            ], [
                'status' => 'pending',
                'started_at' => null,
            ]);
        }
    }

    // Get current active session
    public function getCurrentSession()
    {
        return $this->sessions()
            ->where('status', 'in_progress')
            ->first();
    }

    // Get next pending session berdasarkan urutan category
    public function getNextSession()
    {
        return $this->sessions()
            ->where('status', 'pending')
            ->join('psychotest_categories', 'psychotest_sessions.category_id', '=', 'psychotest_categories.id')
            ->orderBy('psychotest_categories.order')
            ->select('psychotest_sessions.*')
            ->with('category')
            ->first();
    }

    // Check if all sessions are completed
    public function allSessionsCompleted()
    {
        $totalSessions = $this->sessions()->count();
        $completedSessions = $this->sessions()->where('status', 'completed')->count();
        return $totalSessions > 0 && $totalSessions === $completedSessions;
    }

    // Get progress percentage
    public function getProgressPercentage()
    {
        $totalSessions = $this->sessions()->count();
        if ($totalSessions === 0) return 0;

        $completedSessions = $this->sessions()->where('status', 'completed')->count();
        return round(($completedSessions / $totalSessions) * 100);
    }

    // Time validation methods
    public function isActive()
    {
        $now = Carbon::now();
        $isWithinTimeWindow = $now->greaterThanOrEqualTo($this->start_time) && 
                             $now->lessThanOrEqualTo($this->end_time);
        return $isWithinTimeWindow && in_array($this->status, ['scheduled', 'in_progress']);
    }

    public function canStart()
    {
        $now = Carbon::now();
        $isWithinWindow = $now->greaterThanOrEqualTo($this->start_time) && 
                         $now->lessThanOrEqualTo($this->end_time);
        return $isWithinWindow && in_array($this->status, ['scheduled', 'in_progress']);
    }

    // Debug method - untuk melihat status semua sessions
    public function getSessionsStatus()
    {
        return $this->sessions()
            ->join('psychotest_categories', 'psychotest_sessions.category_id', '=', 'psychotest_categories.id')
            ->orderBy('psychotest_categories.order')
            ->select('psychotest_sessions.*', 'psychotest_categories.name as category_name', 'psychotest_categories.order as category_order')
            ->get()
            ->map(function($session) {
                return [
                    'id' => $session->id,
                    'category' => $session->category_name,
                    'order' => $session->category_order,
                    'status' => $session->status,
                    'started_at' => $session->started_at,
                    'completed_at' => $session->completed_at,
                ];
            });
    }
}