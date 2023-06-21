<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotesAnalysis extends Model
{
    use HasFactory;

    protected $table = 'notesanalysis';

    protected $fillable = [
        'notes', 'project_id', 'task_id'
    ];

}
