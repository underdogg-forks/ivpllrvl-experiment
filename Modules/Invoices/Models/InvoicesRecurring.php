<?php

namespace Modules\Invoices\Models;

use Modules\Core\Models\BaseModel;

/**
 * InvoicesRecurring Model.
 *
 * Eloquent model for managing ip_invoices_recurring
 * Migrated from CodeIgniter model
 */
class InvoicesRecurring extends BaseModel
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
    protected $table = 'ip_invoices_recurring';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_recurring_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',
        'recur_start_date',
        'recur_end_date',
        'recur_frequency',
        'recur_next_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_recurring_id' => 'integer',
        'invoice_id'           => 'integer',
    ];

    /**
     * Get the invoice that owns the recurring data.
     */
    public function invoice()
    {
        return $this->belongsTo('Modules\Invoices\Models\Invoice', 'invoice_id', 'invoice_id');
    }

    /**
     * Scope for active recurring invoices.
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('recur_next_date');
    }
}
