<?php

namespace Modules\Core\Models;

/**
 * PaymentCustom Model.
 *
 * Eloquent model for managing payment custom fields
 * Migrated from CodeIgniter Mdl_Payment_Custom model
 *
 * @property int $payment_custom_id
 * @property int $payment_id
 */
class PaymentCustom extends BaseModel
{
    /**
     * Custom field positions for payments.
     */
    public static array $positions = [
        'custom_fields',
    ];

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
    protected $table = 'ip_payment_custom';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'payment_custom_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_custom_id' => 'integer',
        'payment_id'        => 'integer',
    ];

    /**
     * Get validation rules for payment custom fields.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'payment_id'         => 'required|integer',
            'custom_field_id'    => 'required|integer',
            'custom_field_value' => 'nullable|string',
        ];
    }

    /**
     * Get custom fields for a specific payment.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int                                   $paymentId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPaymentId($query, int $paymentId)
    {
        return $query->where('payment_id', $paymentId);
    }
}
