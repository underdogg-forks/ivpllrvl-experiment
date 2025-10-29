<?php

namespace Modules\Payments\Entities;

use App\Models\BaseModel;

/**
 * Payment_log Model
 * 
 * Eloquent model for managing ip_merchant_responses
 * Migrated from CodeIgniter model
 */
class Payment_log extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_merchant_responses';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'merchant_response_id';

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
        'merchant_response_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    // TODO: Add relationships, scopes, and methods from original model
}
