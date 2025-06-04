<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'indicator_id',
        'indicator_category',
        'indicator_name',
        'score',
        'weight',
        'comments'
    ];

    // Relasi ke Evaluation
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function getTotalScoreAttribute()
    {
        return $this->score * ($this->indicator->weight ?? 1);
    }
}