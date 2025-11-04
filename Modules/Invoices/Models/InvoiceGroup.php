<?php

namespace Modules\Invoices\Models;

use Modules\Core\Models\BaseModel;

/**
 * InvoiceGroup Model.
 *
 * Eloquent model for managing ip_invoice_groups
 * Migrated from CodeIgniter model
 */
class InvoiceGroup extends BaseModel
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
    protected $table = 'ip_invoice_groups';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_group_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_group_name',
        'invoice_group_identifier_format',
        'invoice_group_next_id',
        'invoice_group_left_pad',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_group_id'       => 'integer',
        'invoice_group_next_id'  => 'integer',
        'invoice_group_left_pad' => 'integer',
    ];

    /**
     * Default ordering scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('invoice_group_name');
    }

    /**
     * Get invoices that belong to this group.
     */
    public function invoices()
    {
        return $this->hasMany('Modules\Invoices\Models\Invoice', 'invoice_group_id', 'invoice_group_id');
    }
}
