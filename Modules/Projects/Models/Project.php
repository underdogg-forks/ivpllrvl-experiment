<?php

namespace Modules\Projects\Models;

use Modules\Core\Models\BaseModel;

/**
 * Project Model.
 *
 * Eloquent model for managing ip_projects
 * Migrated from CodeIgniter model
 */
class Project extends BaseModel
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
    protected $table = 'ip_projects';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'project_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'project_name',
        'project_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'project_id' => 'integer',
        'client_id'  => 'integer',
    ];

    /**
     * Get the client that owns the project.
     */
    public function client()
    {
        return $this->belongsTo(\Modules\Crm\Models\Client::class, 'client_id', 'client_id');
    }

    /**
     * Get the tasks for the project.
     */
    public function tasks()
    {
        return $this->hasMany(\Modules\Projects\Models\Task::class, 'project_id', 'project_id');
    }
}
