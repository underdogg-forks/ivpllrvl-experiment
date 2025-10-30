<?php

namespace Modules\Payments\Models;

use Modules\Core\Models\BaseModel;

/**
 * PaymentMethod Model.
 *
 * Eloquent model for managing ip_payment_methods
 * Migrated from CodeIgniter model
 */
class PaymentMethod extends BaseModel
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
    protected $table = 'ip_payment_methods';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'payment_method_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_method_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_method_id' => 'integer',
    ];

    /**
     * Get validation rules for payment methods.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'payment_method_name' => 'required|string|max:255',
        ];
    }

    /**
     * Default ordering scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('payment_method_name');
    }

    /**
     * Get payments that use this method.
     */
    public function payments()
    {
        return $this->hasMany('Modules\Payments\Models\Payment', 'payment_method_id', 'payment_method_id');
    }
}
