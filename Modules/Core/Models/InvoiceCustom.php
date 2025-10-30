<?php

namespace Modules\Core\Entities;

use Modules\Core\Models\BaseModel;

/**
 * InvoiceCustom Model
 * 
 * Eloquent model for managing invoice custom fields
 * Migrated from CodeIgniter Mdl_Invoice_Custom model
 * 
 * @property int $invoice_custom_id
 * @property int $invoice_id
 */
class InvoiceCustom extends BaseModel
{
    /**
     * Custom field positions for invoices
     */
    public static array $positions = [
        'custom_fields',
        'after_due_date',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_invoice_custom';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_custom_id';

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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_custom_id' => 'integer',
        'invoice_id' => 'integer',
    ];

    /**
     * Get custom fields for a specific invoice
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $invoiceId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByInvoiceId($query, int $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    /**
     * Get validation rules for invoice custom fields.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'invoice_id' => 'required|integer',
            'custom_field_id' => 'required|integer',
            'custom_field_value' => 'nullable|string',
        ];
    }
}
