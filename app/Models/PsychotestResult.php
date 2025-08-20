<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsychotestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'total_questions',
        'answered_questions',
        'total_points',
        'earned_points',
        'percentage',
        'grade',
        'notes',
        'category_scores',
        'category_results',
        'total_time_spent_seconds',
        'completion_status'
    ];

    protected $casts = [
        'category_scores' => 'array',
    ];

    public function schedule()
    {
        return $this->belongsTo(PsychotestSchedule::class, 'schedule_id');
    }

    public function calculateGrade()
    {
        if ($this->percentage >= 90) return 'A';
        if ($this->percentage >= 80) return 'B';
        if ($this->percentage >= 70) return 'C';
        if ($this->percentage >= 60) return 'D';
        return 'F';
    }
}
