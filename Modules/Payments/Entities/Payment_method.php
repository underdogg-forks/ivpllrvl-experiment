<?php

namespace Modules\Payments\Entities;

use App\Models\BaseModel;

/**
 * Payment_method Model
 * 
 * Eloquent model for managing ip_payment_methods
 * Migrated from CodeIgniter model
 */
class Payment_method extends BaseModel
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
        // TODO: Add fillable fields from validation_rules or db schema
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_method_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    // TODO: Add relationships, scopes, and methods from original model
}
