<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * TablePreset
 *
 * Model for storing user-specific DataTable presets (column visibility, order, filters).
 */
class TablePreset extends Model
{
    protected $fillable = [
        'user_id',
        'table_key',
        'preset_name',
        'columns',
        'filters',
        'page_length',
        'is_default',
    ];

    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
        'page_length' => 'integer',
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the preset.
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    /**
     * Scope to get presets for a specific table and user.
     */
    public function scopeForTable($query, string $tableKey, ?int $userId = null)
    {
        $query->where('table_key', $tableKey);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query;
    }

    /**
     * Scope to get the default preset.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
