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
     * Task statuses constant.
     */
    public const STATUSES = [
        1 => ['label' => 'Not Started', 'class' => 'default'],
        2 => ['label' => 'In Progress', 'class' => 'info'],
        3 => ['label' => 'Complete', 'class' => 'success'],
        4 => ['label' => 'On Hold', 'class' => 'warning'],
    ];

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
        'project_id',
        'task_name',
        'task_status',
        'task_finish_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'task_id'    => 'integer',
        'project_id' => 'integer',
    ];

    /**
     * Get the project that owns the task.
     */
    public function project()
    {
        return $this->belongsTo(\Modules\Projects\Models\Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the tax rate for the task.
     */
    public function taxRate()
    {
        return $this->belongsTo(\Modules\Products\Models\TaxRate::class, 'tax_rate_id', 'tax_rate_id');
    }
}
