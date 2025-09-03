<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentReviewLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_review_id',
        'user_id',
        'action',
        'details'
    ];

    public function documentReview()
    {
        return $this->belongsTo(DocumentReview::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
