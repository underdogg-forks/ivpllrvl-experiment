<?php

namespace Modules\Quotes\Entities;

use App\Models\BaseModel;

/**
 * Quote_tax_rate Model
 * 
 * Eloquent model for managing ip_quote_tax_rates
 * Migrated from CodeIgniter model
 */
class Quote_tax_rate extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_quote_tax_rates';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'quote_tax_rate_id';

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
        'tax_rate_id',
        'include_item_tax',
        'quote_tax_rate_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quote_tax_rate_id' => 'integer',
        'quote_id' => 'integer',
        'tax_rate_id' => 'integer',
        'include_item_tax' => 'integer',
        'quote_tax_rate_amount' => 'decimal:2',
    ];

    /**
     * Get the quote that owns the tax rate.
     */
    public function quote()
    {
        return $this->belongsTo('Modules\Quotes\Entities\Quote', 'quote_id', 'quote_id');
    }

    /**
     * Get the tax rate.
     */
    public function taxRate()
    {
        return $this->belongsTo('Modules\Products\Entities\Tax_rate', 'tax_rate_id', 'tax_rate_id');
    }
}
