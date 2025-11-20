<?php

namespace Modules\Invoices\Models;

use Modules\Core\Models\BaseModel;

/**
 * Invoice Model.
 *
 * Eloquent model for managing invoices
 * Migrated from CodeIgniter Mdl_Invoices
 */
class Invoice extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_invoices';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_number',
        'invoice_date_created',
        'invoice_date_modified',
        'invoice_date_due',
        'invoice_status_id',
        'invoice_password',
        'client_id',
        'user_id',
        'invoice_group_id',
        'invoice_discount_amount',
        'invoice_discount_percent',
        'invoice_terms',
        'invoice_url_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_id'               => 'integer',
        'client_id'                => 'integer',
        'user_id'                  => 'integer',
        'invoice_group_id'         => 'integer',
        'invoice_status_id'        => 'integer',
        'invoice_discount_amount'  => 'decimal:2',
        'invoice_discount_percent' => 'decimal:2',
    ];

    /**
     * Get the client that owns the invoice.
     */
    public function client()
    {
        return $this->belongsTo('Modules\Crm\Models\Client', 'client_id', 'client_id');
    }

    /**
     * Get the user that created the invoice.
     */
    public function user()
    {
        return $this->belongsTo('Modules\Users\Models\User', 'user_id', 'user_id');
    }

    /**
     * Get the invoice group.
     */
    public function invoiceGroup()
    {
        return $this->belongsTo('Modules\Invoices\Models\InvoiceGroup', 'invoice_group_id', 'invoice_group_id');
    }

    /**
     * Get the invoice amounts.
     */
    public function amounts()
    {
        return $this->hasOne('Modules\Invoices\Models\InvoiceAmount', 'invoice_id', 'invoice_id');
    }

    /**
     * Get the invoice items.
     */
    public function items()
    {
        return $this->hasMany('Modules\Invoices\Models\InvoiceItem', 'invoice_id', 'invoice_id');
    }

    /**
     * Get the invoice tax rates.
     */
    public function taxRates()
    {
        return $this->hasMany('Modules\Invoices\Models\InvoiceTaxRate', 'invoice_id', 'invoice_id');
    }

    /**
     * Get the quote associated with this invoice.
     */
    public function quote()
    {
        return $this->hasOne('Modules\Quotes\Models\Quote', 'invoice_id', 'invoice_id');
    }

    /**
     * Scope a query to only include invoices with a given status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int                                   $statusId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, int $statusId)
    {
        return $query->where('invoice_status_id', $statusId);
    }

    /**
     * Scope a query to only include draft invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('invoice_status_id', 1);
    }

    /**
     * Scope a query to only include sent invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->where('invoice_status_id', 2);
    }

    /**
     * Scope a query to only include viewed invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeViewed($query)
    {
        return $query->where('invoice_status_id', 3);
    }

    /**
     * Scope a query to only include paid invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('invoice_status_id', 4);
    }

    /**
     * Scope a query to only include overdue invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotIn('invoice_status_id', [1, 4])
            ->whereRaw('DATEDIFF(NOW(), invoice_date_due) > 0');
    }

    /**
     * Scope for guest-visible invoices.
     */
    public function scopeGuestVisible($query)
    {
        return $query->whereIn('invoice_status_id', [2, 3, 4]);
    }

    /**
     * Scope to filter invoices by client.
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for open invoices.
     */
    public function scopeOpen($query)
    {
        return $query->whereNotIn('invoice_status_id', [1, 4]);
    }

    /**
     * Scope for SUMEX invoices.
     */
    public function scopeSumex($query)
    {
        return $query->whereHas('sumex');
    }
}
