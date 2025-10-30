<?php

namespace Modules\Products\Models;

use Modules\Core\Models\BaseModel;

/**
 * Unit Model
 *
 * Eloquent model for managing ip_units (product units of measure)
 * Migrated from CodeIgniter Mdl_Units model
 */
class Unit extends BaseModel
{
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
     * Default ordering scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('unit_name');
    }

    /**
     * Return either the singular unit name or the plural unit name,
     * depending on the quantity.
     *
     * @param int $unitId
     * @param float $quantity
     * @return string|null
     */
    public static function getName($unitId, $quantity)
    {
        if (!$unitId) {
            return null;
        }

        $unit = static::find($unitId);

        if (!$unit) {
            return null;
        }

        // Return plural if quantity is less than -1 or greater than 1
        if ($quantity < -1 || $quantity > 1) {
            return $unit->unit_name_plrl;
        }

        return $unit->unit_name;
    }

    /**
     * Get products that use this unit
     */
    public function products()
    {
        return $this->hasMany('Modules\Products\Models\Product', 'unit_id', 'unit_id');
    }

    /**
     * Get validation rules for units.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'unit_name' => 'required|string|max:255',
            'unit_name_plrl' => 'required|string|max:255',
        ];
    }
}
