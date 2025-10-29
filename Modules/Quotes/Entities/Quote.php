<?php

namespace Modules\Quotes\Entities;

use App\Models\BaseModel;

/**
 * Quote Model
 * 
 * Eloquent model for managing ip_quotes
 * Migrated from CodeIgniter model
 */
class Quote extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_quotes';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'quote_id';

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
        'quote_number',
        'quote_date_created',
        'quote_date_modified',
        'quote_date_expires',
        'quote_status_id',
        'quote_password',
        'client_id',
        'user_id',
        'invoice_group_id',
        'quote_discount_amount',
        'quote_discount_percent',
        'quote_terms',
        'quote_url_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quote_id' => 'integer',
        'client_id' => 'integer',
        'user_id' => 'integer',
        'invoice_group_id' => 'integer',
        'quote_status_id' => 'integer',
        'quote_discount_amount' => 'decimal:2',
        'quote_discount_percent' => 'decimal:2',
    ];

    /**
     * Get the client that owns the quote.
     */
    public function client()
    {
        return $this->belongsTo('Modules\Crm\Entities\Client', 'client_id', 'client_id');
    }

    /**
     * Get the user that created the quote.
     */
    public function user()
    {
        return $this->belongsTo('Modules\Users\Entities\User', 'user_id', 'user_id');
    }

    /**
     * Get the invoice group.
     */
    public function invoiceGroup()
    {
        return $this->belongsTo('Modules\Invoices\Entities\InvoiceGroup', 'invoice_group_id', 'invoice_group_id');
    }

    /**
     * Get the quote amounts.
     */
    public function amounts()
    {
        return $this->hasOne('Modules\Quotes\Entities\QuoteAmount', 'quote_id', 'quote_id');
    }

    /**
     * Get the quote items.
     */
    public function items()
    {
        return $this->hasMany('Modules\Quotes\Entities\QuoteItem', 'quote_id', 'quote_id');
    }

    /**
     * Get the quote tax rates.
     */
    public function taxRates()
    {
        return $this->hasMany('Modules\Quotes\Entities\QuoteTaxRate', 'quote_id', 'quote_id');
    }

    /**
     * Scope a query to only include quotes with a given status.
     */
    public function scopeByStatus($query, int $statusId)
    {
        return $query->where('quote_status_id', $statusId);
    }

    /**
     * Scope a query to only include draft quotes.
     */
    public function scopeDraft($query)
    {
        return $query->where('quote_status_id', 1);
    }

    /**
     * Scope a query to only include sent quotes.
     */
    public function scopeSent($query)
    {
        return $query->where('quote_status_id', 2);
    }

    /**
     * Scope a query to only include approved quotes.
     */
    public function scopeApproved($query)
    {
        return $query->where('quote_status_id', 4);
    }
}
