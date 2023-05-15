<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditMemorandum extends Model
{
    use HasFactory;

    protected $table = 'audit_memorandum';

    protected $fillable = [
        'project_id',
        'content',
    ];
}
