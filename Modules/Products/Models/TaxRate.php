<?php

namespace Modules\Products\Models;

use Modules\Core\Models\BaseModel;

/**
 * TaxRate Model.
 *
 * Eloquent model for managing ip_tax_rates
 * Migrated from CodeIgniter model
 */
class TaxRate extends BaseModel
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
    protected $table = 'ip_tax_rates';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'tax_rate_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tax_rate_name',
        'tax_rate_percent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tax_rate_id'      => 'integer',
        'tax_rate_percent' => 'decimal:2',
    ];

    /**
     * Get validation rules for tax rates.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'tax_rate_name'    => 'required|string|max:255',
            'tax_rate_percent' => 'required|numeric|min:0|max:100',
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
        return $query->orderBy('tax_rate_percent');
    }
}
