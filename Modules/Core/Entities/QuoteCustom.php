<?php

namespace Modules\Core\Entities;

use App\Models\BaseModel;

/**
 * QuoteCustom Model
 * 
 * Eloquent model for managing quote custom fields
 * Migrated from CodeIgniter Mdl_Quote_Custom model
 * 
 * @property int $quote_custom_id
 * @property int $quote_id
 */
class QuoteCustom extends BaseModel
{
    /**
     * Custom field positions for quotes
     */
    public static array $positions = [
        'custom_fields',
        'after_expires',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_quote_custom';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'quote_custom_id';

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
        'quote_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quote_custom_id' => 'integer',
        'quote_id' => 'integer',
    ];

    /**
     * Get custom fields for a specific quote
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $quoteId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByQuoteId($query, int $quoteId)
    {
        return $query->where('quote_id', $quoteId);
    }
}
