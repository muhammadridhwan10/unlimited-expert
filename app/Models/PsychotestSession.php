<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PsychotestSession extends Model
{
    protected $fillable = [
        'schedule_id',
        'category_id',
        'status',
        'started_at',
        'completed_at',
        'time_spent_seconds',
        'session_data',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'session_data' => 'array',
    ];

    public static $status = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'skipped' => 'Skipped',
    ];

    public function schedule()
    {
        return $this->belongsTo(PsychotestSchedule::class, 'schedule_id');
    }

    public function category()
    {
        return $this->belongsTo(PsychotestCategory::class, 'category_id');
    }

    public function answers()
    {
        return $this->hasMany(PsychotestAnswer::class, 'session_id');
    }

    // Check if session has time remaining - PERBAIKAN UTAMA
    public function hasTimeRemaining()
    {
        // Jika session belum dimulai (status pending atau started_at null), return true
        if ($this->status === 'pending' || !$this->started_at) {
            return true;
        }

        // Jika sudah completed, return false
        if ($this->status === 'completed') {
            return false;
        }

        $elapsed = $this->started_at->diffInSeconds(now());
        $limit = $this->category->duration_minutes * 60;
        
        return $elapsed < $limit;
    }

    // Get remaining time in seconds - PERBAIKAN UTAMA
    public function getRemainingSeconds()
    {
        // PENTING: Jika session belum dimulai (status pending atau started_at null), 
        // return waktu penuh tanpa mengurangi apapun
        if ($this->status === 'pending' || !$this->started_at) {
            return $this->category->duration_minutes * 60;
        }

        // Jika sudah completed, return 0
        if ($this->status === 'completed') {
            return 0;
        }

        $elapsed = $this->started_at->diffInSeconds(now());
        $limit = $this->category->duration_minutes * 60;
        
        return max(0, $limit - $elapsed);
    }

    // Start the session - PERBAIKAN
    public function start()
    {
        // Hanya start jika status masih pending
        if ($this->status === 'pending') {
            $this->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }
    }

    // Complete the session
    public function complete()
    {
        $timeSpent = $this->started_at ? $this->started_at->diffInSeconds(now()) : 0;
        
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'time_spent_seconds' => $timeSpent,
        ]);
    }

    // Check if session is expired
    public function isExpired()
    {
        if (!$this->started_at || $this->status === 'completed') {
            return false;
        }

        return !$this->hasTimeRemaining();
    }

    // Get elapsed time in seconds (helper method)
    public function getElapsedSeconds()
    {
        if (!$this->started_at) {
            return 0;
        }

        return $this->started_at->diffInSeconds(now());
    }

    // Get progress percentage (helper method)
    public function getProgressPercentage()
    {
        if (!$this->started_at) {
            return 0;
        }

        $elapsed = $this->getElapsedSeconds();
        $total = $this->category->duration_minutes * 60;
        
        return min(100, round(($elapsed / $total) * 100, 2));
    }
}