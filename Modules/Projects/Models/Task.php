<?php

namespace Modules\Projects\Models;

use Modules\Core\Models\BaseModel;

/**
 * Task Model.
 *
 * Eloquent model for managing ip_tasks
 * Migrated from CodeIgniter model
 */
class Task extends BaseModel
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
    protected $table = 'ip_tasks';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'task_id';

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
        'task_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    /**
     * Get validation rules for tasks.
     *
     * @return array
     */
}
