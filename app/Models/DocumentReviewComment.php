<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentReviewComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_review_id',
        'user_id',
        'comment',
        'type'
    ];

    public static $types = [
        'general' => 'General',
        'approval' => 'Approval',
        'rejection' => 'Rejection',
        'revision' => 'Revision Request',
        'review' => 'Under Review'
    ];

    public function documentReview()
    {
        return $this->belongsTo(DocumentReview::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeNameAttribute()
    {
        return self::$types[$this->type] ?? 'General';
    }
}
