<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsychotestAnswer extends Model
{
    protected $fillable = [
        'schedule_id',
        'session_id',
        'question_id',
        'answer',
        'points_earned',
        'answered_at',
        'time_taken_seconds',
        'kraeplin_answers',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'kraeplin_answers' => 'array',
    ];

    public function schedule()
    {
        return $this->belongsTo(PsychotestSchedule::class, 'schedule_id');
    }

    public function session()
    {
        return $this->belongsTo(PsychotestSession::class, 'session_id');
    }

    public function question()
    {
        return $this->belongsTo(PsychotestQuestion::class, 'question_id');
    }
}