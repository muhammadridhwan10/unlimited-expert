<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabelProject extends Model
{
   use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get active service types for dropdown
     */
    public static function getActiveServiceTypes()
    {
        return self::where('is_active', true)
                   ->orderBy('name')
                   ->pluck('name', 'name');
    }

    /**
     * Get service types with code as key for backward compatibility
     */
    public static function getServiceTypesWithCode()
    {
        return self::where('is_active', true)
                   ->orderBy('sort_order')
                   ->orderBy('name')
                   ->pluck('name', 'code');
    }

    /**
     * Relationship with User (creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with Projects
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'service_type_id');
    }
}
