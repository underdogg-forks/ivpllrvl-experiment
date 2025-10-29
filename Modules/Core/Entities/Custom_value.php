<?php

namespace Modules\Core\Entities;

use App\Models\BaseModel;

/**
 * Custom_value Model
 * 
 * Eloquent model for managing ip_custom_values
 * Migrated from CodeIgniter model
 */
class Custom_value extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_custom_values';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'custom_values_id';

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
        'custom_values_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    // TODO: Add relationships, scopes, and methods from original model
}
