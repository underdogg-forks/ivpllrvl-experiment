<?php

namespace Modules\Invoices\Entities;

use App\Models\BaseModel;

/**
 * InvoicesRecurring Model
 * 
 * Eloquent model for managing ip_invoices_recurring
 * Migrated from CodeIgniter model
 */
class InvoicesRecurring extends BaseModel
{
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
        'invoice_id' => 'integer',
    ];

    /**
     * Get the invoice that owns the recurring data.
     */
    public function invoice()
    {
        return $this->belongsTo('Modules\Invoices\Entities\Invoice', 'invoice_id', 'invoice_id');
    }

    /**
     * Get validation rules for recurring invoices.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'invoice_id' => 'required|integer',
            'recur_start_date' => 'required|date',
            'recur_end_date' => 'nullable|date',
            'recur_frequency' => 'required|string',
            'recur_next_date' => 'nullable|date',
        ];
    }

    /**
     * Scope for active recurring invoices.
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('recur_next_date');
    }
}
