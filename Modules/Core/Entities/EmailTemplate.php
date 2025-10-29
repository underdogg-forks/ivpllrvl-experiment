<?php

namespace Modules\Core\Entities;

use App\Models\BaseModel;

/**
 * EmailTemplate Model
 * 
 * Eloquent model for managing ip_email_templates
 * Migrated from CodeIgniter model
 */
class EmailTemplate extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_email_templates';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'email_template_id';

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
        'email_template_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    // TODO: Add relationships, scopes, and methods from original model
}
