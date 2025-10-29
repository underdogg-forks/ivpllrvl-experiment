<?php

namespace Modules\Core\Entities;

use App\Models\BaseModel;

/**
 * Custom_field Model
 * 
 * Eloquent model for managing custom field definitions
 * Migrated from CodeIgniter Mdl_Custom_Fields model
 * 
 * @property int $custom_field_id
 * @property string $custom_field_table
 * @property string $custom_field_label
 * @property string $custom_field_column
 * @property string $custom_field_type
 * @property int $custom_field_location
 * @property int $custom_field_order
 */
class Custom_field extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_custom_fields';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'custom_field_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'custom_field_table',
        'custom_field_label',
        'custom_field_column',
        'custom_field_type',
        'custom_field_location',
        'custom_field_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'custom_field_id' => 'integer',
        'custom_field_location' => 'integer',
        'custom_field_order' => 'integer',
    ];

    /**
     * Get custom field values
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customValues()
    {
        return $this->hasMany(Custom_value::class, 'custom_values_field', 'custom_field_id');
    }

    /**
     * Get available custom tables
     *
     * @return array
     */
    public static function customTables(): array
    {
        return [
            'ip_client_custom' => 'client',
            'ip_invoice_custom' => 'invoice',
            'ip_payment_custom' => 'payment',
            'ip_quote_custom' => 'quote',
            'ip_user_custom' => 'user',
        ];
    }

    /**
     * Get available custom field types
     *
     * @return array
     */
    public static function customTypes(): array
    {
        return [
            'TEXT',
            'DATE',
            'BOOLEAN',
            'SINGLE-CHOICE',
            'MULTIPLE-CHOICE',
        ];
    }

    /**
     * Get nicename for a custom field type
     *
     * @param string $element
     * @return string
     */
    public static function getNicename(string $element): string
    {
        if (in_array($element, static::customTypes())) {
            return strtolower(str_replace('-', '', $element));
        }
        
        return 'fallback';
    }

    /**
     * Scope for filtering by table
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $table
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTable($query, string $table)
    {
        return $query->where('custom_field_table', $table);
    }

    /**
     * Scope for filtering by table name
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTableName($query, string $name)
    {
        $tables = array_flip(static::customTables());
        $table = $tables[$name] ?? null;
        
        if ($table) {
            return $query->where('custom_field_table', $table);
        }
        
        return $query;
    }
}
