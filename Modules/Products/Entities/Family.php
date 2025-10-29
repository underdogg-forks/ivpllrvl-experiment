<?php

namespace Modules\Products\Entities;

use App\Models\BaseModel;

/**
 * Family Model
 * 
 * Eloquent model for managing ip_families
 * Migrated from CodeIgniter model
 */
class Family extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_families';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'family_id';

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
        'family_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    // TODO: Add relationships, scopes, and methods from original model
}
