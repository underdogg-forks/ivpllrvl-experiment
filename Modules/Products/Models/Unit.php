<?php

namespace Modules\Products\Models;

use Modules\Core\Models\BaseModel;

/**
 * Unit Model.
 *
 * Eloquent model for managing ip_units (product units of measure)
 * Migrated from CodeIgniter Mdl_Units model
 */
class Unit extends BaseModel
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
    protected $table = 'ip_units';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'unit_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unit_name',
        'unit_name_plrl',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'unit_id' => 'integer',
    ];

    /**
     * Default ordering scope.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('unit_name');
    }

    /**
     * Get products that use this unit.
     */
    public function products()
    {
        return $this->hasMany('Modules\Products\Models\Product', 'unit_id', 'unit_id');
    }
}
