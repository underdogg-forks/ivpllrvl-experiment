<?php

namespace Modules\Crm\Models;

use Modules\Core\Models\BaseModel;

/**
 * Project Model
 *
 * Eloquent model for managing ip_projects
 * Migrated from CodeIgniter model
 */
class Project extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_projects';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'project_id';

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
        'project_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    /**
     * Get validation rules for projects.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'client_id' => 'required|integer',
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string',
        ];
    }
}
