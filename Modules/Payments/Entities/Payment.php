<?php

namespace Modules\Payments\Entities;

use App\Models\BaseModel;

/**
 * Payment Model
 * 
 * Eloquent model for managing payments
 * Migrated from CodeIgniter Mdl_Payments
 */
class Payment extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_payments';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'payment_id';

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
        'payment_method_id',
        'payment_amount',
        'payment_date',
        'payment_note',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_id' => 'integer',
        'invoice_id' => 'integer',
        'payment_method_id' => 'integer',
        'payment_amount' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the payment.
     */
    public function invoice()
    {
        return $this->belongsTo('Modules\Invoices\Entities\Invoice', 'invoice_id', 'invoice_id');
    }

    /**
     * Get the payment method.
     */
    public function paymentMethod()
    {
        return $this->belongsTo('Modules\Payments\Entities\Payment_method', 'payment_method_id', 'payment_method_id');
    }

    /**
     * Default ordering scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('payment_date', 'desc')
            ->orderBy('payment_id', 'desc');
    }

    /**
     * Mutator for payment_amount
     */
    public function setPaymentAmountAttribute($value)
    {
        $this->attributes['payment_amount'] = standardize_amount($value);
    }
}
