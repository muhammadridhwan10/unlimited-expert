<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluator_id',
        'evaluatee_id',
        'quarter',
        'total_score'
    ];

    // Relasi ke User (yang menilai)
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    // Relasi ke User (yang dinilai)
    public function evaluatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }

    // Relasi ke EvaluationDetail
    public function details(): HasMany
    {
        return $this->hasMany(EvaluationDetail::class);
    }

    public function getCategoryScores()
    {
        $categories = [];
        foreach ($this->details as $detail) {
            $category = $detail->indicator_category;
            $categories[$category]['total'] = ($categories[$category]['total'] ?? 0) + $detail->getTotalScoreAttribute();
        }
        return $categories;
    }

    public function getOverallScoreAttribute()
    {
        $categoryScores = $this->getCategoryScores();
        $overallScore = 0;
        foreach ($categoryScores as $category => $data) {
            $overallScore += $data['total'];
        }
        return $overallScore;
    }

    public function getRatingAttribute()
    {
        $overallScore = $this->getOverallScoreAttribute();
        if ($overallScore >= 80) {
            return 5;
        } elseif ($overallScore >= 60) {
            return 4;
        } elseif ($overallScore >= 40) {
            return 3;
        } elseif ($overallScore >= 20) {
            return 2;
        } else {
            return 1;
        }
    }
}