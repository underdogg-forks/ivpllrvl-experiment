<?php

namespace Modules\Payments\Entities;

use App\Models\BaseModel;

/**
 * PaymentMethod Model
 * 
 * Eloquent model for managing ip_payment_methods
 * Migrated from CodeIgniter model
 */
class PaymentMethod extends BaseModel
{
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
     * Default ordering scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('payment_method_name');
    }

    /**
     * Get payments that use this method
     */
    public function payments()
    {
        return $this->hasMany('Modules\Payments\Entities\Payment', 'payment_method_id', 'payment_method_id');
    }
}
