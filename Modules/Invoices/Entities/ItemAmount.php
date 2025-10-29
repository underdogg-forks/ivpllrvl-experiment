<?php

namespace Modules\Invoices\Entities;

use App\Models\BaseModel;

/**
 * ItemAmount Model
 * 
 * Eloquent model for managing invoice item amounts
 * Migrated from CodeIgniter model
 */
class ItemAmount extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_invoice_item_amounts';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'item_amount_id';

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
        'item_id',
        'item_subtotal',
        'item_tax_total',
        'item_discount',
        'item_total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'item_amount_id' => 'integer',
        'item_id' => 'integer',
        'item_subtotal' => 'decimal:2',
        'item_tax_total' => 'decimal:2',
        'item_discount' => 'decimal:2',
        'item_total' => 'decimal:2',
    ];

    /**
     * Get the item that owns the amount.
     */
    public function item()
    {
        return $this->belongsTo('Modules\Invoices\Entities\Item', 'item_id', 'item_id');
    }
}
