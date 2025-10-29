<?php

namespace Modules\Products\Entities;

use App\Models\BaseModel;

/**
 * TaxRate Model
 * 
 * Eloquent model for managing ip_tax_rates
 * Migrated from CodeIgniter model
 */
class TaxRate extends BaseModel
{
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
        'tax_rate_name',
        'tax_rate_percent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tax_rate_id' => 'integer',
        'tax_rate_percent' => 'decimal:2',
    ];

    /**
     * Default ordering scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('tax_rate_percent');
    }
}
