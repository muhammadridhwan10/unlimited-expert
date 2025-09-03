<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentContributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_review_id',
        'user_id',
        'role'
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
