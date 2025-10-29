<?php

namespace Modules\Quotes\Entities;

use App\Models\BaseModel;

/**
 * Quote_amount Model
 * 
 * Eloquent model for managing unknown_table
 * Migrated from CodeIgniter model
 */
class Quote_amount extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_quote_amounts';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'quote_amount_id';

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
        'quote_item_subtotal',
        'quote_item_tax_total',
        'quote_tax_total',
        'quote_total',
        'quote_item_discount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quote_amount_id' => 'integer',
        'quote_id' => 'integer',
        'quote_item_subtotal' => 'decimal:2',
        'quote_item_tax_total' => 'decimal:2',
        'quote_tax_total' => 'decimal:2',
        'quote_total' => 'decimal:2',
        'quote_item_discount' => 'decimal:2',
    ];

    /**
     * Get the quote that owns the amount.
     */
    public function quote()
    {
        return $this->belongsTo('Modules\Quotes\Entities\Quote', 'quote_id', 'quote_id');
    }
}
