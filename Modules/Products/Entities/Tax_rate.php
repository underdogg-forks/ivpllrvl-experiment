<?php

namespace Modules\Products\Entities;

use App\Models\BaseModel;

/**
 * Tax_rate Model
 * 
 * Eloquent model for managing ip_tax_rates
 * Migrated from CodeIgniter model
 */
class Tax_rate extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_tax_rates';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'tax_rate_id';

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
        'tax_rate_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    // TODO: Add relationships, scopes, and methods from original model
}
