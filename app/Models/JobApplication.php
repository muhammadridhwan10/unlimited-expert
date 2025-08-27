<?php

// JobApplication.php Model - Add these relationships and attributes

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JobApplication extends Model
{
    protected $fillable = [
        'job', 'name', 'email', 'phone', 'profile', 'resume', 'kk', 'ktp', 
        'transkrip_nilai', 'ijazah', 'certificate', 'cover_letter', 'dob', 
        'gender', 'country', 'state', 'city', 'year_graduated', 'last_education', 
        'major', 'university', 'latest_work_experience', 'length_of_last_job', 
        'ipk', 'stage', 'custom_question', 'created_by'
    ];

    // Existing IPK options
    public static $ipk = [
        '4' => '4.00',
        '3' => '3.00 - 3.99', 
        '2' => '2.00 - 2.99',
        '1' => 'Below 2.00'
    ];

    public function jobs()
    {
        return $this->hasOne('App\Models\Job', 'id', 'job');
    }

    // Relationship to JobStage
    public function stage_status()
    {
        return $this->belongsTo(JobStage::class, 'stage', 'id');
    }

    // Relationship to Job
    public function job_detail()
    {
        return $this->belongsTo(Job::class, 'job', 'id');
    }

    // Relationship to User (who created this application)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // NEW: Accessor for formatted applied date
    public function getAppliedAtAttribute()
    {
        return $this->created_at;
    }

    // NEW: Accessor for formatted applied date (human readable)
    public function getAppliedAtFormattedAttribute()
    {
        return $this->created_at->format('d M Y, H:i');
    }

    // NEW: Accessor for applied date only
    public function getAppliedDateAttribute()
    {
        return $this->created_at->format('Y-m-d');
    }

    // NEW: Scope for filtering by applied date range
    public function scopeAppliedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // NEW: Scope for filtering by applied date from
    public function scopeAppliedFrom($query, $date)
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    // NEW: Scope for filtering by applied date to
    public function scopeAppliedTo($query, $date)
    {
        return $query->whereDate('created_at', '<=', $date);
    }

    // NEW: Scope for recent applications (last 30 days)
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays(30));
    }

    // NEW: Scope for this month applications
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
    }

    // NEW: Scope for today's applications
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    // Mutator for dates
    public function setDobAttribute($value)
    {
        if ($value) {
            $this->attributes['dob'] = Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
        }
    }

    // Cast dates
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'dob' => 'date',
    ];
}