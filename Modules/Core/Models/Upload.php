<?php

namespace Modules\Core\Entities;

use Modules\Core\Models\BaseModel;

/**
 * Upload Model
 * 
 * Eloquent model for managing ip_uploads
 * Migrated from CodeIgniter model
 */
class Upload extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_uploads';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'upload_id';

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
        'upload_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    // TODO: Add relationships, scopes, and methods from original model
}
