<?php

namespace Modules\Products\Models;

use Modules\Core\Models\BaseModel;

/**
 * Family Model.
 *
 * Eloquent model for managing ip_families (product families)
 * Migrated from CodeIgniter Mdl_Families model
 */
class Family extends BaseModel
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
    protected $table = 'ip_families';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'family_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'family_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'family_id' => 'integer',
    ];

    /**
     * Get validation rules for families.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'family_name' => 'required|string|max:255',
        ];
    }

    /**
     * Default ordering scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('family_name');
    }

    /**
     * Get products that belong to this family.
     */
    public function products()
    {
        return $this->hasMany('Modules\Products\Models\Product', 'family_id', 'family_id');
    }
}
