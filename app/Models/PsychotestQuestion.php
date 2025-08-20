<?php
// app/Models/PsychotestQuestion.php - Updated
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsychotestQuestion extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'question',
        'image',
        'type',
        'options',
        'correct_answer',
        'points',
        'order',
        'is_active',
        'kraeplin_data',
        'time_limit_seconds',
        'created_by',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'kraeplin_data' => 'array',
    ];

    public static $types = [
        'multiple_choice' => 'Multiple Choice',
        'essay' => 'Essay',
        'rating_scale' => 'Rating Scale',
        'true_false' => 'True/False',
        'kraeplin' => 'Kraeplin',
        'image_choice' => 'Image Choice',
        'field_specific' => 'Field Specific Question',
        'epps_forced_choice' => 'EPPS Forced Choice',
    ];

    public static $difficulty_levels = [
        'easy' => 'Easy',
        'medium' => 'Medium',
        'hard' => 'Hard',
    ];

    public static $field_topics = [
        'audit_procedures' => 'Audit Procedures',
        'tax_calculation' => 'Tax Calculation',
        'financial_accounting' => 'Financial Accounting',
        'internal_control' => 'Internal Control',
        'financial_analysis' => 'Financial Analysis',
        'cost_accounting' => 'Cost Accounting',
        'tax_law' => 'Tax Law',
        'audit_reporting' => 'Audit Reporting',
    ];

    public static $personality_dimensions = [
        'achievement' => 'Achievement',
        'deference' => 'Deference', 
        'order' => 'Order',
        'exhibition' => 'Exhibition',
        'autonomy' => 'Autonomy',
        'affiliation' => 'Affiliation',
        'intraception' => 'Intraception',
        'succorance' => 'Succorance',
        'dominance' => 'Dominance',
        'abasement' => 'Abasement',
        'nurturance' => 'Nurturance',
        'change' => 'Change',
        'endurance' => 'Endurance',
        'heterosexuality' => 'Heterosexuality',
        'aggression' => 'Aggression',
    ];

    public function category()
    {
        return $this->belongsTo(PsychotestCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function answers()
    {
        return $this->hasMany(PsychotestAnswer::class, 'question_id');
    }

    // EPPS pair relationship for forced choice questions
    public function eppsPair()
    {
        return $this->belongsTo(PsychotestQuestion::class, 'epps_pair_id');
    }

    public function eppsQuestions()
    {
        return $this->hasMany(PsychotestQuestion::class, 'epps_pair_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }

    public function scopeByFieldTopic($query, $topic)
    {
        return $query->where('field_topic', $topic);
    }

    public function scopeByPersonalityDimension($query, $dimension)
    {
        return $query->where('personality_dimension', $dimension);
    }

    // Check if question has image
    public function hasImage()
    {
        return !empty($this->image) && file_exists(storage_path('app/public/uploads/psychotest/images/' . $this->image));
    }

    // Get image URL
    public function getImageUrl()
    {
        if ($this->hasImage()) {
            return asset('storage/uploads/psychotest/images/' . $this->image);
        }
        return null;
    }

    // Check if this is a kraeplin question
    public function isKraeplin()
    {
        return $this->type === 'kraeplin';
    }

    // Check if this is field specific question
    public function isFieldSpecific()
    {
        return $this->type === 'field_specific';
    }

    // Check if this is EPPS question
    public function isEPPS()
    {
        return $this->type === 'epps_forced_choice';
    }

    // Get kraeplin columns data
    public function getKraeplinColumns()
    {
        return $this->kraeplin_data['columns'] ?? [];
    }

    // Get difficulty color class for display
    public function getDifficultyColorClass()
    {
        switch ($this->difficulty_level) {
            case 'easy': return 'success';
            case 'medium': return 'warning';
            case 'hard': return 'danger';
            default: return 'secondary';
        }
    }

    // Get field topic display name
    public function getFieldTopicName()
    {
        return self::$field_topics[$this->field_topic] ?? $this->field_topic;
    }

    // Get personality dimension display name
    public function getPersonalityDimensionName()
    {
        return self::$personality_dimensions[$this->personality_dimension] ?? $this->personality_dimension;
    }
}