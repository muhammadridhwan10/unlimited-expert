<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentReviewCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_predefined',
        'is_active',
        'created_by',
        'project_id',
        'sort_order'
    ];

    protected $casts = [
        'is_predefined' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function documentReviews()
    {
        return $this->hasMany(DocumentReview::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePredefined($query)
    {
        return $query->where('is_predefined', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_predefined', false);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('project_id');
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where(function($q) use ($projectId) {
            $q->whereNull('project_id')  // Global categories
              ->orWhere('project_id', $projectId); // Project-specific categories
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors & Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function getBadgeAttribute()
    {
        return "<span class='badge' style='background-color: {$this->color}; color: white;'>{$this->name}</span>";
    }

    public function getIconHtmlAttribute()
    {
        return "<i class='{$this->icon} me-1'></i>";
    }

    // Static methods
    public static function getAvailableForProject($projectId)
    {
        return self::active()
            ->forProject($projectId)
            ->ordered()
            ->get();
    }

    public static function createCustomCategory($name, $projectId = null, $userId = null, $attributes = [])
    {
        $slug = Str::slug($name);
        
        // Check if category already exists
        $existing = self::where('slug', $slug)
            ->where(function($q) use ($projectId) {
                $q->whereNull('project_id')
                  ->orWhere('project_id', $projectId);
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        // Create new custom category
        return self::create(array_merge([
            'name' => $name,
            'slug' => $slug,
            'description' => $attributes['description'] ?? "Custom category: {$name}",
            'color' => $attributes['color'] ?? '#6c757d',
            'icon' => $attributes['icon'] ?? 'ti-tag',
            'is_predefined' => false,
            'is_active' => true,
            'created_by' => $userId,
            'project_id' => $projectId,
            'sort_order' => $attributes['sort_order'] ?? 999
        ], $attributes));
    }

    public static function getPopularCategories($projectId = null, $limit = 5)
    {
        return self::select('document_review_categories.*')
            ->join('document_reviews', 'document_reviews.category_id', '=', 'document_review_categories.id')
            ->when($projectId, function($q) use ($projectId) {
                $q->where('document_reviews.project_id', $projectId);
            })
            ->groupBy('document_review_categories.id')
            ->orderByRaw('COUNT(document_reviews.id) DESC')
            ->limit($limit)
            ->get();
    }

    public static function getCategoryStats($projectId = null)
    {
        $query = self::select('document_review_categories.*')
            ->withCount(['documentReviews' => function($q) use ($projectId) {
                if ($projectId) {
                    $q->where('project_id', $projectId);
                }
            }])
            ->active()
            ->ordered();

        if ($projectId) {
            $query->forProject($projectId);
        }

        return $query->get();
    }

    // Helper methods
    public function getUsageCount($projectId = null)
    {
        $query = $this->documentReviews();
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        return $query->count();
    }

    public function canBeDeleted()
    {
        // Predefined categories cannot be deleted
        if ($this->is_predefined) {
            return false;
        }

        // Categories with existing document reviews cannot be deleted
        if ($this->documentReviews()->count() > 0) {
            return false;
        }

        return true;
    }

    public function duplicate($projectId, $userId = null)
    {
        return self::create([
            'name' => $this->name,
            'slug' => $this->slug . '-' . Str::random(4),
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_predefined' => false,
            'is_active' => true,
            'created_by' => $userId,
            'project_id' => $projectId,
            'sort_order' => $this->sort_order
        ]);
    }

    // Constants for colors and icons
    public static function getAvailableColors()
    {
        return [
            '#007bff' => 'Primary Blue',
            '#28a745' => 'Success Green',
            '#dc3545' => 'Danger Red',
            '#ffc107' => 'Warning Yellow',
            '#17a2b8' => 'Info Cyan',
            '#6f42c1' => 'Purple',
            '#e83e8c' => 'Pink',
            '#fd7e14' => 'Orange',
            '#20c997' => 'Teal',
            '#6c757d' => 'Gray',
            '#343a40' => 'Dark',
            '#f8f9fa' => 'Light'
        ];
    }

    public static function getAvailableIcons()
    {
        return [
            'ti ti-file' => 'Document',
            'ti ti-file-text' => 'Text File',
            'ti ti-folder' => 'Folder',
            'ti ti-chart-line' => 'Chart',
            'ti ti-calculator' => 'Calculator',
            'ti ti-search' => 'Search',
            'ti ti-bulb' => 'Idea',
            'ti ti-mail' => 'Mail',
            'ti ti-crown' => 'Crown',
            'ti ti-notes' => 'Notes',
            'ti ti-presentation' => 'Presentation',
            'ti ti-clipboard' => 'Clipboard',
            'ti ti-trending-up' => 'Trending Up',
            'ti ti-package' => 'Package',
            'ti ti-message-circle' => 'Message',
            'ti ti-edit' => 'Edit',
            'ti ti-tag' => 'Tag',
            'ti ti-star' => 'Star',
            'ti ti-award' => 'Award',
            'ti ti-target' => 'Target'
        ];
    }
}