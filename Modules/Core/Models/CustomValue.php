<?php

namespace Modules\Core\Entities;

use Modules\Core\Models\BaseModel;

/**
 * CustomValue Model
 * 
 * Eloquent model for managing custom field value options
 * Migrated from CodeIgniter Mdl_Custom_Values model
 * 
 * @property int $custom_values_id
 * @property int $custom_values_field
 * @property string $custom_values_value
 */
class CustomValue extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_custom_values';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'custom_values_id';

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
        'custom_values_field',
        'custom_values_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'custom_values_id' => 'integer',
        'custom_values_field' => 'integer',
    ];

    /**
     * Get the custom field this value belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customField()
    {
        return $this->belongsTo(CustomField::class, 'custom_values_field', 'custom_field_id');
    }

    /**
     * Get available custom field types
     *
     * @return array
     */
    public static function customTypes(): array
    {
        return array_merge(static::userInputTypes(), static::customValueFields());
    }

    /**
     * Get user input types
     *
     * @return array
     */
    public static function userInputTypes(): array
    {
        return [
            'TEXT',
            'DATE',
            'BOOLEAN',
        ];
    }

    /**
     * Get custom value field types
     *
     * @return array
     */
    public static function customValueFields(): array
    {
        return [
            'SINGLE-CHOICE',
            'MULTIPLE-CHOICE',
        ];
    }

    /**
     * Get custom tables mapping
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
     * Scope for filtering by field ID
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $fieldId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByFieldId($query, int $fieldId)
    {
        return $query->where('custom_values_field', $fieldId);
    }

    /**
     * Get custom values by multiple IDs
     *
     * @param array|string $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByIds($ids)
    {
        if (empty($ids)) {
            return collect();
        }
        
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        
        return static::whereIn('custom_values_id', $ids)->get();
    }

    /**
     * Delete all values for a specific field
     *
     * @param int $fieldId
     * @return void
     */
    public static function deleteAllByField(int $fieldId): void
    {
        static::where('custom_values_field', $fieldId)->delete();
    }

    /**
     * Get validation rules for custom values.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'custom_values_field' => 'required|integer',
            'custom_values_value' => 'required|string|max:255',
            'custom_values_order' => 'nullable|integer',
        ];
    }
}
