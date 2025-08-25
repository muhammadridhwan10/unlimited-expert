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
        if ($this->percentage >= 85) return 'A';
        if ($this->percentage >= 75) return 'B';
        if ($this->percentage >= 65) return 'C';
        if ($this->percentage >= 55) return 'D';
        if ($this->percentage >= 45) return 'E';
        return 'F';
    }
}
