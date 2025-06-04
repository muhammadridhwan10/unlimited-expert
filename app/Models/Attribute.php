<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'name',
        'weight',
    ];

    /**
     * Contoh relasi jika indicator digunakan dalam evaluasi detail
     */
    public function evaluationDetails()
    {
        return $this->hasMany(EvaluationDetail::class);
    }
}
