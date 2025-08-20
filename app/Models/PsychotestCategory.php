<?php
// app/Models/PsychotestCategory.php - Updated
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsychotestCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'duration_minutes',
        'total_questions',
        'order',
        'is_active',
        'is_job_specific',
        'target_job_keywords',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'target_job_keywords' => 'array',
        'is_active' => 'boolean',
        'is_job_specific' => 'boolean',
    ];

    public static $types = [
        'standard' => 'Standard Question',
        'kraeplin' => 'Kraeplin Test',
        'visual' => 'Visual/Image Based',
        'verbal' => 'Verbal/Language',
        'numeric' => 'Numeric/Math',
        'field_specific' => 'Field Specific Test',
        'personality' => 'Personality Test',
    ];

    public function questions()
    {
        return $this->hasMany(PsychotestQuestion::class, 'category_id')
            ->where('is_active', true)
            ->orderBy('order');
    }

    public function sessions()
    {
        return $this->hasMany(PsychotestSession::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeForJob($query, $jobTitle)
    {
        return $query->where(function($q) use ($jobTitle) {
            $q->where('is_job_specific', false)
              ->orWhere(function($q2) use ($jobTitle) {
                  $q2->where('is_job_specific', true);
                  if ($jobTitle) {
                      $q2->where(function($q3) use ($jobTitle) {
                          foreach ($this->getJobKeywords() as $keyword) {
                              $q3->orWhereJsonContains('target_job_keywords', $keyword);
                          }
                      });
                  }
              });
        });
    }

    // Get settings with defaults
    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    // Check if this is a kraeplin test
    public function isKraeplin()
    {
        return $this->type === 'kraeplin' || stripos($this->code, 'kraeplin') !== false;
    }

    // Check if this is EPPS test
    public function isEPPS()
    {
        return stripos($this->code, 'epps') !== false || 
            stripos($this->name, 'epps') !== false ||
            ($this->type === 'personality' && stripos($this->name, 'epps') !== false);
    }

    // Check if this is field specific test
    public function isFieldSpecific()
    {
        return $this->type === 'field_specific' || $this->is_job_specific;
    }

    // Get kraeplin columns count
    public function getKraeplinColumns()
    {
        $settings = $this->settings ?? [];
        return $settings['kraeplin_columns'] ?? 10;
    }

    // Get kraeplin time per column (in seconds)
    public function getKraeplinTimePerColumn()
    {
        $settings = $this->settings ?? [];
        return $settings['time_per_column'] ?? 30;
    }

    public function getEPPSDimensions()
    {
        $settings = $this->settings ?? [];
        return $settings['personality_dimensions'] ?? [
            'achievement', 'deference', 'order', 'exhibition', 'autonomy',
            'affiliation', 'intraception', 'succorance', 'dominance',
            'abasement', 'nurturance', 'change', 'endurance', 'heterosexuality', 'aggression'
        ];
    }

    /**
     * Get EPPS scoring method
     */
    public function getEPPSScoringMethod()
    {
        $settings = $this->settings ?? [];
        return $settings['scoring_method'] ?? 'forced_choice';
    }

    /**
     * Check if category should show progress
     */
    public function shouldShowProgress()
    {
        $settings = $this->settings ?? [];
        return $settings['show_progress'] ?? false;
    }

    // Check if category is applicable for specific job
    public function isApplicableForJob($jobTitle)
    {
        if (!$this->is_job_specific) {
            return true;
        }

        if (!$jobTitle || !$this->target_job_keywords) {
            return false;
        }

        $jobTitleLower = strtolower($jobTitle);
        foreach ($this->target_job_keywords as $keyword) {
            if (strpos($jobTitleLower, strtolower($keyword)) !== false) {
                return true;
            }
        }

        return false;
    }

    // Get job keywords for filtering
    private function getJobKeywords()
    {
        return ['auditor', 'audit', 'tax', 'taxation', 'accounting', 'akuntan', 'perpajakan'];
    }
}
